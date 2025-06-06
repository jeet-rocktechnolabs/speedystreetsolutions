define([
    'underscore',
    'mage/translate',
    'Magento_Customer/js/customer-data'
], function (_, $t, customerData) {
    'use strict';

    var mixin = {
        defaults: {
            messages: {
                messagePartOne: $t('Are you sure you would like to remove this item from the shopping cart?'),
                messagePartTwo: $t('This item is a part of the approved quote. Removing it will remove all quote items from the cart.')
            }
        },

        /**
         * @public
         * @param {Object} item
         * @return {void}
         */
        deleteItem: function (item) {
            var originalMessage = this.messages.deleteItem;

            if (this._isAmastyQuoteItem(item)) {
                this.messages.deleteItem = this.messages.messagePartOne + '<br>'
                    + this.messages.messagePartTwo;
            }

            this._super();

            this.messages.deleteItem = originalMessage;
        },

        /**
         * @param {Object} item
         * @return {Boolean}
         * @private
         */
        _isAmastyQuoteItem: function (item) {
            var cartData = customerData.get('cart')(),
                quoteItemData = {},
                isAmastyQuoteItem = false;

            if (!_.isUndefined(cartData.items)) {
                quoteItemData = _.find(cartData.items, function (itemData) {
                    return itemData.item_id == item.item_id;
                });

                if (quoteItemData) {
                    isAmastyQuoteItem = !!quoteItemData['is_amasty_quote_item'];
                }
            }

            return isAmastyQuoteItem;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
