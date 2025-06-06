define([
    'Magento_Ui/js/form/components/group',
    'uiRegistry'
], function (Component, registry) {
    'use strict';

    return Component.extend({
        /**
         * @param {Object} elem
         * @returns {Object}
         */
        initElement: function (elem) {
            registry.get(this.parentName).initObservableAttribute(elem);
            this._super(elem);
            return this;
        },
    });
});
