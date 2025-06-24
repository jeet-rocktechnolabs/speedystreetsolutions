define([
    'prototype'
], function () {
    'use strict';

    return function () {
        /**
         * Loads shipping options according to address data.
         *
         * @return {Boolean}
         */
        window.AdminOrder.prototype.loadShippingRates = function () {
            var addressContainer = this.shippingAsBilling
                    ? 'billingAddressContainer'
                    : 'shippingAddressContainer',
                data = this.serializeData(this[addressContainer]).toObject();

            data['shipping_as_billing'] = +this.shippingAsBilling; // add to fix shipping method choosing
            data['collect_shipping_rates'] = 1;
            this.isShippingMethodReseted = false;
            this.loadArea(['shipping_method', 'totals'], true, data);

            return false;
        };
    };
});
