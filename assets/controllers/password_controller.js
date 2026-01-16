import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'icon'];

    connect() {
        console.log('Password controller connected');
    }

    toggle() {
        console.log('Toggle clicked');
        if (!this.hasInputTarget) {
            return;
        }

        const isPassword = this.inputTarget.type === 'password';
        this.inputTarget.type = isPassword ? 'text' : 'password';

        if (this.hasIconTarget) {
            this.iconTarget.classList.toggle('bi-eye', !isPassword);
            this.iconTarget.classList.toggle('bi-eye-slash', isPassword);
        }
    }
}
