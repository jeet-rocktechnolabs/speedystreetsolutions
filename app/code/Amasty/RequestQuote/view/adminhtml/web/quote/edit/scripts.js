define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/template',
    'text!Amasty_RequestQuote/templates/quote/create/shipping/reload.html',
    "mage/translate",
    "prototype",
    "Magento_Catalog/catalog/product/composite/configure",
    'Magento_Ui/js/lib/view/utils/async'
], function(jQuery, confirm, alert, template, shippingTemplate){

    window.AmAdminQuote = new Class.create();

    AmAdminQuote.prototype = {
        priceModificators: [
            'discount',
            'surcharge'
        ],
        canShippingModified: false,
        customMethodAllowed: false,
        customFee: 0,

        initialize : function(data){
            if (!data) {
                data = {};
            }
            this.shippingTemplate = template(shippingTemplate, {
                data: {
                    title: jQuery.mage.__('Shipping Method'),
                    linkText: jQuery.mage.__('Get shipping methods and rates')
                }
            });
            this.loadBaseUrl    = false;
            this.customerId     = data.customer_id ? data.customer_id : false;
            this.storeId        = data.store_id ? data.store_id : false;
            this.currencyId     = false;
            this.currencySymbol = data.currency_symbol ? data.currency_symbol : '';
            this.gridProducts   = $H({});
            this.overlayData = $H({});
            this.productConfigureAddFields = {};
            this.productPriceBase = {};
            this.collectElementsValue = true;
            this.isOnlyVirtualProduct = false;
            this.summarizePrice = true;
            this.canShippingModified = typeof data['can_shipping_modified'] !== 'undefined'
                ? data['can_shipping_modified']
                : true;
            this.customMethodAllowed = typeof data['custom_method_allowed'] !== 'undefined'
                ? data['custom_method_allowed']
                : false;
            this.customFee = typeof data['custom_fee'] !== 'undefined' ? data['custom_fee'] : 0;
            this.configureShipping(data['shipping_configured']);
            this.disableCustomMethod(!this.customMethodAllowed);
            jQuery.async('#quote-items', (function(){
                this.dataArea = new OrderFormArea('data', $(this.getAreaId('data')), this);
                this.itemsArea = Object.extend(new OrderFormArea('items', $(this.getAreaId('items')), this), {
                    addControlButton: function(button){
                        var controlButtonArea = $(this.node).select('.actions')[0];
                        if (typeof controlButtonArea != 'undefined') {
                            var buttons = controlButtonArea.childElements();
                            for (var i = 0; i < buttons.length; i++) {
                                if (buttons[i].innerHTML.include(button.label)) {
                                    return ;
                                }
                            }
                            button.insertIn(controlButtonArea, 'top');
                        }
                    }
                });

                var searchButtonId = 'add_products',
                    searchButton = new ControlButton(jQuery.mage.__('Add Products'), searchButtonId),
                    searchAreaId = this.getAreaId('search');
                searchButton.onClick = function() {
                    $(searchAreaId).show();
                    var el = this;
                    window.setTimeout(function () {
                        el.remove();
                    }, 10);
                };


                    this.dataArea.onLoad = this.dataArea.onLoad.wrap(function(proceed) {
                        proceed();
                        this._parent.itemsArea.setNode($(this._parent.getAreaId('items')));
                        this._parent.itemsArea.onLoad();
                    });

                    this.itemsArea.onLoad = this.itemsArea.onLoad.wrap(function(proceed) {
                        proceed();
                        if ($(searchAreaId) && !$(searchAreaId).visible() && !$(searchButtonId)) {
                            this.addControlButton(searchButton);
                        }
                    });
                if (jQuery('#' + this.getAreaId('items')).length) {
                    this.itemsArea.onLoad();
                }
            }).bind(this));
            this.priceModificatorsInit();
        },

        areasLoaded: function(){
        },

        itemsLoaded: function(){
        },

        dataLoaded: function(){
            this.dataShow();
        },

        setLoadBaseUrl : function(url){
            this.loadBaseUrl = url;
        },

        setAddresses : function(addresses){
            this.addresses = addresses;
        },

        setCustomerId : function(id){
            this.customerId = id;
            this.loadArea(['header', 'store'], true);
            $(this.getAreaId('header')).callback = 'setCustomerAfter';
            $('back_order_top_button').hide();
            $('reset_order_top_button').show();
        },

        setCustomerAfter : function () {
            this.customerSelectorHide();
            if (this.storeId) {
                $(this.getAreaId('data')).callback = 'dataLoaded';
                this.loadArea(['data'], true);
            }
            else {
                this.storeSelectorShow();
            }
        },

        setStoreId : function(id){
            this.storeId = id;
            this.storeSelectorHide();
            //this.loadArea(['header', 'sidebar','data'], true);
            this.dataShow();
            this.loadArea(['header', 'data'], true);
        },

        setCurrencyId : function(id){
            this.currencyId = id;
            //this.loadArea(['sidebar', 'data'], true);
            this.loadArea(['data'], true);
        },

        setCurrencySymbol : function(symbol){
            this.currencySymbol = symbol;
        },

        selectAddress : function(el, container){
            const saveInAddressBook = $(container.replace('fields', '') + 'save_in_address_book');

            id = el.value;
            if (id.length == 0) {
                id = '0';
            }

            this.selectAddressEvent = true;
            if (this.addresses[id]) {
                this.fillAddressFields(container, this.addresses[id]);
            } else {
                this.fillAddressFields(container, {});
            }

            if (saveInAddressBook) {
                if (!+id) {
                    saveInAddressBook.checked = true;
                } else {
                    saveInAddressBook.checked = false;
                }
            }

            this.selectAddressEvent = false;

            var data = this.serializeData(container);
            data[el.name] = id;

            if (!(this.isShippingField(container) && !this.isShippingMethodReseted)) {
                this.saveData(data);
            }

        },

        /**
         * Checks if the field belongs to the shipping address.
         *
         * @param {String} fieldId
         * @return {Boolean}
         */
        isShippingField: function (fieldId) {
            if (this.shippingAsBilling) {
                return fieldId.include('billing');
            }

            return fieldId.include('shipping');
        },

        /**
         * Checks if the field belongs to the billing address.
         *
         * @param {String} fieldId
         * @return {Boolean}
         */
        isBillingField: function (fieldId) {
            return fieldId.include('billing');
        },

        /**
         * Binds events on container form fields.
         *
         * @param {String} container
         */
        bindAddressFields: function (container) {
            var fields = $(container).select('input', 'select', 'textarea'),
                i;

            for (i = 0; i < fields.length; i++) {
                jQuery(fields[i]).change(this.changeAddressField.bind(this));
            }
        },

        /**
         * Triggers on each form's element changes.
         *
         * @param {Event} event
         */
        changeAddressField: function (event) {
            var field = Event.element(event),
                re = /[^\[]*\[([^\]]*)_address\]\[([^\]]*)\](\[(\d)\])?/,
                matchRes = field.name.match(re),
                type,
                name,
                data,
                resetShipping = false;

            if (!matchRes) {
                return;
            }

            type = matchRes[1];
            name = matchRes[2];

            if (this.isBillingField(field.id)) {
                data = this.serializeData(this.billingAddressContainer);
            } else {
                data = this.serializeData(this.shippingAddressContainer);
            }
            data = data.toObject();

            if (type === 'billing' && this.shippingAsBilling) {
                this.syncAddressField(this.shippingAddressContainer, field.name, field);
                resetShipping = true;
            }

            if (type === 'shipping' && !this.shippingAsBilling) {
                resetShipping = true;
            }

            if (resetShipping) {
                data['reset_shipping'] = true;
            }

            data['order[' + type + '_address][customer_address_id]'] = null;
            data['shipping_as_billing'] = +this.shippingAsBilling;

            if (name === 'customer_address_id') {
                data['order[' + type + '_address][customer_address_id]'] =
                    $('quote-' + type + '_address_customer_address_id').value;
            }

            if (name === 'country_id' && this.selectAddressEvent === false) {
                $('quote-' + type + '_address_customer_address_id').value = '';
            }

            if (data['reset_shipping']) {
                this.resetShippingMethod();
            } else {
                this.saveData(data);

                if (name === 'country_id' || name === 'customer_address_id') {
                    this.loadArea(['shipping_method', 'billing_method', 'totals', 'items'], true, data);
                }
            }
        },

        /**
         * Set address container form field value.
         *
         * @param {String} container - container ID
         * @param {String} fieldName - form field name
         * @param {*} fieldValue - form field value
         */
        syncAddressField: function (container, fieldName, fieldValue) {
            var syncName;

            if (this.isBillingField(fieldName)) {
                syncName = fieldName.replace('billing', 'shipping');
            }

            $(container).select('[name="' + syncName + '"]').each(function (element) {
                if (~['input', 'textarea', 'select'].indexOf(element.tagName.toLowerCase())) {
                    if (element.type === "checkbox") {
                        element.checked = fieldValue.checked;
                    } else {
                        element.value = fieldValue.value;
                    }
                }
            });
        },

        fillAddressFields: function(container, data){
            var regionIdElem = false;
            var regionIdElemValue = false;

            var fields = $(container).select('input', 'select', 'textarea');
            var re = /[^\[]*\[[^\]]*\]\[([^\]]*)\](\[(\d)\])?/;
            for(var i=0;i<fields.length;i++){
                // skip input type file @Security error code: 1000
                if (fields[i].tagName.toLowerCase() == 'input' && fields[i].type.toLowerCase() == 'file') {
                    continue;
                }
                var matchRes = fields[i].name.match(re);
                if (matchRes === null) {
                    continue;
                }
                var name = matchRes[1];
                var index = matchRes[3];

                if (index){
                    // multiply line
                    if (data[name]){
                        var values = data[name].split("\n");
                        fields[i].value = values[index] ? values[index] : '';
                    } else {
                        fields[i].value = '';
                    }
                } else if (fields[i].tagName.toLowerCase() == 'select' && fields[i].multiple) {
                    // multiselect
                    if (data[name]) {
                        values = [''];
                        if (Object.isString(data[name])) {
                            values = data[name].split(',');
                        } else if (Object.isArray(data[name])) {
                            values = data[name];
                        }
                        fields[i].setValue(values);
                    }
                } else {
                    fields[i].setValue(data[name] ? data[name] : '');
                }

                if (fields[i].changeUpdater) {
                    fields[i].changeUpdater();
                }

                if (name == 'region' && data['region_id'] && !data['region']){
                    fields[i].value = data['region_id'];
                }

                jQuery(fields[i]).trigger('change');
            }
        },

        disableShippingAddress : function(flag) {
            this.shippingAsBilling = flag;
            if ($('quote-shipping_address_customer_address_id')) {
                $('quote-shipping_address_customer_address_id').disabled = flag;
            }
            if ($(this.shippingAddressContainer)) {
                var dataFields = $(this.shippingAddressContainer).select('input', 'select', 'textarea');
                for (var i = 0; i < dataFields.length; i++) {
                    dataFields[i].disabled = flag;

                    if(this.isOnlyVirtualProduct) {
                        dataFields[i].setValue('');
                    }
                }
                var buttons = $(this.shippingAddressContainer).select('button');
                // Add corresponding class to buttons while disabling them
                for (i = 0; i < buttons.length; i++) {
                    buttons[i].disabled = flag;
                    if (flag) {
                        buttons[i].addClassName('disabled');
                    } else {
                        buttons[i].removeClassName('disabled');
                    }
                }
            }
        },

        /**
         * Equals shipping and billing addresses.
         *
         * @param {Boolean} flag
         */
        setShippingAsBilling: function (flag) {
            var data,
                areasToLoad = ['billing_method', 'shipping_address', 'shipping_method', 'totals', 'giftmessage'];

            this.disableShippingAddress(flag);
            data = this.serializeData(flag ? this.billingAddressContainer : this.shippingAddressContainer);
            data = data.toObject();
            data['shipping_as_billing'] = flag ? 1 : 0;
            data['reset_shipping'] = 1;
            this.loadArea(areasToLoad, true, data);
        },

        /**
         * Loads shipping options according to address data.
         *
         * @return {Boolean}
         */
        loadShippingRates: function () {
            var addressContainer = this.shippingAsBilling ?
                'billingAddressContainer' :
                'shippingAddressContainer',
                data = this.serializeData(this[addressContainer]).toObject();

            data['collect_shipping_rates'] = 1;

            this.isShippingMethodReseted = false;
            this.loadArea(['shipping_method', 'totals'], true, data);

            return false;
        },

        setShippingMethod: function(method) {
            var data = {};

            data['quote[shipping_method]'] = method;
            this.loadArea([
                'shipping_method',
                'totals',
                // 'billing_method'
            ], true, data);
        },

        /**
         * Replace shipping method area.
         */
        resetShippingMethod: function () {
            if (!this.isOnlyVirtualProduct) {
                $(this.getAreaId('shipping_method')).update(this.shippingTemplate);
            }
        },

        changeShippingModified: function (element) {
            this.canShippingModified = element.checked;
        },

        changeCustomMethodAllowed: function (element) {
            this.customMethodAllowed = element.checked;
            this.disableCustomMethod(!this.customMethodAllowed);
        },

        disableCustomMethod: function (disabled) {
            var customMethodFields = jQuery('[data-amquote-js="custom-method"]');
            if (disabled) {
                customMethodFields.attr('disabled', true);
                customMethodFields.removeAttr('checked')
            } else {
                customMethodFields.removeAttr('disabled');
            }
        },

        configureShipping: function (visible) {
            if ($('quote-addresses')) {
                var addresses = $('quote-addresses'),
                    addressesInputs = jQuery('[data-amquote-js="quote_addresses"] input, [data-amquote-js="quote_addresses"] select'),
                    methods = $('quote-methods'),
                    methodsInputs = jQuery('[data-amquote-js="shipping_methods"] input, [data-amquote-js="shipping_methods"] select');

                if (visible) {
                    addresses.show();
                    methods.show();
                    addressesInputs.removeAttr('disabled');
                    methodsInputs.removeAttr('disabled');
                } else {
                    addresses.hide();
                    methods.hide();
                    addressesInputs.attr('disabled', true);
                    methodsInputs.attr('disabled', true);
                }

                this.disableShippingAddress($('quote-shipping_same_as_billing').checked);
                this.disableCustomMethod(!this.customMethodAllowed);
                this.disableCustomMethod(!this.customMethodAllowed);
            }
        },

        changeCustomFee: function (customFee) {
            this.customFee = customFee;
        },

        getCollectShippingRates: function () {
            return $('shipping_configured').checked;
        },

        addProduct : function(id){
            this.loadArea(['items', 'totals', 'shipping_method'], true, {add_product:id});
        },

        removeQuoteItem : function(id){
            this.loadArea(['items', 'totals', 'shipping_method'], true,
                {remove_item:id, from:'quote'});
        },

        moveQuoteItem : function(id, to){
            this.loadArea(['items', 'totals', 'shipping_method'], this.getAreaId('items'),
                {move_item:id, to:to});
        },

        productGridShow : function(buttonElement){
            this.productGridShowButton = buttonElement;
            Element.hide(buttonElement);
            this.showArea('search');
        },

        productGridRowInit : function(grid, row){
            var checkbox = $(row).select('.checkbox')[0];
            var inputs = $(row).select('.input-text');
            if (checkbox && inputs.length > 0) {
                checkbox.inputElements = inputs;
                for (var i = 0; i < inputs.length; i++) {
                    var input = inputs[i];
                    input.checkboxElement = checkbox;

                    var product = this.gridProducts.get(checkbox.value);
                    if (product) {
                        var defaultValue = product[input.name];
                        if (defaultValue) {
                            if (input.name == 'giftmessage') {
                                input.checked = true;
                            } else {
                                input.value = defaultValue;
                            }
                        }
                    }

                    input.disabled = !checkbox.checked || input.hasClassName('input-inactive');

                    Event.observe(input,'keyup', this.productGridRowInputChange.bind(this));
                    Event.observe(input,'change',this.productGridRowInputChange.bind(this));
                }
            }
        },

        productGridRowInputChange : function(event){
            var element = Event.element(event);
            if (element && element.checkboxElement && element.checkboxElement.checked){
                if (element.name!='giftmessage' || element.checked) {
                    this.gridProducts.get(element.checkboxElement.value)[element.name] = element.value;
                } else if (element.name=='giftmessage' && this.gridProducts.get(element.checkboxElement.value)[element.name]) {
                    delete(this.gridProducts.get(element.checkboxElement.value)[element.name]);
                }
            }
        },

        productGridRowClick : function(grid, event){
            var trElement = Event.findElement(event, 'tr');
            var qtyElement = trElement.select('input[name="qty"]')[0];
            var eventElement = Event.element(event);
            var isInputCheckbox = eventElement.tagName == 'INPUT' && eventElement.type == 'checkbox';
            var isInputQty = eventElement.tagName == 'INPUT' && eventElement.name == 'qty';
            if (trElement && !isInputQty) {
                var checkbox = Element.select(trElement, 'input[type="checkbox"]')[0];
                var qtyInput = Element.select(trElement, 'input[name="qty"]')[0];
                var confLink = Element.select(trElement, 'a')[0];
                var priceColl = Element.select(trElement, '.price')[0];
                if (checkbox) {
                    // processing non composite product
                    if (typeof confLink == 'undefined' || confLink.readAttribute('disabled')) {
                        var checked = isInputCheckbox ? checkbox.checked : !checkbox.checked;
                        grid.setCheckboxChecked(checkbox, checked);
                        // processing composite product
                    } else if (isInputCheckbox && !checkbox.checked) {
                        grid.setCheckboxChecked(checkbox, false);
                        // processing composite product
                    } else if (!isInputCheckbox || (isInputCheckbox && checkbox.checked)) {
                        var listType = confLink.readAttribute('list_type');
                        var productId = confLink.readAttribute('product_id');
                        if (typeof this.productPriceBase[productId] == 'undefined') {
                            var priceBase = priceColl.innerHTML.match(/.*?([\d,]+\.?\d*)/);
                            if (!priceBase) {
                                this.productPriceBase[productId] = 0;
                            } else {
                                this.productPriceBase[productId] = parseFloat(priceBase[1].replace(/,/g,''));
                            }
                        }
                        productConfigure.setConfirmCallback(listType, function() {
                            // sync qty of popup and qty of grid
                            var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                            if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                                qtyElement.value = confirmedCurrentQty.value;
                            }
                            // calc and set product price
                            var productPrice = this._calcProductPrice();
                            if (this._isSummarizePrice()) {
                                productPrice += this.productPriceBase[productId];
                            }
                            productPrice = parseFloat(Math.round(productPrice + "e+2") + "e-2");
                            priceColl.innerHTML = this.currencySymbol + productPrice.toFixed(2);
                            // and set checkbox checked
                            grid.setCheckboxChecked(checkbox, true);
                        }.bind(this));
                        productConfigure.setCancelCallback(listType, function() {
                            if (!$(productConfigure.confirmedCurrentId) || !$(productConfigure.confirmedCurrentId).innerHTML) {
                                grid.setCheckboxChecked(checkbox, false);
                            }
                        });
                        productConfigure.setShowWindowCallback(listType, function() {
                            // sync qty of grid and qty of popup
                            var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                            if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                                formCurrentQty.value = qtyElement.value;
                            }
                        }.bind(this));
                        productConfigure.showItemConfiguration(listType, productId);
                    }
                }
            }
        },

        /**
         * Is need to summarize price
         */
        _isSummarizePrice: function(elm) {
            if (elm && elm.hasAttribute('summarizePrice')) {
                this.summarizePrice = parseInt(elm.readAttribute('summarizePrice'));
            }
            return this.summarizePrice;
        },
        /**
         * Calc product price through its options
         */
        _calcProductPrice: function () {
            var productPrice = 0;
            var getPriceFields = function (elms) {
                var productPrice = 0;
                var getPrice = function (elm) {
                    var optQty = 1;
                    if (elm.hasAttribute('qtyId')) {
                        if (!$(elm.getAttribute('qtyId')).value) {
                            return 0;
                        } else {
                            optQty = parseFloat($(elm.getAttribute('qtyId')).value);
                        }
                    }
                    if (elm.hasAttribute('price') && !elm.disabled) {
                        return parseFloat(elm.readAttribute('price')) * optQty;
                    }
                    return 0;
                };
                for(var i = 0; i < elms.length; i++) {
                    if (elms[i].type == 'select-one' || elms[i].type == 'select-multiple') {
                        for(var ii = 0; ii < elms[i].options.length; ii++) {
                            if (elms[i].options[ii].selected) {
                                if (this._isSummarizePrice(elms[i].options[ii])) {
                                    productPrice += getPrice(elms[i].options[ii]);
                                } else {
                                    productPrice = getPrice(elms[i].options[ii]);
                                }
                            }
                        }
                    }
                    else if (((elms[i].type == 'checkbox' || elms[i].type == 'radio') && elms[i].checked)
                        || ((elms[i].type == 'file' || elms[i].type == 'text' || elms[i].type == 'textarea' || elms[i].type == 'hidden')
                        && Form.Element.getValue(elms[i]))
                    ) {
                        if (this._isSummarizePrice(elms[i])) {
                            productPrice += getPrice(elms[i]);
                        } else {
                            productPrice = getPrice(elms[i]);
                        }
                    }
                }
                return productPrice;
            }.bind(this);
            productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('input'));
            productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('select'));
            productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('textarea'));
            return productPrice;
        },

        productGridCheckboxCheck : function(grid, element, checked){
            if (checked) {
                if(element.inputElements) {
                    this.gridProducts.set(element.value, {});
                    var product = this.gridProducts.get(element.value);
                    for (var i = 0; i < element.inputElements.length; i++) {
                        var input = element.inputElements[i];
                        if (!input.hasClassName('input-inactive')) {
                            input.disabled = false;
                            if (input.name == 'qty' && !input.value) {
                                input.value = 1;
                            }
                        }

                        if (input.checked || input.name != 'giftmessage') {
                            product[input.name] = input.value;
                        } else if (product[input.name]) {
                            delete(product[input.name]);
                        }
                    }
                }
            } else {
                if(element.inputElements){
                    for(var i = 0; i < element.inputElements.length; i++) {
                        element.inputElements[i].disabled = true;
                    }
                }
                this.gridProducts.unset(element.value);
            }
            grid.reloadParams = {'products[]':this.gridProducts.keys()};
        },

        /**
         * Submit configured products to quote
         */
        productGridAddSelected : function(){
            if(this.productGridShowButton) Element.show(this.productGridShowButton);
            var area = ['search', 'items', 'shipping_method', 'totals', 'giftmessage','billing_method'];
            // prepare additional fields and filtered items of products
            var fieldsPrepare = {};
            if ($('quote_id')) {
                fieldsPrepare.quote_id = $('quote_id').value;
            }
            this.priceModificators.each(function (value) {
                var modificatorInput = jQuery('[data-price-modificator="' + value + '"]');
                if (!modificatorInput.attr('disabled')) {
                    fieldsPrepare[modificatorInput.attr('name')] = parseFloat(modificatorInput.val());
                }
            });
            var itemsFilter = [];
            var products = this.gridProducts.toObject();
            for (var productId in products) {
                itemsFilter.push(productId);
                var paramKey = 'item['+productId+']';
                for (var productParamKey in products[productId]) {
                    paramKey += '['+productParamKey+']';
                    fieldsPrepare[paramKey] = products[productId][productParamKey];
                }
            }
            this.productConfigureSubmit('product_to_add', area, fieldsPrepare, itemsFilter);
            productConfigure.clean('quote_items');
            this.hideArea('search');
            this.gridProducts = $H({});
        },

        selectCustomer : function(grid, event){
            var element = Event.findElement(event, 'tr');
            if (element.title){
                this.setCustomerId(element.title);
            }
        },

        customerSelectorHide : function(){
            this.hideArea('customer-selector');
        },

        customerSelectorShow : function(){
            this.showArea('customer-selector');
        },

        storeSelectorHide : function(){
            this.hideArea('store');
        },

        storeSelectorShow : function(){
            this.showArea('store');
        },

        dataHide : function(){
            this.hideArea('data');
        },

        dataShow : function(){
            if ($('submit_order_top_button')) {
                $('submit_order_top_button').show();
            }
            this.showArea('data');
        },

        itemsUpdate : function (additionalData) {
            var editForm = jQuery('#edit_form');
            if (editForm.validate().element(jQuery('#discount'))
                && editForm.validate().element(jQuery('#surcharge'))
            ) {
                var area = ['items', 'totals', 'shipping_method'];
                // prepare additional fields
                var fieldsPrepare = {update_items: 1};
                if ($('quote_id')) {
                    fieldsPrepare.quote_id = $('quote_id').value;
                }
                this.priceModificators.each(function (value) {
                    var modificatorInput = jQuery('[data-price-modificator="' + value + '"]');
                    if (!modificatorInput.attr('disabled')) {
                        fieldsPrepare[modificatorInput.attr('name')] = parseFloat(modificatorInput.val());
                        if (fieldsPrepare[modificatorInput.attr('name')]) {
                            jQuery('.custom-price').attr('disabled', false);
                        }
                    }
                });
                var info = $('order-items_grid').select('input', 'select', 'textarea');
                for (var i = 0; i < info.length; i++) {
                    if (!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                        fieldsPrepare[info[i].name] = info[i].getValue();
                    }
                }
                fieldsPrepare = Object.extend(fieldsPrepare, this.productConfigureAddFields);
                fieldsPrepare = Object.extend(fieldsPrepare, additionalData);
                fieldsPrepare['quote[shipping_can_modified]'] = this.canShippingModified ? 1 : 0;
                fieldsPrepare['quote[shipping_configured]'] = $('shipping_configured').checked ? 1 : 0;
                fieldsPrepare['quote[custom_fee]'] = this.customFee;
                fieldsPrepare['quote[custom_method_enabled]'] = this.customMethodAllowed ? 1 : 0;
                if (this.getCollectShippingRates()) {
                    fieldsPrepare['collect_shipping_rates'] = 1;
                }

                this.productConfigureSubmit('quote_items', area, fieldsPrepare);
                this.orderItemChanged = false;
            }
        },

        itemsOnchangeBind : function(){
            var elems = $('order-items_grid').select('input', 'select', 'textarea');
            for(var i=0; i<elems.length; i++){
                if(!elems[i].bindOnchange){
                    elems[i].bindOnchange = true;
                    elems[i].observe('change', this.itemChange.bind(this))
                }
            }
        },

        itemChange : function(event){
            this.orderItemChanged = true;
        },

        /**
         * Submit batch of configured products
         *
         * @param listType
         * @param area
         * @param fieldsPrepare
         * @param itemsFilter
         */
        productConfigureSubmit : function(listType, area, fieldsPrepare, itemsFilter) {
            // prepare loading areas and build url
            area = this.prepareArea(area);
            this.loadingAreas = area;
            var url = this.loadBaseUrl + 'block/' + area + '?isAjax=true';

            // prepare additional fields
            fieldsPrepare = this.prepareParams(fieldsPrepare);
            fieldsPrepare.reset_shipping = 1;
            fieldsPrepare.json = 1;

            // create fields
            var fields = [];
            for (var name in fieldsPrepare) {
                fields.push(new Element('input', {type: 'hidden', name: name, value: fieldsPrepare[name]}));
            }
            productConfigure.addFields(fields);

            // filter items
            if (itemsFilter) {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }

            // prepare and do submit
            productConfigure.addListType(listType, {urlSubmit: url});
            productConfigure.setOnLoadIFrameCallback(listType, function(response){
                this.loadAreaResponseHandler(response);
            }.bind(this));
            productConfigure.submit(listType);
            // clean
            this.productConfigureAddFields = {};
        },

        /**
         * Show configuration of quote item
         *
         * @param itemId
         */
        showQuoteItemConfiguration: function(itemId){
            var listType = 'quote_items';
            var qtyElement = $('order-items_grid').select('input[name="item\['+itemId+'\]\[qty\]"]')[0];
            productConfigure.setConfirmCallback(listType, function() {
                // sync qty of popup and qty of grid
                var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                    qtyElement.value = confirmedCurrentQty.value;
                }
                this.productConfigureAddFields['item['+itemId+'][configured]'] = 1;
                this.itemsUpdate();
            }.bind(this));
            productConfigure.setShowWindowCallback(listType, function() {
                // sync qty of grid and qty of popup
                var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                    formCurrentQty.value = qtyElement.value;
                }
            }.bind(this));
            productConfigure.showItemConfiguration(listType, itemId);
        },

        accountFieldsBind : function(container){
            if($(container)){
                var fields = $(container).select('input', 'select', 'textarea');
                for(var i=0; i<fields.length; i++){
                    if(fields[i].id == 'group_id'){
                        fields[i].observe('change', this.accountGroupChange.bind(this))
                    }
                    else{
                        fields[i].observe('change', this.accountFieldChange.bind(this))
                    }
                }
            }
        },

        accountGroupChange : function(){
            this.loadArea(['data'], true, this.serializeData('order-form_account').toObject());
        },

        accountFieldChange : function(){
            this.saveData(this.serializeData('order-form_account'));
        },

        commentFieldsBind : function(container){
            if($(container)){
                var fields = $(container).select('input', 'textarea');
                for(var i=0; i<fields.length; i++)
                    fields[i].observe('change', this.commentFieldChange.bind(this))
            }
        },

        commentFieldChange : function(){
            this.saveData(this.serializeData('order-comment'));
        },

        loadArea : function(area, indicator, params){
            var deferred = new jQuery.Deferred();
            var url = this.loadBaseUrl;
            if (area) {
                area = this.prepareArea(area);
                url += 'block/' + area;
            }
            if (indicator === true) indicator = 'html-body';
            params = this.prepareParams(params);
            this.priceModificators.each(function (value) {
                var modificatorInput = jQuery('[data-price-modificator="' + value + '"]');
                if (!modificatorInput.attr('disabled')) {
                    params[modificatorInput.attr('name')] = parseFloat(modificatorInput.val());
                }
            });
            params.json = true;
            if ($('quote_id')) {
                params.quote_id = $('quote_id').value;
            }
            if (!this.loadingAreas) this.loadingAreas = [];
            if (indicator) {
                this.loadingAreas = area;
                new Ajax.Request(url, {
                    parameters:params,
                    loaderArea: indicator,
                    onSuccess: function(transport) {
                        var response = transport.responseText.evalJSON();
                        this.loadAreaResponseHandler(response);
                        deferred.resolve();
                    }.bind(this)
                });
            }
            else {
                new Ajax.Request(url, {
                    parameters:params,
                    loaderArea: indicator,
                    onSuccess: function(transport) {
                        deferred.resolve();
                    }
                });
            }
            if (typeof productConfigure != 'undefined' && area instanceof Array && area.indexOf('items') != -1) {
                productConfigure.clean('quote_items');
            }
            return deferred.promise();
        },

        loadAreaResponseHandler : function (response) {
            if (response.error) {
                alert({
                    content: response.message
                });
            }
            if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
            if (!this.loadingAreas) {
                this.loadingAreas = [];
            }
            if (typeof this.loadingAreas == 'string') {
                this.loadingAreas = [this.loadingAreas];
            }
            if (this.loadingAreas.indexOf('message') == -1) {
                this.loadingAreas.push('message');
            }
            if (response.header) {
                jQuery('.page-actions-inner').attr('data-title', response.header);
            }

            for (var i = 0; i < this.loadingAreas.length; i++) {
                var id = this.loadingAreas[i];
                if ($(this.getAreaId(id))) {
                    if ('message' != id || response[id]) {
                        var area = $(this.getAreaId(id)).update(response[id]);
                        jQuery(area).trigger('contentUpdated');
                    }
                    if ($(this.getAreaId(id)).callback && (typeof this[$(this.getAreaId(id)).callback] == 'function')) {
                        this[$(this.getAreaId(id)).callback]();
                    }
                }
            }

            if (this.loadingAreas.indexOf('items') != -1) {
                this.priceModificatorsInit();
            }

            this.configureShipping($('shipping_configured').checked);
        },

        prepareArea : function(area) {
            if (this.giftMessageDataChanged) {
                return area.without('giftmessage');
            }
            return area;
        },

        saveData : function(data) {
            this.loadArea(false, false, data);
        },

        showArea : function(area) {
            var id = this.getAreaId(area);
            if($(id)) {
                $(id).show();
                this.areaOverlay();
            }
        },

        hideArea : function(area) {
            var id = this.getAreaId(area);
            if($(id)) {
                $(id).hide();
                this.areaOverlay();
            }
        },

        areaOverlay : function() {
            $H(quote.overlayData).each(function(e) {
                e.value.fx();
            });
        },

        getAreaId : function(area){
            return 'quote-'+area;
        },

        prepareParams : function(params) {
            if (!params) {
                params = {};
            }
            if (!params.customer_id) {
                params.customer_id = this.customerId;
            }
            if (!params.store_id) {
                params.store_id = this.storeId;
            }
            if (!params.currency_id) {
                params.currency_id = this.currencyId;
            }
            if (!params.form_key) {
                params.form_key = FORM_KEY;
            }
            if (!params.shipping_can_modified) {
                params['quote[shipping_can_modified]'] = this.canShippingModified ? 1 : 0;
            }
            if (!params.shipping_configured) {
                params['quote[shipping_configured]'] = $('shipping_configured').checked ? 1 : 0;
            }
            params['quote[custom_fee]'] = this.customFee;
            params['quote[custom_method_enabled]'] = this.customMethodAllowed ? 1 : 0;
            if (this.getCollectShippingRates()) {
                params['collect_shipping_rates'] = 1;
            }

            return params;
        },


        serializeData : function(container) {
            var fields = $(container).select('input', 'select', 'textarea');
            var data = Form.serializeElements(fields, true);

            return $H(data);
        },

        toggleCustomPrice: function(checkbox, elemId) {
            if (checkbox.checked) {
                $(elemId).disabled = false;
                $(elemId).show();
            }
            else {
                $(elemId).disabled = true;
                $(elemId).hide();
            }
        },

        submit: function() {
            jQuery('#edit_form').submit();
        },

        _realSubmit: function () {
            var disableAndSave = function() {
                disableElements('save');
                jQuery('#edit_form').on('invalid-form.validate', function() {
                    enableElements('save');
                    jQuery('#edit_form').trigger('processStop');
                    jQuery('#edit_form').off('invalid-form.validate');
                });
                jQuery('#edit_form').triggerHandler('save');
            }
            if (this.orderItemChanged) {
                var self = this;

                jQuery('#edit_form').trigger('processStop');

                confirm({
                    content: jQuery.mage.__('You have item changes'),
                    actions: {
                        confirm: function() {
                            jQuery('#edit_form').trigger('processStart');
                            disableAndSave();
                        },
                        cancel: function() {
                            self.itemsUpdate();
                        }
                    }
                });
            } else {
                disableAndSave();
            }
        },

        overlay : function(elId, show, observe) {
            if (typeof(show) == 'undefined') { show = true; }

            var orderObj = this;
            var obj = this.overlayData.get(elId);
            if (!obj) {
                obj = {
                    show: show,
                    el: elId,
                    order: orderObj,
                    fx: function(event) {
                        this.order.processOverlay(this.el, this.show);
                    }
                };
                obj.bfx = obj.fx.bindAsEventListener(obj);
                this.overlayData.set(elId, obj);
            } else {
                obj.show = show;
                Event.stopObserving(window, 'resize', obj.bfx);
            }

            Event.observe(window, 'resize', obj.bfx);

            this.processOverlay(elId, show);
        },

        processOverlay : function(elId, show) {
            var el = $(elId);

            if (!el) {
                return;
            }

            var parentEl = el.up(1);
            if (show) {
                parentEl.removeClassName('ignore-validate');
            } else {
                parentEl.addClassName('ignore-validate');
            }

            if (Prototype.Browser.IE) {
                parentEl.select('select').each(function (elem) {
                    if (show) {
                        elem.needShowOnSuccess = false;
                        elem.style.visibility = '';
                    } else {
                        elem.style.visibility = 'hidden';
                        elem.needShowOnSuccess = true;
                    }
                });
            }

            parentEl.setStyle({position: 'relative'});
            el.setStyle({
                display: show ? 'none' : ''
            });
        },

        validateVat: function(parameters)
        {
            var params = {
                country: $(parameters.countryElementId).value,
                vat: $(parameters.vatElementId).value
            };

            if (this.storeId !== false) {
                params.store_id = this.storeId;
            }

            var currentCustomerGroupId = $(parameters.groupIdHtmlId)
                ? $(parameters.groupIdHtmlId).value : '';

            new Ajax.Request(parameters.validateUrl, {
                parameters: params,
                onSuccess: function(response) {
                    var message = '';
                    var groupActionRequired = null;
                    try {
                        response = response.responseText.evalJSON();

                        if (null === response.group) {
                            if (true === response.valid) {
                                message = parameters.vatValidMessage;
                            } else if (true === response.success) {
                                message = parameters.vatInvalidMessage.replace(/%s/, params.vat);
                            } else {
                                message = parameters.vatValidationFailedMessage;
                            }
                        } else {
                            if (true === response.valid) {
                                message = parameters.vatValidAndGroupValidMessage;
                                if (0 === response.group) {
                                    message = parameters.vatValidAndGroupInvalidMessage;
                                    groupActionRequired = 'inform';
                                } else if (currentCustomerGroupId != response.group) {
                                    message = parameters.vatValidAndGroupChangeMessage;
                                    groupActionRequired = 'change';
                                }
                            } else if (response.success) {
                                message = parameters.vatInvalidMessage.replace(/%s/, params.vat);
                                groupActionRequired = 'inform';
                            } else {
                                message = parameters.vatValidationFailedMessage;
                                groupActionRequired = 'inform';
                            }
                        }
                    } catch (e) {
                        message = parameters.vatValidationFailedMessage;
                    }
                    if (null === groupActionRequired) {
                        alert({
                            content: message
                        });
                    }
                    else {
                        this.processCustomerGroupChange(
                            parameters.groupIdHtmlId,
                            message,
                            parameters.vatCustomerGroupMessage,
                            parameters.vatGroupErrorMessage,
                            response.group,
                            groupActionRequired
                        );
                    }
                }.bind(this)
            });
        },

        processCustomerGroupChange: function(groupIdHtmlId, message, customerGroupMessage, errorMessage, groupId, action)
        {
            var groupMessage = '';
            try {
                var currentCustomerGroupId = $(groupIdHtmlId).value;
                var currentCustomerGroupTitle =
                    $$('#' + groupIdHtmlId + ' > option[value=' + currentCustomerGroupId + ']')[0].text;
                var customerGroupOption = $$('#' + groupIdHtmlId + ' > option[value=' + groupId + ']')[0];
                groupMessage = customerGroupMessage.replace(/%s/, customerGroupOption.text);
            } catch (e) {
                groupMessage = errorMessage;
                if (action === 'change') {
                    message = '';
                    action = 'inform';
                }
            }

            if (action === 'change') {
                var confirmText = message.replace(/%s/, customerGroupOption.text);
                confirmText = confirmText.replace(/%s/, currentCustomerGroupTitle);
                if (confirm(confirmText)) {
                    $$('#' + groupIdHtmlId + ' option').each(function (o) {
                        o.selected = o.readAttribute('value') == groupId;
                    });
                    this.accountGroupChange();
                }
            } else if (action === 'inform') {
                alert({
                    content: message + '\n' + groupMessage
                });
            }
        },

        removeModificators: function () {
            this.priceModificators.each(function (modificator) {
                var modificator = jQuery('[data-price-modificator="' + modificator + '"]');
                modificator.val(0);
                modificator.attr('disabled', false);
            });
            this.itemsUpdate({'reset_price_modificators': 1});
        },

        priceModificatorsInit: function () {
            var self = this;
            jQuery('[data-price-modificator]').on('keyup change', function (event) {
                var changedInput = event.currentTarget;
                self.priceModificators.each(function (modificator) {
                    var modificatorSelector = '[data-price-modificator="' + modificator + '"]';
                    if (!jQuery(changedInput).is(modificatorSelector)) {
                        jQuery(modificatorSelector).attr('disabled', !!parseFloat(changedInput.value));
                    }
                });
            });
        }
    };

    window.OrderFormArea = Class.create();
    OrderFormArea.prototype = {
        _name: null,
        _node: null,
        _parent: null,
        _callbackName: null,

        initialize: function(name, node, parent){
            if(!node)
                return;
            this._name = name;
            this._parent = parent;
            this._callbackName = node.callback;
            if (typeof this._callbackName == 'undefined') {
                this._callbackName = name + 'Loaded';
                node.callback = this._callbackName;
            }
            if (typeof parent[this._callbackName] != 'undefined') {
                parent[this._callbackName] = parent[this._callbackName].wrap((function (proceed){
                    proceed();
                    this.onLoad();
                }).bind(this));
            }

            this.setNode(node);
        },

        setNode: function(node){
            if (!node.callback) {
                node.callback = this._callbackName;
            }
            this.node = node;
        },

        onLoad: function(){
        }
    };

    window.ControlButton = Class.create();

    ControlButton.prototype = {
        _label: '',
        _node: null,

        initialize: function(label, id){
            this._label = label;
            this._node = new Element('button', {
                'class': 'action-secondary action-add',
                'type':  'button'
            });
            if (typeof id !== 'undefined') {
                this._node.setAttribute('id', id)
            }
        },

        onClick: function(){
        },

        insertIn: function(element, position){
            var node = Object.extend(this._node),
                content = {};
            node.observe('click', this.onClick);
            node.update('<span>' + this._label + '</span>');
            content[position] = node;
            Element.insert(element, content);
        }
    };



    /**
     * @param {String} url
     * @returns {Object}
     */
    function getForm(url) {
        return jQuery('<form>', {
            'action': url,
            'method': 'POST'
        }).append(jQuery('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        }));
    }

    function validateQuoteItems() {
        if (jQuery('[data-amquote-js="quote-item"]').length === 0) {
            if (jQuery('.quote-items-validation').length === 0) {
                jQuery('.page-main-actions').after(jQuery('<div class="messages"><div class="quote-items-validation message message-error error">'
                    + jQuery.mage.__('Please specify quote items.')
                    + '</div></div>'
                ));
            }
            window.scrollTo(0, 0);

            return false;
        }

        return true;
    }

    jQuery('body').on('click', '.quote-action-button', function () {
        var button = jQuery(this),
            url = button.data('url');

        if (button.data('amquote-js') === 'approve' && !validateQuoteItems()) {
            return false;
        }

        getForm(url).appendTo('body').submit();

        return false;
    });

    jQuery('.add-note-quote-item').click(function() {
        jQuery(this).parent().find('.textarea-control').show();
        jQuery(this).hide();
    });

});

