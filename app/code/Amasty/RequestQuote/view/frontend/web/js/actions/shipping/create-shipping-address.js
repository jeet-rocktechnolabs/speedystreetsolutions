define([
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter'
], function (addressList, addressConverter) {
    'use strict';

    function getAddress (addressData) {
        var address = Object.assign(
            addressConverter.formAddressDataToQuoteAddress(addressData),
            {
                getType: function () {
                    return 'amasty_quote_address';
                },
                isEditable: function () {
                    return false;
                }
            }
        );
        delete address['extensionAttributes'];

        return address;
    }

    return function (addressData) {
        var address = getAddress(addressData),
            isAddressUpdated = addressList().some(function (currentAddress, index, addresses) {
                if (currentAddress.getKey() == address.getKey()) { //eslint-disable-line eqeqeq
                    addresses[index] = address;

                    return true;
                }

                return false;
            });

        if (!isAddressUpdated) {
            addressList.push(address);
        } else {
            addressList.valueHasMutated();
        }

        return address;
    };
});
