// Alpine components for the student Resume Builder.

let pdfjs;
const loadPdfjs = () => (pdfjs ??= Promise.all([
    import('pdfjs-dist'),
    import('pdfjs-dist/build/pdf.worker.min.mjs?url'),
]).then(([lib, worker]) => {
    lib.GlobalWorkerOptions.workerSrc = worker.default;
    return lib;
}));

const uploadToWire = ($wire, name, file) => new Promise((resolve, reject) => {
    $wire.upload(name, file, resolve, reject);
});

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    // Debounced autosave with an honest status: saved | dirty | saving | invalid | failed.
    Alpine.data('resumeEditor', () => ({
        status: 'saved',
        tab: 'editor',
        timer: null,

        init() {
            this.guard = (event) => {
                if (this.status !== 'saved') event.preventDefault();
            };
            this.leave = (event) => {
                if (this.status !== 'saved' && !confirm('You have unsaved changes. Leave anyway?')) event.preventDefault();
            };
            window.addEventListener('beforeunload', this.guard);
            document.addEventListener('livewire:navigate', this.leave);
        },

        destroy() {
            clearTimeout(this.timer);
            window.removeEventListener('beforeunload', this.guard);
            document.removeEventListener('livewire:navigate', this.leave);
        },

        queue() {
            this.status = 'dirty';
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.persist(), 900);
        },

        async persist() {
            clearTimeout(this.timer);
            this.timer = null;
            this.status = 'saving';

            try {
                await this.$wire.save();
            } catch {
                if (!this.timer) this.status = 'failed';
            }
        },

        // Server-side results; a newer queued edit keeps the status "dirty".
        saved() {
            if (!this.timer) this.status = 'saved';
        },

        invalid() {
            if (!this.timer) this.status = 'invalid';
        },

        async download(url) {
            if (this.status !== 'saved') await this.persist();

            if (this.status === 'saved') {
                window.location.href = url;
            } else {
                window.Flux?.toast({ variant: 'danger', text: 'Fix the highlighted fields first. The PDF uses your saved resume.' });
            }
        },
    }));

    // Renders the exact PDF that downloads, using PDF.js.
    Alpine.data('resumePreview', (url) => {
        // Kept outside Alpine's reactive proxy: PDF.js objects use private class fields.
        let doc = null;
        let task = null;

        return {
            url,
            zoom: 'fit',
            scale: 1,
            pages: 0,
            loading: true,
            error: false,
            loadSeq: 0,
            renderSeq: 0,

            init() {
                this.load();
                this.observer = new ResizeObserver(() => {
                    // A reload can destroy the document mid-render; the next render recovers.
                    if (this.zoom === 'fit') this.render().catch(() => {});
                });
                this.observer.observe(this.$refs.viewport);
            },

            destroy() {
                this.observer?.disconnect();
                task?.destroy();
            },

            async load() {
                const seq = ++this.loadSeq;
                this.loading = true;

                try {
                    const [lib, response] = await Promise.all([
                        loadPdfjs(),
                        fetch(`${this.url}?t=${Date.now()}`, { credentials: 'same-origin' }),
                    ]);
                    if (!response.ok) throw new Error(`Preview failed (${response.status})`);

                    const next = lib.getDocument({ data: await response.arrayBuffer() });
                    const loaded = await next.promise;
                    if (seq !== this.loadSeq) return next.destroy();

                    task?.destroy();
                    task = next;
                    doc = loaded;
                    this.pages = doc.numPages;
                    await this.render();
                    this.error = false;
                } catch (error) {
                    console.error('Resume preview failed', error);
                    if (seq === this.loadSeq) this.error = true;
                } finally {
                    if (seq === this.loadSeq) this.loading = false;
                }
            },

            async render() {
                const viewport = this.$refs.viewport;
                if (!doc || !viewport.clientWidth) return;

                const seq = ++this.renderSeq;
                const first = await doc.getPage(1);
                const pageWidth = first.getViewport({ scale: 1 }).width;
                const style = getComputedStyle(viewport);
                const available = viewport.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
                this.scale = this.zoom === 'fit' ? Math.max(0.3, available / pageWidth) : this.zoom;

                const ratio = window.devicePixelRatio || 1;
                const canvases = [];

                for (let number = 1; number <= doc.numPages; number++) {
                    const page = number === 1 ? first : await doc.getPage(number);
                    const pageViewport = page.getViewport({ scale: this.scale });
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.floor(pageViewport.width * ratio);
                    canvas.height = Math.floor(pageViewport.height * ratio);
                    canvas.style.width = `${Math.floor(pageViewport.width)}px`;
                    canvas.style.height = `${Math.floor(pageViewport.height)}px`;
                    canvas.className = 'bg-white shadow-md ring-1 ring-black/5';
                    canvas.setAttribute('role', 'img');
                    canvas.setAttribute('aria-label', `Resume page ${number} of ${doc.numPages}`);

                    await page.render({
                        canvas,
                        canvasContext: canvas.getContext('2d'),
                        viewport: pageViewport,
                        transform: ratio === 1 ? null : [ratio, 0, 0, ratio, 0, 0],
                    }).promise;

                    if (seq !== this.renderSeq) return;
                    canvases.push(canvas);
                }

                // Swap in all pages at once so the preview never flickers blank.
                this.$refs.pages.replaceChildren(...canvases);
            },

            setZoom(value) {
                this.zoom = value === 'fit' ? 'fit' : Math.min(3, Math.max(0.3, Math.round(value * 100) / 100));
                this.render().catch(() => {});
            },
        };
    });

    // Square (2x2) crop with drag, keyboard, and zoom; uploads a 600x600 JPEG.
    Alpine.data('photoCropper', () => ({
        src: null,
        image: null,
        zoom: 1,
        x: 0,
        y: 0,
        drag: null,
        error: null,
        uploading: false,

        pick(event) {
            const file = event.target.files[0];
            event.target.value = '';
            this.error = null;
            if (!file) return;

            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                this.error = 'Choose a JPG or PNG image.';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.error = 'The photo must be 5 MB or smaller.';
                return;
            }

            const src = URL.createObjectURL(file);
            const image = new Image();
            image.onload = () => {
                if (image.naturalWidth < 200 || image.naturalHeight < 200) {
                    this.error = 'The photo must be at least 200×200 pixels.';
                    URL.revokeObjectURL(src);
                    return;
                }
                this.cancel();
                Object.assign(this, { src, image, zoom: 1, x: 0, y: 0 });
                this.$nextTick(() => this.$refs.frame.focus());
            };
            image.onerror = () => {
                this.error = 'That image could not be opened.';
            };
            image.src = src;
        },

        frameSize() {
            return this.$refs.frame?.clientWidth || 224;
        },

        displayScale() {
            const size = this.frameSize();
            return Math.max(size / this.image.naturalWidth, size / this.image.naturalHeight) * this.zoom;
        },

        get imageStyle() {
            if (!this.image) return '';
            const scale = this.displayScale();
            return `width:${this.image.naturalWidth * scale}px;height:${this.image.naturalHeight * scale}px;`
                + `transform:translate(calc(-50% + ${this.x}px), calc(-50% + ${this.y}px));`;
        },

        // Keep the frame fully covered by the image.
        clamp() {
            const scale = this.displayScale();
            const size = this.frameSize();
            const maxX = Math.max(0, (this.image.naturalWidth * scale - size) / 2);
            const maxY = Math.max(0, (this.image.naturalHeight * scale - size) / 2);
            this.x = Math.min(maxX, Math.max(-maxX, this.x));
            this.y = Math.min(maxY, Math.max(-maxY, this.y));
        },

        zoomBy(delta) {
            this.zoom = Math.min(4, Math.max(1, this.zoom + delta));
            this.clamp();
        },

        start(event) {
            event.currentTarget.setPointerCapture(event.pointerId);
            this.drag = { x: event.clientX - this.x, y: event.clientY - this.y };
        },

        move(event) {
            if (!this.drag) return;
            this.x = event.clientX - this.drag.x;
            this.y = event.clientY - this.drag.y;
            this.clamp();
        },

        end() {
            this.drag = null;
        },

        nudge(event) {
            const moves = { ArrowLeft: [-5, 0], ArrowRight: [5, 0], ArrowUp: [0, -5], ArrowDown: [0, 5] };
            if (moves[event.key]) {
                event.preventDefault();
                this.x += moves[event.key][0];
                this.y += moves[event.key][1];
                this.clamp();
            } else if (event.key === '+' || event.key === '=') {
                this.zoomBy(0.1);
            } else if (event.key === '-') {
                this.zoomBy(-0.1);
            }
        },

        cancel() {
            if (this.src) URL.revokeObjectURL(this.src);
            Object.assign(this, { src: null, image: null });
        },

        // ponytail: only the cropped 600x600 result is stored, so re-positioning a saved photo
        // means choosing the file again. Store the original too if in-place re-cropping is wanted.
        async apply() {
            const output = 600;
            const scale = this.displayScale();
            const size = this.frameSize() / scale;
            const sx = this.image.naturalWidth / 2 - this.x / scale - size / 2;
            const sy = this.image.naturalHeight / 2 - this.y / scale - size / 2;

            const canvas = document.createElement('canvas');
            canvas.width = canvas.height = output;
            const context = canvas.getContext('2d');
            context.fillStyle = '#fff';
            context.fillRect(0, 0, output, output);
            context.drawImage(this.image, sx, sy, size, size, 0, 0, output, output);

            this.uploading = true;
            this.error = null;

            try {
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.92));
                await uploadToWire(this.$wire, 'upload', new File([blob], 'photo.jpg', { type: 'image/jpeg' }));
                if (await this.$wire.saveUpload()) this.cancel();
            } catch {
                this.error = 'The photo could not be saved. Check your connection and try again.';
            } finally {
                this.uploading = false;
            }
        },
    }));

    // Pointer-based signature pad (mouse, touch, stylus) with undo/clear, plus image upload.
    Alpine.data('signaturePad', () => ({
        mode: 'draw',
        strokes: [],
        current: null,
        preview: null,
        file: null,
        error: null,
        uploading: false,

        init() {
            this.observer = new ResizeObserver(() => this.resize());
            this.observer.observe(this.$refs.canvas);
        },

        destroy() {
            this.observer?.disconnect();
            this.resetFile();
        },

        resize() {
            const canvas = this.$refs.canvas;
            if (!canvas.clientWidth) return;
            const ratio = window.devicePixelRatio || 1;
            canvas.width = canvas.clientWidth * ratio;
            canvas.height = canvas.clientHeight * ratio;
            this.redraw();
        },

        point(event) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            // Points are stored relative to the canvas size so strokes survive resizes.
            const pressure = event.pointerType === 'pen' && event.pressure ? event.pressure : 0.5;
            return [(event.clientX - rect.left) / rect.width, (event.clientY - rect.top) / rect.height, pressure];
        },

        start(event) {
            event.preventDefault();
            event.currentTarget.setPointerCapture(event.pointerId);
            this.current = [this.point(event)];
            this.strokes.push(this.current);
            this.redraw();
        },

        draw(event) {
            if (!this.current) return;
            const events = event.getCoalescedEvents?.() ?? [event];
            events.forEach((item) => this.current.push(this.point(item)));
            this.redraw();
        },

        stop() {
            this.current = null;
        },

        paint(context, width, height, offsetX = 0, offsetY = 0) {
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.strokeStyle = '#111827';

            this.strokes.forEach((stroke) => {
                for (let i = 0; i < stroke.length; i++) {
                    const [x, y, pressure] = stroke[i];
                    const [px, py] = stroke[Math.max(0, i - 1)];
                    context.lineWidth = (1.2 + pressure * 2.6) * (width / 600) * 1.6;
                    context.beginPath();
                    context.moveTo(px * width - offsetX, py * height - offsetY);
                    context.lineTo(x * width - offsetX + (i ? 0 : 0.01), y * height - offsetY);
                    context.stroke();
                }
            });
        },

        redraw() {
            const canvas = this.$refs.canvas;
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
            this.paint(context, canvas.width, canvas.height);
        },

        undo() {
            this.strokes.pop();
            this.redraw();
        },

        clear() {
            this.strokes = [];
            this.redraw();
        },

        // Exports the drawing trimmed to its ink with a transparent background.
        async saveDrawing() {
            const width = 1500;
            const height = 500;
            const xs = this.strokes.flat().map(([x]) => x * width);
            const ys = this.strokes.flat().map(([, y]) => y * height);
            const pad = 16;
            const left = Math.max(0, Math.min(...xs) - pad);
            const top = Math.max(0, Math.min(...ys) - pad);
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.min(width, Math.max(...xs) + pad) - left);
            canvas.height = Math.max(1, Math.min(height, Math.max(...ys) + pad) - top);
            this.paint(canvas.getContext('2d'), width, height, left, top);

            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
            if (await this.send(new File([blob], 'signature.png', { type: 'image/png' }))) this.clear();
        },

        pickFile(event) {
            const file = event.target.files[0];
            event.target.value = '';
            this.error = null;
            if (!file) return;

            if (!['image/png', 'image/jpeg'].includes(file.type)) {
                this.error = 'Choose a PNG or JPG image.';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                this.error = 'The signature image must be 2 MB or smaller.';
                return;
            }

            this.resetFile();
            this.file = file;
            this.preview = URL.createObjectURL(file);
        },

        resetFile() {
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = null;
            this.file = null;
        },

        async saveFile() {
            if (this.file && await this.send(this.file)) this.resetFile();
        },

        async send(file) {
            this.uploading = true;
            this.error = null;

            try {
                await uploadToWire(this.$wire, 'upload', file);
                return await this.$wire.saveUpload();
            } catch {
                this.error = 'The signature could not be saved. Check your connection and try again.';
                return false;
            } finally {
                this.uploading = false;
            }
        },
    }));
});
