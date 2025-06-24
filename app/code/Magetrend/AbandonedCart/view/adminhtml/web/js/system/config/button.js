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
require([
    'prototype'
], function () {
    window.abandonedCartMass = function (url, storeId) {
        var r = confirm("Are you sure? ");
        if (r == true) {
            new Ajax.Request(url, {
                parameters: {
                    'store_id': storeId
                },
                loaderArea: container,
                onComplete: function (transport) {
                    if (transport.responseJSON.message) {
                        alert(transport.responseJSON.message);
                    }
                    if (transport.responseJSON.error) {
                        alert('Error: ' + transport.responseJSON.error);
                    }
                }.bind(this)
            });
        }
    };
});
