/*
 * Sudbury Admin Javascript File
 * - jQuery block
 * - String prototypes
 */
(function ($) {
	jQuery(document).ready(function () {
		// Add Public Archives status to the Post Status Dropdown
		$("#post_status, [name='_status']").append($("<option/>").val('public-archive').text('Public Archives')).change(function () {
			setTimeout(function () {
				if ('public-archive' == $("#post_status").val()) {
					$("#publish").text("Update");
					$("#post-status-display").val("Public Archives");
				}
			}, 1);
		});

		$("#post_status, [name='_status']").append($("<option/>").val('publish').text('Re-Publish')).change(function () {
			setTimeout(function () {
				if ('publish' == $("#post_status").val()) {
					$("#publish").text("Publish");
					$("#post-status-display").val("Re-Publish");
				}
			}, 1);
		});

		// Click the Refresh link for the selected attachment in the media modal
		function refreshAttachments() {
			console.log('On Focus');
			$('.refresh-attachment').click();
		};

		if ("onfocusin" in document) {
			document.onfocusin = refreshAttachments;
		} else {
			window.onfocus = refreshAttachments;
		}

		/*
		 * If the post status is public-archive then set the selected post status to public-archive
		 */
		function init_post_status() {
			if ('public-archive' == $("#original_post_status, ._status").first().val()) {
				if ('undefined' !== $("#post-status-display")) {
					$("#post-status-display").text("Public Archives");
				}

				$("#post_status, ._status").first().val('public-archive');
				if ('undefined' !== $("#publish")) {
					$("#publish").val("Update");
				}
			}
		}

		init_post_status();

		// A really simple way to link the value of one element to the value of another
		// element without aditional JS
		$('.sudbury_set_remote_value').click(function (e) {
			e.preventDefault();
			var $this = $(this);
			$($this.attr('data-target')).val($($this.attr('data-source')).val());
		});

		$('.sudbury_set_remote_value_html').click(function (e) {
			e.preventDefault();
			var $this = $(this);
			$($this.attr('data-target')).val($($this.attr('data-source')).html());
		});

		$('.check-toggle').click(function () {
			$target = $($(this).attr('data-toggle'));

			if ($(this).is(':checked') != $(this).hasClass('toggle-inverted')) {
				$target.slideDown(500);
				if ($(this).hasClass('toggle-disabled')) {
					$target.find('input, select, textarea').removeAttr('disabled');
				}
			}
			else {
				$target.slideUp(500);
				if ($(this).hasClass('toggle-disabled')) {
					$target.find('input, select, textarea').attr('disabled', 'disabled');
				}
			}
		})

		// Timepicker Logic
		mouseDown = 0;
		document.body.onmousedown = function () {
			++mouseDown;
		}
		document.body.onmouseup = function () {
			--mouseDown;
		}

		var registered_timepickers = [];
		$('.sudbury_datetimepicker').each(function (index, element) {
			$(this).datetimepicker({
				showButtonPanel: true,
				timeFormat     : "HH:mm:ss",
				showSecond     : false
			});

			$input = $(element)

			if ($input.attr('name').indexOf("start") > -1) {
				$input.data('picker_type', 'start');
			} else if ($input.attr('id').indexOf("end") > -1) {
				$input.data('picker_type', 'end');
			} else {
				$input.data('picker_type', 'other');
			}

			$input.data('old_value', $input.val());

			registered_timepickers.push($(this));
		});

		/**
		 * Assumes value in mm/dd/yyyy hh:mm format
		 * @param $val
		 * @return int unix timestamp
		 */
		function parse_datepicker_time($val) {
			return Date.parse($val);
		}

		function pad_num(n, width, z) {
			z = z || '0';
			n = n + '';
			return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
		}

		// @remove
		function datepickerbuttons(input, changed_calendar) {
			setTimeout(function () {
				var buttonPane = $(input)
					.datepicker("widget")
					.find(".ui-datepicker-buttonpane");

				if (changed_calendar)
					$('.ui-extra-button').remove();

				if ($(input).datepicker("widget").find('.ui-added-by-cmy').length != 0) {
					$(input).datepicker("widget").find('.ui-added-by-cmy').removeClass('.ui-added-by-cmy');
					return;
				}
				$("<button>", {
					text : "Clear",
					click: function () {
						//Code to clear your date field (text box, read only field etc.) I had to remove the line below and add custom code here
						$.datepicker._clearDate(input);
						$(input).datepicker("hide")
					}
				}).appendTo(buttonPane).addClass("ui-datepicker-clear ui-extra-button ui-state-default ui-priority-primary ui-corner-all" + (changed_calendar ? ' ui-added-by-cmy' : ''));

				$("<button>", {
					text : "+Week",
					click: function () {
						//Code to clear your date field (text box, read only field etc.) I had to remove the line below and add custom code here
						var currentDate = $(input).datepicker('getDate');
						var d = new Date(currentDate);

						var newdate = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 7);
						$(input).datepicker('setDate', newdate);
						if (!changed_calendar)
							datepickerbuttons(input, false); // Reinitialize Datepicker Buttons
					}
				}).appendTo(buttonPane).addClass("ui-datepicker-plus-week ui-extra-button ui-state-default ui-datepicker-current ui-priority-primary ui-corner-all" + (changed_calendar ? ' ui-added-by-cmy' : ''));
			}, 1);
		}

		$("#excerpt").keyup(function (e) {
			var text = $(this).val();
			if (text.length > 90) {
				$(this).addClass('error');
			} else {
				$(this).removeClass('error');
			}
		});
		$(".sudbury_datepicker_add_week").click(function (e) {
			e.preventDefault();
			var target = $(this).attr("data-target");
			var number = $(this).attr("data-number");
			var currentDate = $(target).datetimepicker('getDate');
			var d = new Date(currentDate);

			var newdate = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 7 * number, d.getHours(), d.getMinutes(), d.getSeconds());
			$(target).datetimepicker('setDate', newdate);
		});

		$(".sudbury_datepicker_set_date").click(function (e) {
			e.preventDefault();
			var target = $(this).attr("data-target");
			var date = $(this).attr("data-date");
			var d = new Date(date);

			$(target).datetimepicker('setDate', d);
		});
		// Show/Hide the redirect enabled box
		$('#sudbury_redirect_enabled').change(function () {
			if ($(this).is(':checked')) {
				$("#sudbury_wp_editor_wrapper").css('opacity', 0);
				$("#sudbury_wp_editor_wrapper").hide(500)
				$("#sudbury_redirect_url_container").slideDown("slow");
			} else {
				$("#sudbury_wp_editor_wrapper").css('opacity', 1);
				$("#sudbury_wp_editor_wrapper").show(500)
				$("#sudbury_redirect_url_container").slideUp("slow");
			}
		});

		// Checks the parent categories automatically when a child category is checked
		$('.categorydiv input[type="checkbox"]').change(function () {
			$parents = $(this).parents('li');
			var checked = $(this).is(':checked')
			$parents.each(function (i, parent) {
				$parent = $(parent).find('input[type="checkbox"]').first()

				if (checked && !$parent.is(':checked')) {
					$parent.attr('checked', 'checked');
				} else if (!checked && $parent.is(':checked')) {
					$parent.removeAttr('checked');
				}
			})
		});

		$('.add_tag_suggestion').click(function () {
			$('#new-tag-post_tag').val($(this).text());
			$('.button.tagadd').click(); //invoke the add button click
		})

		// Will automatically pull in the Post Content for the parent location when
		// a parent location is selected
		$('#sudbury_parent_location_id').change(function () {
			$this = $(this);
			if ($this.val() != 'none') {

				$.ajax({
					type: "POST",
					url : $this.attr('data-autocomplete-url') + '&location_id=' + $this.val(),
					data: {}
				}).done(function (json) {
					try {
						data = JSON.parse(json);
					} catch (e) {

						console.log('JSON PARSE FAILED WITH EXCEPTION')
						console.log(e);
						console.log('FOR FOLLOWING DATA');
						console.log(json);
						alert('An Error Has Occurred Parsing JSON Code, please contact a System Admin');
					}

					var indx;
					var $title_input = $('[name="post_title"]').first()
					if ((indx = $title_input.val().indexOf(' - ')) != -1) {
						// Remove the content
						$title_input.val($title_input.val().substring(indx + 3));
					}

					if ('' == tinyMCE.activeEditor.getContent()) {
						tinyMCE.activeEditor.setContent(data.post_content);
					} else {
						var r = confirm("I can also update the contents to " + data.location_name + "'s Contents [OK or Cancel]");
						if (r == true) {
							tinyMCE.activeEditor.setContent(data.post_content);
						}
					}


					$title_input.val(data.location_name + ' - ' + $title_input.val());
					$.each(data, function (key, value) {
						if ($('[name="' + key + '"]').length > 0 && key != 'location_id') {
							$('[name="' + key + '"]').val(value);
							$('[name="' + key + '"]').change();
						}
					})
				})
			} else {
				if ((indx = $('[name="post_title"]').val().indexOf(' - ')) != -1) {
					$('[name="post_title"]').val($('[name="post_title"]').val().substring(indx + 3));

					if (tinyMCE.activeEditor.getContent() != '') {
						var r = confirm("Do you want to clear the Content the editor too?");
						if (r == true) {
							tinyMCE.activeEditor.setContent('');
						}
					}
				}
			}
		});

		/* Network User Manager */
		$('.sudbury-unassign-blog').click(function () {
			var selected = $('#sudbury_assigned_sites option:selected');

			selected.each(function (i, e_from) {
				var $e_from = $(e_from);
				var available = $('#sudbury_available_sites').children();
				$e_from.text($e_from.attr('data-blogname'));
				if (available.length > 0) {
					available.each(function (i, e_dest) {
						var $e_dest = $(e_dest);


						if ($e_dest.text() > $e_from.attr('data-blogname')) {
							$e_from.insertBefore($e_dest);
							return false;
						}

						if (i == available.length - 1) {
							$e_from.insertAfter($e_dest);
						}
					});
				} else {
					$('#sudbury_available_sites').append($e_from);
				}
			});

			$('#sudbury_assigned_sites').remove(':selected');
		});

		$('.sudbury-assign-blog').click(function () {
			var selected = $('#sudbury_available_sites option:selected');
			var $button = $(this);

			var role = $button.attr('data-role');
			var role_text = $button.attr('data-role-text')
			selected.each(function (i, e_from) {
				var $e_from = $(e_from);
				var assigned = $('#sudbury_assigned_sites').children();

				$e_from.attr('data-role', role);
				$e_from.text(role_text + ' @ ' + $e_from.attr('data-blogname'));

				if (assigned.length > 0) {
					assigned.each(function (i, e_dest) {
						var $e_dest = $(e_dest);

						if ($e_dest.attr('data-blogname') > $e_from.attr('data-blogname')) {
							$e_from.insertBefore($e_dest);
							return false;
						}

						if (i == assigned.length - 1) {
							$e_from.insertAfter($e_dest);
						}
					});
				} else {
					$('#sudbury_assigned_sites').append($e_from);
				}

			});

			$('#sudbury_available_sites').remove(':selected');
		});
		// need to select all options to post the values back
		$('#sudbury_user_manager_form').submit(function (e) {
			e.preventDefault();
			$('.sudbury-spinner').fadeIn();
			$('#sudbury_assigned_sites option').each(function (i, e) {
				var $option = $(e);
				$option.val($option.val() + '-' + $option.attr('data-role'));
			});

			$('#sudbury_available_sites option').attr('selected', 'selected');
			$('#sudbury_assigned_sites option').attr('selected', 'selected');

			$.post(
				$('#sudbury_user_manager_form').attr('action'),
				$('#sudbury_user_manager_form').serialize(),
				function (data, msg) {
					$('.sudbury-spinner').fadeOut();
					//console.log(data);
					//console.log(msg);

					document.location.href = 'users.php';
				}
			);
		})

		/* My Sites Filtering */
		$('#my_sites_quick_go').keydown(function (event) {

			var input = $(this);
			var search = input.val();
			var sites = $('#myblogs').find('li')


			sites.each(function (i, site) {
				site = $(site);
				var matched = match(search, site);

				if (matched && site.hasClass('hide-if-js')) {
					site.removeClass('hide-if-js');
				} else if (!matched && !site.hasClass('hide-if-js')) {
					site.addClass('hide-if-js');
				}
			});

			var dest = sites.filter(':visible').first().find("a:contains('Dashboard')").attr("href")

			if (event.which == 13 && dest !== undefined) {
				window.location.href = dest;
				event.preventDefault();
			}


			function match(search, site) {
				return $(site).find('h3').text().toLowerCase().indexOf(search.toLowerCase()) != -1;
			}
		});

		/* Help Link */
		//Remove the purple help button when there is no contextual help
		if (!$('#contextual-help-link').is(':visible')) {
			$('.sudbury-help').css('display', 'none');
		}

		// When the purple Help Link is clicked, pop down the contextual Help
		$('.sudbury-help').click(function (e) {
			e.preventDefault();
			if ($('#contextual-help-wrap').is(':visible')) {
				screenMeta.close($('#contextual-help-wrap'), $('.show-settings'));
			} else {
				screenMeta.open($('#contextual-help-wrap'), $('.show-settings'));
			}
		});

		// Switch to the Visual Tab before saving a post
		$('#publish').click(function () {
			$('#content-tmce').click();
		})

		$('#publish').click(function (event) {
			errors = false
			$('.attachment-type').each(function (i, e) {
				if ($(e).find('input[type="radio"]').length && $(e).find('input[type="radio"]:checked').length == 0) {
					$(e).parent().prepend('<div class="error"><p>Please select a document type for this attachment</p></div>')
					errors = true
					event.preventDefault()
				}
			});
			if (errors) {
				alert('Some items failed validation, you cannot publish this meeting without correcting the noted issues');
				return false;
			}
		});

		/* Title Length and case checker */
		$('#title').keyup(function (e) {
			$title = $('#title');
			$warning = $('.length-warning');
			if ($title.val().length > 70) {
				if ($warning.lenth != 0) {
					$warning.hide();
				}
				$error = $('.length-error');
				if ($error.length == 0) {
					$title.after('<div class="error length-warning length-error"><p>To provide a clean looking article, this title must be shortened</p></div>');
					$('#publish').prop('disabled', true)
				} else {
					$error.show();
				}
			} else if ($title.val().length > 60) {
				$error = $('.length-error');
				$error.remove();
				if ($warning.length == 0) {
					$title.after('<div class="error length-warning"><p>Try to use 60 characters or fewer for titles.</p></div>');
				} else {
					$warning.show();
				}
				$('#publish').prop('disabled', false)
			} else {
				$('#publish').prop('disabled', false)
				$warning.hide()
			}
		})

		$('#title').change(function () {
			$title = $('#title');
			$prompt = $('.title-case-prompt');
			if ($title.val().toTitleCase() != $title.val() && $title.val().length < 70) {
				if ($prompt.length == 0) {
					$title.after('<div class="updated title-case-prompt"><p>This title isn\'t in title case.  Do you want to convert it?</p></div>');
					$('.title-case-prompt p').append($('<button/>').addClass('button-cancel button').text('Yes ').click(function () {
						$title.val($title.val().toTitleCase());
						$('.title-case-prompt').remove();
					})).append('<span>&nbsp;</span>').append($('<button/>').addClass('button-cancel button').text('No').click(function () {
						$('.title-case-prompt').remove()
					}))
				}
			} else {

			}
		});

		if (/[?&]clear-title/.test(location.href) && $('#post_type').val() == 'attachment') {
			$('#title').val('');
		}

		$('#title').prop('required', true);

		if ($('.compat-field-enable-media-replace .button-secondary').length > 0) {
			var new_url = $('.compat-field-enable-media-replace .button-secondary').attr('href');
			new_url = new_url + "&return_url=" + encodeURIComponent(window.location.href)
			$('.compat-field-enable-media-replace .button-secondary').attr('href', new_url);
		}

		// When the front-page-news cateogy is checked/unchecked then Post to Twitter should be changed
		$('#categorydiv .selectit [value="6"]').click(function () {
			document.getElementById('post_to_twitter_enabled').checked = $(this).is(':checked');
		});
	});

	function refferedOpen(url) {
		return window.open(url + '&referred=true&referrer_post_type=' + $('#post_type').val())
	}

	$.ajaxSetup({cache: true});
	$.getScript('https://connect.facebook.net/en_US/sdk.js', function () {
		$('.post_to_facebook').on('click', function (e) {
			var url = $(this).data('url')
			FB.init({
				appId  : '735250859854865',
				version: 'v2.7' // or v2.1, v2.2, v2.3, ...
			});
			FB.ui({
				method: 'share',
				href  : url
			}, function (response) {
				console.log(response)
			});

		});
	});
})(jQuery);

String.prototype.toTitleCase = function () {
	nontitled = ["a", "an", "the", "at", "by", "for", "in", "of", "on", "to", "up", "and", "as", "but", "or", "nor", "with", "is"];
	return this.replace(/\w\S*/g, function (word) {
		if (jQuery.inArray(word, nontitled) > -1) {
			return word;
		} else {
			return word.charAt(0).toUpperCase() + word.substr(1).toLowerCase();
		}
	});
}

String.prototype.escapehtml = function () {
	var tagsToReplace = {
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		'"': '&quot;',
		"'": '&#39;',
		"/": '&#x2F;'
	};
	return this.replace(/[&<>]/g, function (tag) {
		return tagsToReplace[tag] || tag;
	});
};
