// Global Pomodoro timer: lives in an Alpine store so it keeps running across wire:navigate,
// and stores an absolute end time so it also survives full page reloads.
// Keyed per user so students sharing a browser don't inherit each other's session.
const STORAGE_KEY = `student-pomodoro-state:${document.querySelector('meta[name="user-id"]')?.content ?? 'guest'}`;

document.addEventListener('alpine:init', () => {
    Alpine.store('pomodoro', {
        presets: [
            { key: 'focus', label: 'Focus', seconds: 1500, dot: 'bg-red-500', stroke: 'stroke-red-600 dark:stroke-red-500' },
            { key: 'short_break', label: 'Short Break', seconds: 300, dot: 'bg-sky-500', stroke: 'stroke-sky-500' },
            { key: 'long_break', label: 'Long Break', seconds: 900, dot: 'bg-emerald-500', stroke: 'stroke-emerald-500' },
        ],
        mode: 'focus',
        duration: 1500,
        timeLeft: 1500,
        endsAt: null,
        completedSessions: 0,
        focusMinutes: 0,
        cycleCount: 1,

        init() {
            this.loadState();
            this.tick();
            setInterval(() => this.tick(), 1000);
        },

        get isRunning() {
            return this.endsAt !== null;
        },

        // Started and not reset: drives the sidebar card.
        get isActive() {
            return this.isRunning || this.timeLeft < this.duration;
        },

        get formattedTime() {
            const minutes = String(Math.floor(this.timeLeft / 60)).padStart(2, '0');
            const seconds = String(this.timeLeft % 60).padStart(2, '0');

            return `${minutes}:${seconds}`;
        },

        get progress() {
            if (this.duration <= 0) {
                return 0;
            }

            return Math.min(1, Math.max(0, (this.duration - this.timeLeft) / this.duration));
        },

        get activePreset() {
            return this.presets.find((item) => item.key === this.mode) ?? this.presets[0];
        },

        get activeLabel() {
            return this.activePreset.label;
        },

        // x-model on the segmented control routes through switchMode so the timer resets
        get selectedMode() {
            return this.mode;
        },

        set selectedMode(key) {
            this.switchMode(key);
        },

        tick() {
            if (!this.isRunning) {
                return;
            }

            this.timeLeft = Math.max(0, Math.round((this.endsAt - Date.now()) / 1000));

            if (this.timeLeft === 0) {
                this.completeSession();
            }
        },

        toggleTimer() {
            this.isRunning ? this.pauseTimer() : this.startTimer();
        },

        startTimer() {
            if (this.isRunning) {
                return;
            }

            this.endsAt = Date.now() + this.timeLeft * 1000;
            this.persistState();
        },

        pauseTimer() {
            if (this.isRunning) {
                this.timeLeft = Math.max(0, Math.round((this.endsAt - Date.now()) / 1000));
                this.endsAt = null;
            }

            this.persistState();
        },

        resetTimer() {
            this.pauseTimer();
            this.timeLeft = this.duration;
            this.persistState();
        },

        skipSession() {
            this.pauseTimer();
            this.completeSession();
        },

        adjustTime(amount) {
            this.pauseTimer();

            this.duration = Math.max(60, this.duration + amount);
            this.timeLeft = Math.max(0, Math.min(this.timeLeft + amount, this.duration));

            if (this.timeLeft === 0) {
                this.timeLeft = this.duration;
            }

            this.persistState();
        },

        switchMode(key) {
            const preset = this.presets.find((item) => item.key === key);

            if (!preset) {
                return;
            }

            this.pauseTimer();
            this.mode = preset.key;
            this.duration = preset.seconds;
            this.timeLeft = preset.seconds;
            this.persistState();
        },

        completeSession() {
            const completedMode = this.mode;

            this.endsAt = null;

            if (completedMode === 'focus') {
                this.completedSessions += 1;
                this.focusMinutes += Math.round(this.duration / 60);

                if (this.cycleCount >= 4) {
                    this.cycleCount = 1;
                    this.switchMode('long_break');
                } else {
                    this.cycleCount += 1;
                    this.switchMode('short_break');
                }
            } else {
                this.switchMode('focus');
            }

            document.dispatchEvent(new CustomEvent('toast-show', {
                detail: {
                    duration: 5000,
                    slots: {
                        heading: 'Session complete',
                        text: completedMode === 'focus'
                            ? 'Nice work. Time for a well-earned break.'
                            : 'Break finished. Ready for another focus block.',
                    },
                    dataset: { variant: 'success' },
                },
            }));
        },

        persistState() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    mode: this.mode,
                    duration: this.duration,
                    timeLeft: this.timeLeft,
                    endsAt: this.endsAt,
                    completedSessions: this.completedSessions,
                    focusMinutes: this.focusMinutes,
                    cycleCount: this.cycleCount,
                }));
            } catch {
                // Storage unavailable (private mode): timer still works for this page load.
            }
        },

        loadState() {
            try {
                const state = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? 'null');

                if (!state) {
                    return;
                }

                this.mode = state.mode ?? this.mode;
                this.duration = state.duration ?? this.duration;
                this.timeLeft = state.timeLeft ?? this.timeLeft;
                this.endsAt = state.endsAt ?? null;
                this.completedSessions = state.completedSessions ?? this.completedSessions;
                this.focusMinutes = state.focusMinutes ?? this.focusMinutes;
                this.cycleCount = state.cycleCount ?? this.cycleCount;
            } catch {
                // Corrupt or unavailable storage: start fresh.
            }
        },
    });
});
