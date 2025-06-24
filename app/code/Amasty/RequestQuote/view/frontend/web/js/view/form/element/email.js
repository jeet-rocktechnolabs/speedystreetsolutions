define([
    'jquery',
    'Magento_Checkout/js/view/form/element/email',
    'Amasty_RequestQuote/js/actions/check-email-availability',
    'Magento_Checkout/js/checkout-data'
], function ($, EmailComponent, checkEmailAvailability, checkoutData) {
    'use strict';

    return EmailComponent.extend({
        /**
         * @returns {void}
         */
        checkEmailAvailability: function () {
            this.validateRequest();
            this.isEmailCheckComplete = $.Deferred();
            // Clean up errors on email
            $(this.emailInputId).removeClass('mage-error').parent().find('.mage-error').remove();
            this.isLoading(true);
            this.checkRequest = checkEmailAvailability(this.isEmailCheckComplete, this.email());

            $.when(this.isEmailCheckComplete).done(function () {
                this.isPasswordVisible(false);
                checkoutData.setCheckedEmailValue('');
            }.bind(this)).fail(function () {
                this.isPasswordVisible(true);
                checkoutData.setCheckedEmailValue(this.email());
            }.bind(this)).always(function () {
                this.isLoading(false);
            }.bind(this));
        },
    });
});
