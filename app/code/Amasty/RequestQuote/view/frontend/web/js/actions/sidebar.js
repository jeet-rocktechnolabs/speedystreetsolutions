define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'sidebar'
], function ($, customerData, sidebar) {
    'use strict';

    $.widget('amasty.quoteSidebar', sidebar, {
        /**
         * @inheritDoc
         */
        _removeItemAfter: function (elem) {
            var productData = customerData.get('quotecart')().items.find(function (item) {
                return Number(elem.data('cart-item')) === Number(item['item_id']);
            });

            $(document).trigger('ajax:removeFromQuoteCart', productData['product_sku']);
        },

        /**
         * @inheritDoc
         */
        _initContent: function () {
            var element = this.element.find(this.options.button.checkout);

            this._super();

            element.on('click ' + this.options.button.checkout, function () {
                element.prop('disabled', true);
                window.location.href = this.options.url.checkout;
            }.bind(this));
        }
    });

    return $.amasty.quoteSidebar;
});
