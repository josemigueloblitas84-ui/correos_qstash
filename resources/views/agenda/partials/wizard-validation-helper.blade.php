@once
    @push('styles')
        <style>
            .wizard-front-error {
                display: block;
                width: 100%;
                margin-top: 0.35rem;
                font-size: 0.875rem;
                color: var(--bs-danger);
            }

            .wizard-front-error.d-none {
                display: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                if (window.WizardValidationUtils) {
                    return;
                }

                const getConfig = (field, configMap = {}) => configMap[field.name] || null;

                const getErrorElement = (field, create = false) => {
                    const anchor = field.closest('.input-group') || field;
                    const container = anchor.parentElement;

                    if (!container) {
                        return null;
                    }

                    let errorElement = container.querySelector(`.wizard-front-error[data-field-error="${field.name}"]`);

                    if (!errorElement && create) {
                        errorElement = document.createElement('div');
                        errorElement.className = 'wizard-front-error d-none';
                        errorElement.dataset.fieldError = field.name;
                        anchor.insertAdjacentElement('afterend', errorElement);
                    }

                    return errorElement;
                };

                const clearFieldError = (field) => {
                    field.setCustomValidity('');
                    field.classList.remove('is-invalid', 'is-valid');

                    const errorElement = getErrorElement(field);

                    if (errorElement) {
                        errorElement.textContent = '';
                        errorElement.classList.add('d-none');
                    }
                };

                const setFieldValid = (field) => {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                    field.setCustomValidity('');
                    const errorElement = getErrorElement(field);

                    if(errorElement) {
                        errorElement.textContent = '';
                        errorElement.classList.add('d-none');
                    }

                    return true;
                }

                const setFieldError = (field, message) => {
                    field.setCustomValidity(message);
                    field.classList.add('is-invalid');

                    const errorElement = getErrorElement(field, true);

                    if (errorElement) {
                        errorElement.textContent = message;
                        errorElement.classList.remove('d-none');
                    }

                    return false;
                };

                const applyAttributes = (field, config) => {
                    if (!config) {
                        return;
                    }

                    if (config.maxLength && field.tagName !== 'SELECT') {
                        field.setAttribute('maxlength', config.maxLength);
                    }

                    if (config.minLength && field.tagName !== 'SELECT') {
                        field.setAttribute('minlength', config.minLength);
                    }

                    if (config.attributes) {
                        Object.entries(config.attributes).forEach(([attribute, value]) => {
                            field.setAttribute(attribute, value);
                        });
                    }
                };

                const sanitizeField = (field, configMap = {}) => {
                    const config = getConfig(field, configMap);

                    if (!config || typeof config.sanitize !== 'function' || typeof field.value !== 'string') {
                        return;
                    }

                    const sanitized = config.sanitize(field.value);

                    if (sanitized !== field.value) {
                        field.value = sanitized;
                    }
                };

                const validateField = (field, configMap = {}) => {
                    const config = getConfig(field, configMap);

                    clearFieldError(field);
                    sanitizeField(field, configMap);

                    const value = typeof field.value === 'string' ? field.value.trim() : field.value;

                    if (!config) {
                        if (!field.checkValidity()) {
                            return setFieldError(field, field.validationMessage);
                        }

                        return true;
                    }

                    if ((field.required || config.required) && value === '') {
                        return setFieldError(field, `El campo ${config.label || field.name} es obligatorio.`);
                    }

                    if (value === '') {
                        field.classList.remove('is-valid');
                        return true;
                    }

                    if (config.minLength && value.length < config.minLength) {
                        return setFieldError(
                            field,
                            `El campo ${config.label || field.name} debe tener al menos ${config.minLength} caracteres.`
                        );
                    }

                    if (config.maxLength && value.length > config.maxLength) {
                        return setFieldError(
                            field,
                            `El campo ${config.label || field.name} no puede superar ${config.maxLength} caracteres.`
                        );
                    }

                    if (config.allowed && !config.allowed.includes(String(value).toLowerCase())) {
                        return setFieldError(field, config.message || `El campo ${config.label || field.name} no es valido.`);
                    }

                    if (config.regex && !config.regex.test(String(value))) {
                        return setFieldError(field, config.message || `El campo ${config.label || field.name} no es valido.`);
                    }

                    if (typeof config.custom === 'function') {
                        const customResult = config.custom(value, field, configMap);

                        if (customResult !== true) {
                            return setFieldError(
                                field,
                                typeof customResult === 'string'
                                    ? customResult
                                    : (config.message || `El campo ${config.label || field.name} no es valido.`)
                            );
                        }
                    }

                    return setFieldValid(field);
                };

                const validateFields = (fields, configMap = {}) => {
                    for (const field of fields) {
                        if (!validateField(field, configMap)) {
                            field.focus();
                            return false;
                        }
                    }

                    return true;
                };

                const attachFieldEvents = (form, configMap = {}) => {
                    const fields = Array.from(form.querySelectorAll('input, select, textarea'))
                        .filter((field) => field.type !== 'hidden');

                    fields.forEach((field) => {
                        const config = getConfig(field, configMap);

                        applyAttributes(field, config);
                        sanitizeField(field, configMap);

                        field.addEventListener('input', () => {
                            sanitizeField(field, configMap);

                            if (field.classList.contains('is-invalid')) {
                                validateField(field, configMap);
                            }
                        });

                        field.addEventListener('change', () => {
                            validateField(field, configMap);
                        });

                        field.addEventListener('blur', () => {
                            validateField(field, configMap);
                        });
                    });
                };

                window.WizardValidationUtils = {
                    attachFieldEvents,
                    sanitizeField,
                    validateField,
                    validateFields,
                    clearFieldError,
                    setFieldError,
                };
            })();
        </script>
    @endpush
@endonce
{{--Nota: Para usar esta validacion en cualquier formulario se debe usar:
@include('agenda.partials.wizard-validation-helper')
definir formValidationConfig
llamar attachFieldEvents(form, formValidationConfig)
llamar validateFields(..., formValidationConfig)
--}}
