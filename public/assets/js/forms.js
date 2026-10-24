function validateField(field) {
    const value = field.value.trim();
    if (field.required && !value) return 'Vui lòng nhập thông tin này.';
    if (!value) return '';
    if (field.type === 'email' && field.validity.typeMismatch) return 'Vui lòng nhập email đúng định dạng.';
    if (field.name === 'phone' && !/^(0|\+84)[0-9]{9}$/.test(value.replace(/[\s.-]/g, ''))) return 'Số điện thoại gồm 10 chữ số, bắt đầu bằng 0 (hoặc +84).';
    if (field.minLength > 0 && value.length < field.minLength) return `Vui lòng nhập ít nhất ${field.minLength} ký tự.`;
    if (field.maxLength > 0 && value.length > field.maxLength) return `Vui lòng nhập tối đa ${field.maxLength} ký tự.`;
    return '';
}

function displayError(field, message) {
    const target = document.getElementById(`${field.id}-error`);
    if (target) target.textContent = message;
    if (message) field.setAttribute('aria-invalid', 'true');
    else field.removeAttribute('aria-invalid');
}

export function showFormResult(form, message, error = false) {
    const result = form.querySelector('.form-result');
    if (!result) return;
    result.textContent = message;
    result.hidden = false;
    result.classList.toggle('is-error', error);
    result.focus();
}

export function initForms({ checkout }) {
    const form = document.querySelector('[data-checkout-form]');
    if (form) {
        const fields = [...form.querySelectorAll('input:not([type=radio]):not([type=hidden]), textarea')];
        for (const field of fields) {
            field.addEventListener('input', () => {
                if (field.hasAttribute('aria-invalid')) displayError(field, validateField(field));
            });
        }
        form.addEventListener('submit', event => {
            event.preventDefault();
            let firstInvalid = null;
            for (const field of fields) {
                const error = validateField(field);
                displayError(field, error);
                if (error && !firstInvalid) firstInvalid = field;
            }
            if (firstInvalid) { firstInvalid.focus(); return; }
            checkout(form);
        });
    }
    for (const button of document.querySelectorAll('[data-toggle-password]')) {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', visible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    }
}
