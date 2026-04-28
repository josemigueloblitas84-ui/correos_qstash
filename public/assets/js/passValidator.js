document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (!input || !icon) {
                return;
            }

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        });
    });

    const passwordInput = document.getElementById('contrasenaUsuario');
    const bar = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');

    if (!passwordInput || !bar || !text) {
        return;
    }

    const rules = {
        length: document.getElementById('rule-length'),
        upper: document.getElementById('rule-upper'),
        lower: document.getElementById('rule-lower'),
        number: document.getElementById('rule-number'),
        special: document.getElementById('rule-special')
    };

    function setRuleState(element, valid) {
        if (!element) {
            return;
        }
        element.classList.toggle('is-active', valid);
    }

    function updatePasswordStrength() {
        const value = passwordInput.value;

        const checks = {
            length: value.length >= 8,
            upper: /[A-Z]/.test(value),
            lower: /[a-z]/.test(value),
            number: /\d/.test(value),
            special: /[^A-Za-z0-9]/.test(value)
        };

        setRuleState(rules.length, checks.length);
        setRuleState(rules.upper, checks.upper);
        setRuleState(rules.lower, checks.lower);
        setRuleState(rules.number, checks.number);
        setRuleState(rules.special, checks.special);

        const passed = Object.values(checks).filter(Boolean).length;
        const percent = (passed / 5) * 100;

        bar.style.width = percent + '%';
        bar.setAttribute('aria-valuenow', percent);
        text.textContent = 'Seguridad de contraseña: ' + percent + '%';

        bar.classList.remove('bg-danger', 'bg-warning', 'bg-info', 'bg-success');

        if (percent <= 20) {
            bar.classList.add('bg-danger');
        } else if (percent <= 40) {
            bar.classList.add('bg-warning');
        } else if (percent <= 80) {
            bar.classList.add('bg-info');
        } else {
            bar.classList.add('bg-success');
        }
    }

    passwordInput.addEventListener('input', updatePasswordStrength);
    updatePasswordStrength();
});
