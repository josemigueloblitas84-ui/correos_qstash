document.addEventListener('DOMContentLoaded', function () {
    function buildRegex(pattern, flags, fallback) {
        try {
            return new RegExp(pattern, flags);
        } catch (error) {
            return fallback;
        }
    }

    const allowedCharactersRegex = buildRegex(
        '^[\\p{Latin}\\d\\p{P}\\p{S}]+$',
        'u',
        /^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỿ0-9!-/:-@[-`{-~]+$/
    );
    const upperRegex = buildRegex('\\p{Lu}', 'u', /[A-ZÀ-ÖØ-ÞĀ-Ž]/);
    const lowerRegex = buildRegex('\\p{Ll}', 'u', /[a-zà-öø-ÿā-ž]/);
    const specialRegex = buildRegex('[\\p{P}\\p{S}]', 'u', /[!-/:-@[-`{-~]/);

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
        const hasOnlyAllowedCharacters = value.length === 0 || allowedCharactersRegex.test(value);

        const checks = {
            length: value.length >= 8,
            upper: upperRegex.test(value),
            lower: lowerRegex.test(value),
            number: /\d/.test(value),
            special: specialRegex.test(value)
        };

        setRuleState(rules.length, checks.length);
        setRuleState(rules.upper, checks.upper);
        setRuleState(rules.lower, checks.lower);
        setRuleState(rules.number, checks.number);
        setRuleState(rules.special, checks.special);

        const passed = hasOnlyAllowedCharacters
            ? Object.values(checks).filter(Boolean).length
            : 0;
        const percent = (passed / 5) * 100;

        bar.style.width = percent + '%';
        bar.setAttribute('aria-valuenow', percent);
        text.textContent = hasOnlyAllowedCharacters
            ? 'Seguridad de contrasena: ' + percent + '%'
            : 'Seguridad de contrasena: 0% (solo letras latinas, numeros y simbolos)';

        bar.classList.remove('bg-danger', 'bg-warning', 'bg-info', 'bg-success');

        if (!hasOnlyAllowedCharacters && value.length > 0) {
            bar.classList.add('bg-danger');
        } else if (percent <= 20) {
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
