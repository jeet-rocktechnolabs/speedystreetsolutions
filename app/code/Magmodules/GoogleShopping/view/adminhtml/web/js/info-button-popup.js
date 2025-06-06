require([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    let defaultOptions = {
        modalClass: 'mm-ui-modal googleshopping',
        type: 'popup',
        responsive: true,
        innerScroll: true,
        title: '',
        buttons: [
            {
                text: $.mage.__('Close window'),
                class: 'mm-ui-modal-close',
                click: function () {
                    this.closeModal();
                },
            }
        ]
    };

    // Init modals
    modal(defaultOptions, $('#conditionalInfo'));
    modal(defaultOptions, $('#multisourceInfo'));

    $(".conditional-modal").on('click', () => $("#conditionalInfo").modal("openModal"));
    $(".multisource-modal").on('click', () => $("#multisourceInfo").modal("openModal"));
});
