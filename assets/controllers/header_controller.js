import { Controller } from '@hotwired/stimulus';

/**
 * Header controller
 *
 * Responsibilities:
 * - Fullscreen toggle
 * - Theme (light/dark) toggle
 */
export default class extends Controller {

    static targets = ['themeToggle'];

    connect() {
        this._loadTheme();
    }

    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', nextTheme);
        localStorage.setItem('theme', nextTheme);
    }

    _loadTheme() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
    }
}
