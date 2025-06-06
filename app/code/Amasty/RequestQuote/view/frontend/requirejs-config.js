var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Amasty_RequestQuote/js/product/catalog-add-to-cart': true
            },
            'mage/sticky': {
                'Amasty_RequestQuote/js/mage/amquote-sticky': true
            },
            'Magento_Checkout/js/sidebar': {
                'Amasty_RequestQuote/js/sidebar/modify-remove-request': true
            },
            'Amasty_CheckoutCore/js/view/checkout/summary/item/details': {
                'Amasty_RequestQuote/js/view/checkout/summary/item/details/modify-remove-request': true
            }
        }
    },
    shim: {
        'Magento_Checkout/js/view/shipping': {
            deps: [ 'Amasty_RequestQuote/js/actions/shipping/add-address' ]
        },
        'Magento_Checkout/js/view/shipping-address/list': {
            deps: [ 'Amasty_RequestQuote/js/actions/shipping/add-address' ]
        }
    }
};
