const workerUrl = document.querySelector('meta[name="dutytrack-worker"]')?.content;
let deferredInstall;
let registration;
let reloadForUpdate = false;
const standalone = () => matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
const dismissed = () => {
    try { return Number(localStorage.getItem('dutytrack-install-dismissed')) > Date.now() - 7 * 86400000; }
    catch { return false; }
};
function notice(id, message, label, action, dismiss) {
    if (document.getElementById(id)) return;
    let region = document.getElementById('pwa-notices');
    if (!region) {
        region = document.createElement('aside');
        region.id = 'pwa-notices';
        region.setAttribute('aria-label', 'DutyTrack app notices');
        document.body.append(region);
    }
    const panel = document.createElement('div');
    panel.id = id;
    panel.className = 'pwa-notice';
    const text = document.createElement('p');
    text.setAttribute('role', 'status');
    text.textContent = message;
    panel.append(text);
    if (label) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.onclick = action;
        panel.append(button);
    }
    if (dismiss) {
        const close = document.createElement('button');
        close.type = 'button';
        close.textContent = 'Dismiss';
        close.onclick = () => { dismiss(); panel.remove(); };
        panel.append(close);
    }
    region.append(panel);
}
function dismissInstall() {
    try { localStorage.setItem('dutytrack-install-dismissed', Date.now()); } catch { /* Storage may be disabled. */ }
}
function render() {
    if (!navigator.onLine) notice('pwa-offline', 'You’re offline. Live data and saving changes require an internet connection.');
    else document.getElementById('pwa-offline')?.remove();
    if (registration?.waiting) {
        notice('pwa-update', 'A DutyTrack update is ready. Save your work in all open tabs before reloading.', 'Update and reload', () => {
            if (!navigator.onLine || !registration.waiting) return;
            if (!window.confirm('Have you saved your work in all DutyTrack tabs? This tab will reload.')) return;
            reloadForUpdate = true;
            registration.waiting.postMessage('ACTIVATE_UPDATE');
        }, () => {});
    }
    if (standalone() || dismissed()) return;
    if (deferredInstall) notice('pwa-install', 'Install DutyTrack for quick access from your home screen.', 'Install DutyTrack', async () => {
        const prompt = deferredInstall;
        if (!prompt) return;
        deferredInstall = null;
        document.getElementById('pwa-install')?.remove();
        try {
            await prompt.prompt();
            const choice = await prompt.userChoice;
            if (choice.outcome === 'dismissed') dismissInstall();
        } catch (error) { console.warn('DutyTrack installation could not start.', error); }
    }, dismissInstall);
    else if (/iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
        if (/Safari/.test(navigator.userAgent) && !/CriOS|FxiOS|EdgiOS/.test(navigator.userAgent)) {
            notice('pwa-install', 'To install DutyTrack in Safari, tap Share → Add to Home Screen → Add.', null, null, dismissInstall);
        }
    }
}
window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredInstall = event;
    render();
});
window.addEventListener('appinstalled', () => {
    deferredInstall = null;
    document.getElementById('pwa-install')?.remove();
});
window.addEventListener('online', render);
window.addEventListener('offline', render);
document.addEventListener('livewire:navigated', render);
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
else render();
if (workerUrl && 'serviceWorker' in navigator && window.isSecureContext) {
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (reloadForUpdate) { reloadForUpdate = false; location.reload(); }
    });
    navigator.serviceWorker.register(workerUrl, { scope: new URL('.', workerUrl).pathname, updateViaCache: 'none' })
        .then(value => {
            registration = value;
            render();
            registration.addEventListener('updatefound', () => {
                const worker = registration.installing;
                worker?.addEventListener('statechange', () => {
                    if (worker.state === 'installed') render();
                });
            });
            window.addEventListener('online', () => registration.update().catch(console.warn));
        })
        .catch(error => console.warn('DutyTrack offline support could not start. Online access is still available.', error));
}
