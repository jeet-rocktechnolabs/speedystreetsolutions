/**
 * Custom route name validation rule.
 */
define([
    'jquery',
    'mage/translate',
    'jquery/validate'
], function ($) {
    'use strict';

    return function (validator) {
        $.validator.addMethod(
            'amasty-validate-url-key',
            function (value) {
                return $.mage.isEmptyNoTrim(value) || /^[a-zA-Z0-9][a-zA-Z0-9_-]+?$/.test(value);
            },
            $.mage.__('Please enter a valid URL Key (Ex: "example-page").')
        );

        return validator;
    };
});
