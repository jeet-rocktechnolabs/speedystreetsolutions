define([
    'jquery',
    'uiClass',
    'amasty-fancyambox'
], function ($, Class, fancyambox) {
    var amHidePriceForm = Class.extend({
        products: {},
        customer: null,
        selectors: {
            amHidePricePopup: '[data-amhide="AmastyHidePricePopup"][data-product-id="%s"]:not(.observed)',
            onClickPopup: 'observed',
            amHideClosePopup: '[data-amhide-js="close-popup"]',
            formClass: '.amform-form.amhideprice-form',
            formId: '#amhideprice-form',
            fancyBoxContainer: '.fancyambox-container .amhideprice-form',
            hideProductIdName: '[name="hide_product_id"]',
            hideProductId: 'hide_product_id',
            productName: '.product-name',
            productIdAttr: 'data-product-id',
            successMessage: 'success am-hide-message',
            errorMessage: 'error am-hide-message',
        },

        initialize: function (options) {
        },

        addProduct: function (product) {
            this.products[product['id']] = product['name'];
            this.customer = product['customer'];
            this.url = product['url'];
            this._createButtonObserver(product['id']);
        },

        _createButtonObserver: function (id) {
            var self = this,
                popupSelector = self._getPopupSelectorById(id);

            $(popupSelector).click(function (e) {
                e.stopPropagation();
                self._showPopup(this);
            }).addClass(
                self.selectors.onClickPopup
            );

            $(document).on('click', self.selectors.amHideClosePopup, function () {
                $.fancyambox.close();
            });
        },

        _getPopupSelectorById: function (id) {
            return this.selectors.amHidePricePopup.replace(/%s/, id);
        },

        _showPopup: function (element) {
            var id = $(element).attr(this.selectors.productIdAttr),
                form = $(this.selectors.formClass);

            if (!form.length) {
                form = this._getFilledForm(id);
            } else {
                if (form.find(this.selectors.hideProductIdName).length === 0) {
                    form.append(
                        $('<input>', {
                            'name': this.selectors.hideProductId,
                            'type': 'hidden',
                            'value': id
                        })
                    );
                }
            }

            if (!form) {
                console.warn('We can`t find the form for displaying. Please check your module configuration.');
            }

            $.fancyambox.open(form, {
                touch: false
            });
        },

        _getFilledForm: function (id) {
            var form = $(this.selectors.formId),
                product = this.products[id];

            if (!form.length) {
                return false;
            }

            var productName = form.find(this.selectors.productName);
            if (productName && product) {
                productName.html(product);
            }

            var productId = form.find('[name="product_id"]');
            if (productId) {
                productId.val(id);
            }

            var name = form.find('[name="name"]');
            if (name && this.customer.name) {
                name.val(this.customer.name);
            }

            var phone = form.find('[name="phone"]');
            if (phone && this.customer.phone) {
                phone.val(this.customer.phone);
            }

            var email = form.find('[name="email"]');
            if (email && this.customer.email) {
                email.val(this.customer.email);
            }

            var self = this;
            form.on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                var validator = $(this).validation({ radioCheckboxClosest: '.nested'});
                if (validator.valid()) {
                    self.submitForm($(this));
                }

                return false;
            });

            return form;
        },

        submitForm: function (form) {
            form = $(form);
            var self = this;

            var data = form.serialize();
            this._clearForm(form);

            $.ajax({
                url: self.url,
                data: data,
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    $(self.selectors.fancyBoxContainer).remove();
                    $.fancyambox.getInstance().showLoading();
                },
                success: function (response) {
                    $.fancyambox.getInstance().hideLoading();
                    var result = $('<div>',
                        {class: 'message'}
                    );
                    var html = $('<div>');

                    if (response.success) {
                        html.html(response.success).appendTo(result);
                        result.addClass(self.selectors.successMessage);
                    }

                    if (response.error) {
                        html.html(response.error).appendTo(result);
                        result.addClass(self.selectors.errorMessage);
                    }

                    $.fancyambox.close();
                    $.fancyambox.open(result);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $.fancyambox.getInstance().hideLoading();
                    $.fancyambox.close();
                    $.fancyambox.open(errorThrown);
                }
            });
        },

        _clearForm: function (form) {
            var key = form.find('[name="form_key"]'),
                value = key.val();

            $(form)[0].reset();
            key.val(value);
        }
    });

    window.amHidePriceForm = amHidePriceForm();

    return window.amHidePriceForm;
});
