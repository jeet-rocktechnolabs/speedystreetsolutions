var config = {
    config: {
        mixins: {
            'Magento_Sales/order/create/scripts': {
                'Amasty_RequestQuote/order/create/scripts-mixin': true
            },
            'mage/validation': {
                'Amasty_RequestQuote/js/mixin-validator-url-key': true
            }
        }
    }
};
