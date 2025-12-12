document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('[data-toggle-password]');
    const input = document.getElementById('password');
    if (!btn || !input) return;

    btn.addEventListener('click', () => {
        const isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');

        btn.setAttribute('aria-pressed', String(isPassword));

        const icon = btn.querySelector('i');
        icon.classList.toggle('bi-eye', !isPassword);
        icon.classList.toggle('bi-eye-slash', isPassword);
    });
});
