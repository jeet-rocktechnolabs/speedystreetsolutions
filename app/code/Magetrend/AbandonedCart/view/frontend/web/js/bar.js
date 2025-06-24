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

    $.widget('mage.abandonedCartBar', {
        /**
         * Object options
         */
        options: {
            isDevMode: 0,
            tabCounterCookieName: 'mtactc',
            flagCookieName: 'mtacrv',
            hashCookieName: '',
            front_name: 'index'
        },

        ignoreFrontNames: {
            checkout_index_index: 1
        },

        /**
         * Initialize script
         *
         * @private
         */
        _create: function() {
            if (!this.ignoreFrontNames[this.options.front_name]) {
                this._loadBar({
                    store_id: this.options.store_id,
                    started_at: new Date().getTime(),
                    is_visitor_back: this._isVisitorBack()?1:0
                });
            }
            this._initEvents();
        },

        _initEvents: function () {
            $(window).on('unload', this, this._onUnloadWindows);
            this._tabCount(+1);
            this._setCookie(this.options.flagCookieName, 0, 0);
        },

        _onUnloadWindows: function(event) {
            var tabCount = event.data._getCookie(event.data.options.tabCounterCookieName);
            if (tabCount == 1) {
                event.data._setCookie(event.data.options.flagCookieName, new Date().getTime(), 60*24);
            }
            event.data._tabCount(-1);
        },

        _tabCount: function (i) {
            var tabCount = this._getCookie(this.options.tabCounterCookieName);
            if (tabCount == '' ||  isNaN(tabCount) || tabCount < 0 ) {
                tabCount = 0;
            } else {
                tabCount = parseInt(tabCount);
            }

           this._setCookie(
               this.options.tabCounterCookieName,
               tabCount + i,
               60
           );
        },

        _isVisitorBack: function()
        {
            var lastUnload = parseInt(this._getCookie(this.options.flagCookieName));
            if (lastUnload == '' || lastUnload == 0 || isNaN(lastUnload)) {
                return false;
            }
            var currentDate =  new Date().getTime();
            if (currentDate - lastUnload <= 10000) {
                return false;
            }

            return true;
        },


        /**
         * Load and show available message
         *
         * @param requestData
         * @private
         */
        _loadBar: function (requestData) {
            $.ajax({
                method: "GET",
                url: this.options.url.load,
                data: requestData
            }).done(function(response) {
                if (response.success == true) {
                    $('body').append(response.data);
                }
            });
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
        },

        /**
         * Returns cookie value
         *
         * @param key
         * @returns {string}|{null}
         * @private
         */
        _getCookie: function(key) {
            var nameEQ = escape(key) + "=";
            var ca = document.cookie.split(';');
            for (var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) {
                    return unescape(c.substring(nameEQ.length,c.length));
                }
            }
            return '';
        }
    });

    return $.mage.abandonedCartBar;
});
