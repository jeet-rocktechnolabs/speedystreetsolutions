define([
    'jquery'
], function ($) {
    'use strict';

    var widgetMixin = {
        classes: {
            cartSummarySidebar: 'amquote-cart-summary'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            if ('ResizeObserver' in window && this.element.hasClass(this.classes.cartSummarySidebar)) {
                this._initResizeObserver().observe(this.element[0]);
            }

            return this._super();
        },

        /**
         * Add a ResizeObserver to check for an element dimensions change and call the reset method
         *
         * @return {Object} - ResizeObserver
         * @private
         */
        _initResizeObserver: function () {
            this.resizeObserver = new ResizeObserver(function () {
                this.reset();
            }.bind(this));

            return this.resizeObserver;
        }
    };

    return function (parentWidget) {
        $.widget('mage.sticky', parentWidget, widgetMixin);

        return $.mage.sticky;
    };
});
