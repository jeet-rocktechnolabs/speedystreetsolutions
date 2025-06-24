define([
    'underscore',
    'uiCollection',
    'Magento_Checkout/js/checkout-data'
], function (_, Component, checkoutData) {
    'use strict';

    return Component.extend({
        /**
         * @param {Object} elem
         * @returns {Object}
         */
        initElement: function (elem) {
            if (elem.componentType !== 'group') {
                this.initObservableAttribute(elem);
            }
            this._super(elem);
            return this;
        },

        /**
         * @param {Object} attribute
         * @returns {void}
         */
        initObservableAttribute: function (attribute) {
            var savedValue = this.getSavedValue(attribute.dataScope);

            if (savedValue) {
                attribute.value(savedValue);
            }

            attribute.value.subscribe(function (value) {
                var shippingData = this.getShippingData();

                shippingData.amasty_quote_attributes[attribute.dataScope] = value;
                checkoutData.setShippingAddressFromData(shippingData);
            }.bind(this));
        },

        /**
         * @returns {Object}
         */
        getShippingData: function () {
            var shippingData = checkoutData.getShippingAddressFromData();

            if (shippingData === null) {
                shippingData = {
                    amasty_quote_attributes: {quote_id: this.quoteId}
                };
            } else if (!shippingData.amasty_quote_attributes
                || !shippingData.amasty_quote_attributes.quote_id
                || shippingData.amasty_quote_attributes.quote_id !== this.quoteId
            ) {
                shippingData.amasty_quote_attributes = {quote_id: this.quoteId};
            }

            return shippingData;
        },

        /**
         * @param {String} key
         * @returns {mixed}
         */
        getSavedValue: function (key) {
            var data = this.getShippingData();

            return data.amasty_quote_attributes[key];
        },

        /**
         * @returns {Boolean}
         */
        validate: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('data.validate');

            return !this.source.get('params.invalid');
        },

        /**
         * Tries to set focus on first invalid form field.
         *
         * @returns {void}
         */
        focusInvalid: function () {
            var invalidField = _.find(this.delegate('checkInvalid'));

            if (!_.isUndefined(invalidField) && _.isFunction(invalidField.focused)) {
                invalidField.focused(true);
            }
        }
    });
});
