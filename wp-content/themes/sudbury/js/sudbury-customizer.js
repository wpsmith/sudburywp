/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and
 * then make any necessary changes to the page using jQuery.
 */
(function ($) {
	//$('#logo img').data('name', $('#logo img').attr('data-name')).data('note', $('#logo img').attr('data-note')).data('color', $('#logo img').attr('data-color'));
	function updateLogo() {
	}

	// Update the site CSS in real time...
	wp.customize('custom_css', function (value) {
		value.bind(function (newval) {
			$('<style>' + newval + '</style>').appendTo('head');
		});
	});
	// Update the site title in real time...
	wp.customize('blogname', function (value) {
		value.bind(function (newval) {
			$('#logo img').data('name', newval);
			updateLogo();
		});
	});

	//Update the site description in real time...
	wp.customize('blogdescription', function (value) {
		value.bind(function (newval) {
			$('#logo img').data('note', newval);
			updateLogo();
		});
	});

	//Update site title color in real time...
	wp.customize('header_textcolor', function (value) {
		value.bind(function (newval) {
			$('#logo img').data('color', newval.substring(1));

			updateLogo();
		});
	});

	//Update site background color...
	wp.customize('background_color', function (value) {
		value.bind(function (newval) {
			$('#header').css('background-color', newval);
		});
	});

})(jQuery);