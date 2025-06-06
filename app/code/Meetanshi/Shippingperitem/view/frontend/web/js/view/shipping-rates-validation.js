/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingperitemShippingRatesValidator,
        shippingperitemShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('shippingperitem', shippingperitemShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('shippingperitem', shippingperitemShippingRatesValidationRules);
        return Component;
    }
);
