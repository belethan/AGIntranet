import { Controller } from '@hotwired/stimulus';

/**
 * Sidebar controller
 *
 * Responsibilities:
 * - Toggle sidebar on mobile
 * - Toggle collapsed state (desktop)
 *
 * CSS contract:
 * - body.sidebar-open
 * - body.sidebar-collapsed
 */
export default class extends Controller {

    static targets = ['toggle'];

    connect() {
        // Ensure sidebar state consistency on load
        this._syncState();
    }

    toggle() {
        document.body.classList.toggle('sidebar-open');
    }

    collapse() {
        document.body.classList.toggle('sidebar-collapsed');
    }

    closeOnResize() {
        if (window.innerWidth >= 992) {
            document.body.classList.remove('sidebar-open');
        }
    }

    _syncState() {
        window.addEventListener('resize', () => this.closeOnResize());
    }
}
