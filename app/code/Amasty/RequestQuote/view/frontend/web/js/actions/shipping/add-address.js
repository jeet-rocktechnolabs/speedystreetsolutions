define([
    'jquery',
    './create-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
], function ($, createShippingAddress, checkoutData, uiRegistry) {
    'use strict';

    if (window.checkoutConfig
        && window.checkoutConfig.amasty_quote
        && window.checkoutConfig.amasty_quote.shipping_address
    ) {
        checkoutData.setSelectedShippingAddress('amasty_quote_address');
        createShippingAddress(window.checkoutConfig.amasty_quote.shipping_address);
        // dont show add new address button
        uiRegistry.get('checkout.steps.shipping-step.shippingAddress', function (shipping) {
            shipping.isNewAddressAdded(true);
        });
    }
});
