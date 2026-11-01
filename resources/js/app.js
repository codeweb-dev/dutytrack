import { Observer } from "tailwindcss-intersect";

Observer.start();

import './pwa';

const docxPreview = document.querySelector('[data-docx-preview]');

if (docxPreview) {
    const loadDocxPreview = async () => {
        try {
            const response = await fetch(docxPreview.dataset.fileUrl);
            if (!response.ok) throw new Error();

            const { renderAsync } = await import('docx-preview');
            await renderAsync(await response.blob(), docxPreview, undefined, { renderAltChunks: false });
            docxPreview.setAttribute('aria-busy', 'false');
            docxPreview.querySelectorAll('a').forEach((link) => link.removeAttribute('href'));
        } catch {
            docxPreview.textContent = 'This document could not be previewed.';
            docxPreview.setAttribute('aria-busy', 'false');
        }
    };

    loadDocxPreview();
}

let highlighted;
let highlightedSidebar;
let positionFrame;

function positionTour() {
    cancelAnimationFrame(positionFrame);
    positionFrame = requestAnimationFrame(() => {
        const replayMarker = document.querySelector('[data-onboarding-replay]');
        if (replayMarker) {
            const url = new URL(location.href);
            url.searchParams.delete('tour');
            history.replaceState(history.state, '', url);
            replayMarker.removeAttribute('data-onboarding-replay');
        }

        const tooltip = document.querySelector('[data-onboarding-tour]');
        const dialog = tooltip || document.querySelector('.duty-tour-dialog');

        if (!tooltip) {
            highlighted?.classList.remove('duty-tour-highlight');
            highlighted = null;
            highlightedSidebar?.classList.remove('duty-tour-sidebar-active');
            highlightedSidebar = null;
            if (dialog && !dialog.contains(document.activeElement)) {
                (dialog.querySelector('[data-tour-primary]') || dialog.querySelector('button'))?.focus();
            }
            return;
        }

        if (!dialog.contains(document.activeElement)) {
            (dialog.querySelector('[data-tour-primary]') || dialog.querySelector('button'))?.focus();
        }

        const selector = (name) => `[data-tour="${name}"]`;
        let target = document.querySelector(selector(tooltip.dataset.tourTarget));
        let bounds = target?.getBoundingClientRect();

        const group = target?.closest('ui-disclosure');
        if (group?.getClientRects().length && !group.hasAttribute('open')) {
            group.querySelector('button')?.click();
            setTimeout(positionTour, 100);
            return;
        }

        if (!bounds?.width || !bounds?.height) {
            target = document.querySelector(selector(tooltip.dataset.tourFallback || 'mobile-navigation-toggle'));
            bounds = target?.getBoundingClientRect();
        }

        if (!bounds?.width || !bounds?.height) {
            tooltip.style.left = '50%';
            tooltip.style.top = '50%';
            tooltip.style.transform = 'translate(-50%, -50%)';
            return;
        }

        if (target !== highlighted) {
            highlighted?.classList.remove('duty-tour-highlight');
            highlighted = target;
            highlighted.classList.add('duty-tour-highlight');
        }

        const sidebar = target.closest('[data-flux-sidebar]');
        if (sidebar !== highlightedSidebar) {
            highlightedSidebar?.classList.remove('duty-tour-sidebar-active');
            highlightedSidebar = sidebar;
            highlightedSidebar?.classList.add('duty-tour-sidebar-active');
        }

        const margin = 16;
        if (bounds.top < 0 || bounds.bottom > innerHeight) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            bounds = target.getBoundingClientRect();
        }

        const width = Math.min(tooltip.offsetWidth, innerWidth - margin * 2);
        const left = Math.min(Math.max(margin, bounds.left + bounds.width / 2 - width / 2), innerWidth - width - margin);
        const below = bounds.bottom + 16;
        const top = below + tooltip.offsetHeight <= innerHeight - margin
            ? below
            : Math.max(margin, bounds.top - tooltip.offsetHeight - 16);

        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
        tooltip.style.transform = 'none';
    });
}

const tourObserver = new MutationObserver(positionTour);
tourObserver.observe(document.documentElement, {
    subtree: true,
    childList: true,
    attributes: true,
    attributeFilter: ['data-onboarding-tour', 'data-tour-target', 'data-tour-fallback'],
});
window.addEventListener('resize', positionTour);
window.addEventListener('scroll', positionTour, true);
document.addEventListener('livewire:navigated', positionTour);
document.addEventListener('keydown', (event) => {
    const dialog = document.querySelector('[data-onboarding-tour], .duty-tour-dialog');
    if (!dialog) return;

    if (event.key === 'Escape') {
        event.preventDefault();
        dialog.querySelector('button[wire\\:click="dismiss"]')?.click();
    } else if (event.key === 'Tab') {
        const buttons = [...dialog.querySelectorAll('button:not([disabled])')];
        if (!buttons.length) return;
        const first = buttons[0];
        const last = buttons.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
});
positionTour();
