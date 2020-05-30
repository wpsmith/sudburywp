jQuery(document).ready(function ($) {
	function updateImage($url_field) {
		$url_field.siblings('img').attr('src', $url_field.val());
	}

	$(".upload_image_url").on("change", function (e) {
		updateImage($(this));
	});

	$(document).on("keyup", ".icon-select", function (e) {
		var $icon_field = $(this);
		$($icon_field.data('for')).attr('class', $icon_field.val());
	});


	$(document).on("click", ".upload_image_button", function (e) {
		e.preventDefault();
		var $button = $(this);


		// Create the media frame.
		var file_frame = wp.media.frames.file_frame = wp.media({
			title   : 'Select or upload image',
			library : { // remove these to show all
				type: 'image' // specific mime
			},
			button  : {
				text: 'Select'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});

		// When an image is selected, run a callback.
		file_frame.on('select', function () {
			// We set multiple to false so only get one image from the uploader

			var attachment = file_frame.state().get('selection').first().toJSON();

			var thumbnail_url = attachment.url;
			if (attachment.sizes.thumbnail) {
				thumbnail_url = attachment.sizes.thumbnail.url;
			}
			$button.siblings('input').val(thumbnail_url);
			updateImage($($button.data('for')));
			$($button.data('for')).change();

		});

		// Finally, open the modal
		file_frame.open();
	});
});