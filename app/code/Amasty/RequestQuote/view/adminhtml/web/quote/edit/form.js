define([
    'jquery',
    'Amasty_RequestQuote/quote/edit/scripts'
], function (jQuery) {
    'use strict';

    var $el = jQuery('#quote-config'),
        config,
        baseUrl,
        order,
        payment;

    if (!$el.length || !$el.val()) {
        return;
    }

    config = JSON.parse($el.val());
    var quote = new AmAdminQuote(config);
    quote.setLoadBaseUrl(jQuery('#base-url').val());
    window.quote = quote;

});
