define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.amQuoteTrimInput', {
        _create: function () {
            this.options.input = $(this.element);
            this._bind();
        },

        _bind: function () {
            if (this.options.input.length) {
                this._on(this.options.input, {
                    'change': this._trimInput,
                    'keyup': this._trimInput,
                    'paste': this._trimInput
                });
            }
        },

        _trimInput: function () {
            var input = this._getInputValue().trim();

            this.options.input.val(input);
        },

        _getInputValue: function () {
            return this.options.input.val();
        }
    });

    return $.mage.amQuoteTrimInput;
});
