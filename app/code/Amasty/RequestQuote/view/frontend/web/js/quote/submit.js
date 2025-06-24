define([
    'jquery',
    'uiRegistry'
], function ($, uiRegistry) {
    'use strict';

    return function (config, element) {
        var form = $('.amasty-quote-update'),
            customerAttributesForm;

        $(element).click(function (event) {
            event.preventDefault();
            var postcode = $('#postcode').val().trim();
            if (!isVUKPostcode(postcode)) {
                alert('Please enter a valid UK postcode before submitting the quote.');
                return false;
            }
            var detailsForm = $('[data-form-js="am-details-form"]'),
                emailForm = $('[data-role="email-with-possible-login"]'),
                eventData = {isValid: true};

            eventData.isValid = eventData.isValid && form.valid();
            if (emailForm.length) {
                eventData.isValid = eventData.isValid && emailForm.valid();
            }
            eventData.isValid = eventData.isValid && detailsForm.valid();

            customerAttributesForm = uiRegistry.get('details.customer-attributes');
            if (customerAttributesForm && !customerAttributesForm.validate()) {
                eventData.isValid = false;
                customerAttributesForm.focusInvalid();
            }

            $('body').trigger('validateRequestQuoteForm', eventData);

            if (eventData.isValid) {
                $('<input></input>').attr('type', 'hidden')
                    .attr('name', 'remarks')
                    .attr('value', detailsForm.find('[name="quote_remark"]').val())
                    .appendTo(form);
                $('<input></input>').attr('type', 'hidden')
                    .attr('name', 'update_cart_action')
                    .attr('value', 'submit')
                    .appendTo(form);
                $('<input></input>').attr('type', 'hidden')
                    .attr('name', 'email')
                    .attr('value', detailsForm.find('[name="username"]').val())
                    .appendTo(form);

                detailsForm.find('input, textarea, select').each(function (index, input) {
                    var newInput = $('<input></input>').attr('type', 'hidden')
                        .attr('name', $(input).attr('name'))
                        .attr('value', $(input).val())
                        .appendTo(form);
                    if ($(input).attr('type') === 'file') {
                        newInput.attr('type', 'file');
                        newInput.files = input.files;
                        $(input).removeAttr('name');
                        $('[name="' + newInput.attr('name') + '"]')[0].files = input.files;
                    }
                });

                $(element).attr('disabled', true);

                $('body').trigger('beforeSubmitRequestQuoteForm', {form: form});

                form.submit();
            }
        });
    };

    function isVUKPostcode(postcode) {
        var postcodeRegex = /^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9]?[A-Za-z]))))\s?[0-9][A-Za-z]{2})$/;
        return postcodeRegex.test(postcode);
    }
});
