document.addEventListener('DOMContentLoaded', function () {

    const source = document.querySelector('[name="income_source"]');
    const orderField = document.querySelector('[name="id_order"]');

    function toggleOrderField() {

        if (!source || !orderField) {
            return;
        }

        const value = document.querySelector('[name="income_source"]:checked');

        if (!value) {
            return;
        }

        const wrapper = orderField.closest('.form-group');

        if (!wrapper) {
            return;
        }

        if (value.value === 'order') {
            wrapper.style.display = '';
        } else {
            wrapper.style.display = 'none';
            orderField.value = '';
        }
    }

    document.querySelectorAll('[name="income_source"]').forEach(function (radio) {
        radio.addEventListener('change', toggleOrderField);
    });

    toggleOrderField();

});