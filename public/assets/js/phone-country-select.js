document.addEventListener('DOMContentLoaded', function () {
    const phoneInputs = ['celular', 'telefono_contacto']
        .map(function (id) {
            return document.getElementById(id);
        })
        .filter(Boolean);

    function keepOnlyDigits(event) {
        const input = event.target;
        const digits = input.value.replace(/\D+/g, '');

        if (input.value !== digits) {
            input.value = digits;
        }
    }

    phoneInputs.forEach(function (input) {
        input.addEventListener('input', keepOnlyDigits);
        input.addEventListener('change', keepOnlyDigits);
        input.addEventListener('blur', keepOnlyDigits);
    });

    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
        return;
    }

    const $ = window.jQuery;

    function renderCountryOption(option) {
        if (!option.id) {
            return option.text;
        }

        const countryCode = String(option.id).toLowerCase();

        return $(
            '<span class="d-inline-flex align-items-center gap-2">' +
                '<span class="flag-icon flag-icon-' + countryCode + '" style="width: 1.25rem; height: 0.95rem;"></span>' +
                '<span>' + option.text + '</span>' +
            '</span>'
        );
    }

    $('.js-country-select').select2({
        width: '100%',
        theme: 'bootstrap4',
        placeholder: 'Seleccione un pais',
        allowClear: false,
        templateResult: renderCountryOption,
        templateSelection: renderCountryOption,
        escapeMarkup: function (markup) {
            return markup;
        },
    });
});
