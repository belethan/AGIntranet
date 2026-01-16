import { Application } from '@hotwired/stimulus';
import PasswordController from './controllers/password_controller.js';

console.log('STIMULUS BOOTSTRAP LOADED');

const application = Application.start();
application.register('password', PasswordController);

// Expose pour debug si besoin
window.application = application;
