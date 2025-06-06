var config = {

	map: {
		'*': {
			'alothemes': 'Magiccart_Alothemes/js/alothemes',
		},
	},

	config: {
        mixins: {
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Magiccart_Alothemes/js/view/summary/abstract-total-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Magiccart_Alothemes/js/view/shipping-mixin': true
            }
        }
    },

	paths: {
		'magiccart/easing'			: 'Magiccart_Alothemes/js/plugins/jquery.easing.min',
		'magiccart/parallax'		: 'Magiccart_Alothemes/js/plugins/jquery.parallax',
		'magiccart/socialstream'	: 'Magiccart_Alothemes/js/plugins/jquery.socialstream',
		'magiccart/zoom'			: 'Magiccart_Alothemes/js/plugins/jquery.zoom.min',
		'magiccart/bootstrap'		: 'Magiccart_Alothemes/js/plugins/bootstrap.min',
		'magiccart/slick'			: 'Magiccart_Alothemes/js/plugins/slick.min',
		'magiccart/lazyload'		: 'Magiccart_Alothemes/js/plugins/lazyload.min',
		'magiccart/sticky'		    : 'Magiccart_Alothemes/js/plugins/sticky-kit.min',
		'magiccart/wow'				: 'Magiccart_Alothemes/js/plugins/wow.min',
		// 'alothemes'					: 'Magiccart_Alothemes/js/alothemes',
	},

	shim: {
		'magiccart/easing': {
			deps: ['jquery']
		},
		'magiccart/bootstrap': {
			deps: ['jquery']
		},
		'magiccart/parallax': {
			deps: ['jquery']
		},
		'magiccart/socialstream': {
			deps: ['jquery']
		},
		'magiccart/slick': {
			deps: ['jquery']
		},
		'magiccart/zoom': {
			deps: ['jquery']
		},
		'magiccart/lazyload': {
			deps: ['jquery']
		},
		'magiccart/sticky': {
			deps: ['jquery']
		},
		'magiccart/wow': {
			deps: ['jquery']
		},
        'alothemes': {
            deps: ['jquery', 'magiccart/easing', 'magiccart/slick' , 'magiccart/zoom']
        },

	}

};
