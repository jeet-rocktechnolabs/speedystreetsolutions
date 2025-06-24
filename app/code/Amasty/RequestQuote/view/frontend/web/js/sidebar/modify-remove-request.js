define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/url'
], function ($, confirm, urlBuilder) {
    'use strict';

    var sidebarWidgetMixin = {
        options: {
            messages: {
                messagePartOne: $.mage.__('Are you sure you would like to remove this item from the shopping cart?'),
                messagePartTwo: $.mage.__('This item is a part of the approved quote. Removing it will remove all quote items from the cart.')
            }
        },
        selectors: {
            quoteItemSelector: '.action.edit[href^="#hide-element-%id"]'
        },

        /**
         * Replace confirmation message and remove url for our quote items in minicart.
         * @private
         * @return {void}
         */
        _initContent: function () {
            this._super();

            this.element.off('click', this.options.button.remove);
            this.element.on('click', this.options.button.remove, this._removeEventHandler.bind(this));
        },

        /**
         * Remove Event Handler for our quote items in minicart.
         * @private
         * @param {Event} event
         * @return {void}
         */
        _removeEventHandler: function (event) {
            var self = this,
                itemId = $(event.currentTarget).data('cart-item'),
                isQuoteItem = $(self.selectors.quoteItemSelector.replace('%id', itemId)).length !== 0,
                confirmMessage = '',
                originalRemoveUrl = self.options.url.remove;

            event.stopPropagation();

            if (isQuoteItem) {
                confirmMessage = self.options.messages.messagePartOne + '<br>'
                    + self.options.messages.messagePartTwo;
            } else {
                confirmMessage = self.options.confirmMessage;
            }

            confirm({
                content: confirmMessage,
                actions: {
                    confirm: function () {
                        if (isQuoteItem) {
                            self.options.url.remove = urlBuilder.build('amasty_quote/quote/removeFromMinicart');
                        }
                        self._removeItem($(event.currentTarget));
                    },
                    always: function (event) {
                        event.stopImmediatePropagation();
                        self.options.url.remove = originalRemoveUrl;
                    }
                }
            });
        }
    };

    return function (targetWidget) {
        $.widget('mage.sidebar', targetWidget, sidebarWidgetMixin);

        return $.mage.sidebar;
    };
});
