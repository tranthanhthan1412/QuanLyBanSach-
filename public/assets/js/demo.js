// No fetch, form POST, authentication state, or personal-data persistence here.
// Form submission only validates and calls the explicit demo callbacks below.
function validateField(field, form) {
    const value = field.value.trim();
    if (field.required && !value) return 'Vui lòng nhập thông tin này.';
    if (!value) return '';
    if (field.type === 'email' && field.validity.typeMismatch) return 'Vui lòng nhập email đúng định dạng, ví dụ ban@example.com.';
    if (field.name === 'phone' && !/^(0|\+84)[0-9]{9}$/.test(value.replace(/[\s.-]/g, ''))) return 'Nhập số điện thoại gồm 10 chữ số, bắt đầu bằng 0 (hoặc +84).';
    if (field.name === 'password' && field.value.length < 8) return 'Mật khẩu mẫu cần ít nhất 8 ký tự.';
    if (field.name === 'confirm' && field.value !== form.elements.password.value) return 'Mật khẩu nhập lại chưa khớp.';
    if (field.minLength > 0 && value.length < field.minLength) return `Vui lòng nhập ít nhất ${field.minLength} ký tự.`;
    if (field.maxLength > 0 && value.length > field.maxLength) return `Vui lòng nhập tối đa ${field.maxLength} ký tự.`;
    return '';
}

function displayError(field, message) {
    const target = document.getElementById(`${field.id}-error`)
        || (field.id === 'newsletter-email' ? document.getElementById('newsletter-error') : null);
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

export function initDemoForms({ checkout, notify }) {
    for (const form of document.querySelectorAll('[data-demo-form]')) {
        const fields = [...form.querySelectorAll('input:not([type=radio]):not([type=hidden]), textarea')];
        for (const field of fields) {
            field.addEventListener('input', () => {
                if (field.hasAttribute('aria-invalid')) displayError(field, validateField(field, form));
            });
        }
        form.addEventListener('submit', event => {
            event.preventDefault();
            let firstInvalid = null;
            for (const field of fields) {
                const error = validateField(field, form);
                displayError(field, error);
                if (error && !firstInvalid) firstInvalid = field;
            }
            if (firstInvalid) { firstInvalid.focus(); return; }
            const kind = form.dataset.demoForm;
            if (kind === 'checkout') { checkout(form); return; }
            form.reset();
            for (const input of form.querySelectorAll('input[name=password], input[name=confirm]')) input.type = 'password';
            for (const toggle of form.querySelectorAll('[data-toggle-password]')) {
                toggle.setAttribute('aria-pressed', 'false');
                toggle.setAttribute('aria-label', 'Hiện mật khẩu');
            }
            const message = kind === 'newsletter'
                ? 'Đã kiểm tra email mẫu. Chưa kết nối backend; không đăng ký nhận tin, không gửi hoặc lưu email.'
                : kind === 'register'
                    ? 'Thông tin mẫu hợp lệ. Chưa tạo tài khoản vì chưa kết nối backend. Mật khẩu và thông tin cá nhân đã được xóa khỏi form.'
                    : 'Thông tin mẫu đúng định dạng. Đây không phải xác thực đăng nhập; chưa có phiên đăng nhập. Mật khẩu và email đã được xóa khỏi form.';
            showFormResult(form, message);
            notify('Đã hoàn tất kiểm tra form mô phỏng.');
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
