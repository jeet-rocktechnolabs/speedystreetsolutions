/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

define(['jquery'], function ($) {
        'use strict';

    $.widget('mage.abandonedCartBarBasic', {
        /**
         * Object options
         */
        options: {
            barSelector: '#bar',
            position: 'top',
            show_after: 0,
            cookieName: 'mtacbar'
        },

        /**
         * Initialize script
         *
         * @private
         */
        _create: function() {
            var main = this;
            setTimeout(function () {
                main._showBar();
            }, this._getShowAfter());
        },

        _getShowAfter: function () {
            var delay = parseInt(this.options.show_after);
            var showAfter = (delay * 1000) - (new Date().getTime() - this.options.started_at);
            return showAfter > 0 ?showAfter:0;
        },

        _getHideAfter: function () {
            var delay = parseInt(this.options.hide_after);
            return delay * 1000;
        },

        _showBar: function () {
            if (this.options.position == 'top') {
                this._showBarTop();
            }
            this._initEvents();
        },

        _showBarTop: function () {
            $(this.options.barSelector).prependTo("body");
            var height = $(this.options.barSelector).height();
            $(this.options.barSelector).css('height', 0).show().animate({
                height: height+'px'
            }, 1000);
            this._setCookie(this.options.cookieName, 1, 99, true);
        },

        _initEvents: function () {
            $(this.options.barSelector+ ' .ac-bar-close a').on('click', this, this._onClickCloseEvent);
            var autoHideAfter = this._getHideAfter();
            if (autoHideAfter > 0) {
                var main = this;
                setTimeout(function () {
                    main._hideBar();
                }, autoHideAfter);
            }
        },

        _onClickCloseEvent: function(event) {
            event.preventDefault();
            event.data._hideBar();
        },

        _hideBar: function () {
            $(this.options.barSelector).animate({
                height: '0'
            }, 500);
        },


        /**
         * Set cookie
         *
         * @param key
         * @param value
         * @param cookieLifeTime
         * @param session
         * @private
         */
        _setCookie: function(key, value, cookieLifeTime, session) {
            var now = new Date();
            var time = now.getTime();
            time += 60 * 1000 * cookieLifeTime;
            now.setTime(time);
            if (session && session == true) {
                var expires = "; expires="+0;
            } else {
                var expires = "; expires="+now.toUTCString();
            }
            document.cookie = escape(key)+"="+escape(value)+expires+"; path=/";
        }
    });

    return $.mage.abandonedCartBarBasic;
});
