define(
	[
		'uiComponent',
		'Magento_Checkout/js/model/payment/renderer-list'
	],
	function (
		Component,
		rendererList
	) {
		'use strict';
		rendererList.push({
			type: 'Utp_PaymentGateway',
			component: 'Utp_PaymentGateway/js/view/payment/method-renderer/payment-method'
		});
		/** Add view logic here if needed */
		return Component.extend({});
	}
);
