define([

], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            isFullMode: function () {
                var result = this._super();

                if (!this.getTotals()) {
                    return false;
                }

                return true;
            }
        });
    };
});
