define([
    'ko',
    'uiComponent',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder'
], function (ko, Component, storage, urlBuilder) {
    'use strict';

    return Component.extend({
        remark: ko.observable(window.checkoutConfig.quoteData['remarks']),
        checkDelay: 2000,
        remarkCheckTimeout: 0,
        isLoading: ko.observable(false),
        attributeRenderer: [],
        attributes: [],

        /**
         * @return {Object}
         */
        initObservable: function () {
            var self = this;
            this.remark.subscribe(function (value) {
                clearTimeout(self.remarkCheckTimeout);
                self.isLoading(true);

                self.remarkCheckTimeout = setTimeout(function () {
                    storage.put(
                        self._getUrl(),
                        JSON.stringify({'remark': value}),
                        false
                    ).always(function () {
                        self.isLoading(false);
                    });
                }, self.checkDelay);
            });

            this._super();

            return this;
        },

        /**
         * @private
         * @returns {String}
         */
        _getUrl: function () {
            return urlBuilder.createUrl('/amasty_quote/updateRemark', {});
        },

        /**
         * @returns {Boolean}
         */
        notLoggedIn: function () {
            return !window.checkoutConfig.isCustomerLoggedIn;
        }
    });
});
