// BEGIN election.js

jQuery(document).ready(function ($) {

	var pcts = ["pct1", "pct1a", "pct2", "pct3", "pct4", "pct5"];

	$('#news-article table').each(function (index, element) {
		var current = $(this);
		$.each(pcts, function (index, element) {
			var setOut = false;
			var arr = current.find('.' + element);
			$.each(arr, function (index, element) {
				if (index == arr.length - 1) {
					setOut = true;
				}
				if ($(this).html() != "0") {
					return false;
				}

			});

			if (setOut) {
				$.each(arr, function (index, element) {
					$(this).addClass("grayed").html("");
				});
			}
		});
	});
});

// BEGIN menu.js

jQuery(document).ready(function ($) {
	$(".primary-menu > .menu-item > a").each(function (i, e) {
		var $e = $(e);
		$e.data('title-attr', $e.attr('title'));
		if (isNumber($e.attr('title'))) {
			$e.attr('title', '');
		}
	});
	$(".primary-menu > .menu-item").click(function (e) {
		var $this = $(this);
		if ($this.find('a').first().attr('href') == '#' || $this.find('a').first().attr('href') === undefined) {
			e.preventDefault(); // prevent a tag from linking to no-js page
		} else {
			return true;
		}

		if ($this.hasClass("open")) {
			$(".scroll").slideUp();
			$this.removeClass("open");
			$(".scroll").removeClass("open");
		} else {
			var $items = $this.children(".sub-menu").clone();
			var scroll_class = 'row mini-gutters';
			$menu = [];
			var num_parts = 4;
			var groups = $this.children('ul').children('li').length;

			if (groups > 1 && groups <= 6) {
				num_parts = groups;
			}
			// count the children elements when auto balancing
			var balance_ignore_children = false;
			// A Manual number of columns was specified
			if (isNumber($this.children('a').first().data("title-attr"))) {
				// Number of Columns should be the number specified
				num_parts = parseInt($this.children('a').first().data("title-attr"));
				// Balance the columns by looking at the op level li elements Only... it doesn't matter how many children elements it has
				balance_ignore_children = true;
				scroll_class += ' manual';
			} else {
				scroll_class += ' auto';
			}

			var $parts = list_split($items.children(), num_parts, balance_ignore_children);

			for (var i = 0; i < $parts.length; i++) {
				var cols = Math.floor(12 / num_parts);
				var percent = 100 / num_parts;
				// .css('flex', '0 0 ' + percent.toString() + '%').css('max-width', percent.toString() + '%')
				$menu.push($('<div/>').addClass('scroll-col col-lg').append($('<ul/>').addClass('sub-menu').append($parts[i])));
			}

			$menu = $('<div/>').addClass(scroll_class).append($menu);


			var $menu = $("<div/>").addClass("menu-body container-fluid").append($("<h1/>").html($this.children("a").text()))
				.append($menu)
				.append($("<div />").addClass("clear"));
			if ($(".scroll").hasClass("open")) {
				closeMenus(false);
				$(".scroll").fadeOut(200, function () {
					$(this).html($menu).fadeIn(200);
				});
			}
			else {
				$(".scroll").html($menu).slideDown(500).addClass("open");
			}
			$this.addClass("open");

			// Auto Scroll to the Scroll Menu
			jQuery([document.documentElement, document.body]).animate({
				scrollTop: jQuery(".scroll-wrap").offset().top - 120
			}, 350);

		}
	});
	$('body').click(function (event) {
		if (!$(event.target).closest('.primary-menu').length && !$(event.target).closest('.scroll').length) {
			closeMenus();
		}
	});

	function closeMenus($move) {
		$(".primary-menu > .menu-item").each(function (index, element) {
			if ($(this).hasClass("open")) {
				if ($move) {
					$(".scroll").slideUp(500).removeClass("open");
				}
				$(this).removeClass("open");
			}
		});
	}

	function list_split(a, parts, balance_ignore_children) {

		var toplvl_len = a.length;

		var $children = a.find('ul');

		var children_len = 0;

		$children.each(function (i, e) {
			children_len += $(e).children().length;
		})

		var total_len = children_len + toplvl_len;
		var items_per_col = Math.ceil(total_len / parts);

		if (balance_ignore_children) {
			items_per_col = Math.ceil(toplvl_len / parts)
		}
		var out = [];
		var j = 0;
		for (var i = 0; i < parts; i++) {
			out[i] = [];
			do {
				out[i].push(a[j]);
				j++;
			} while (($(out[i]).length + (balance_ignore_children ? 0 : $(out[i]).find('li').length)) < items_per_col && a[j] !== undefined);
		}
		return out;
	}

	/* Fly Out Links Menu */
	function flyouts() {

		$button_bar = $(".header-ext ul:first");
		$button_bar.children('li').hover(openSub).mouseleave(closeSubs);


		var flyoutDelay;

		function openSub(element) {
			clearTimeout(flyoutDelay);
			var $this = $(this);
			var $flyout = $this.children('.sub-menu');
			$button_bar.find('.sub-menu').not($flyout).fadeOut();

			if ($flyout.length > 0) {
				$flyout = $flyout.first();
				if (!$flyout.is(':visible')) {
					$flyout.fadeIn();
				}
			}
		};

		function closeSubs() {
			flyoutDelay = setTimeout(function () {
				$button_bar.find('.sub-menu').fadeOut();
			}, 250);
		};
	}

	flyouts();

});


function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

// BEGIN misc.js

timeout = null;

jQuery.fn.center = function () {
	this.css("position", "absolute");
	this.css("top", ($(window).height() - this.outerHeight()) / 2 + $(window).scrollTop() + "px");
	this.css("left", ($(window).width() - this.outerWidth()) / 2 + $(window).scrollLeft() + "px");
	return this;
};

jQuery(document).ready(function ($) {

	var search_location = ''

	function doSearch(e) {
		e.preventDefault()
		var google = 'https://www.google.com/search?q='
		var trailer = '+site%3Asudbury.ma.us'
		var term = encodeURIComponent(jQuery('#search').val())
		var url = google + term + trailer
		document.location.href = url;
	}

	$('.search-form').submit(doSearch);
	$('.search-btn').click(doSearch);

	$('a[data-scrambled]').each(function () {
		var $this = $(this);

		var to = $this.attr('data-to');
		var salt = $this.attr('data-salt');
		var domain = $this.attr('data-domain');
		var content = $this.attr('data-text');
		to = to.substring(salt.length, to.length - salt.length);
		content = content.substring(salt.length, content.length - salt.length);

		$this.attr('href', "mailto:" + to + "@" + domain).html(content);


	});

	$('.contact.cornerButton').css('right', ($('.settings.cornerButton').width() + 35) + 'px');

	$('.contact.cornerButton').click(function (e) {
		if (!$("#DeptSelect").hasClass('loaded')) {
			//hello me
			$.ajax({
				url: "/wp-admin/admin-ajax.php",

				data    : {
					get   : 'getDeptList',
					action: 'sudbury_api'
				},
				dataType: "text",
				error   : function () {
				},
				cache   : false,
				success : function (data) {
					try {
						data = $.parseJSON(data);
					} catch (e) {
						data = false;
					}
					$wrapper = $('#DeptSelect');
					$wrapper.html('');
					$wrapper.append('<option value="default">Search a Department / <span class="link">Committee</span></option>');

					$.each(data, function (index, value) {
						if (value.is_committee) {
							if (value.encode) {
								$wrapper.append('<option class="link" data-com="true" value="' + decodeURIComponent(value.BoardMembershipKey).replace(/\+/g, " ") + '">' + value.long_name + '</option>');
							}
							else {
								$wrapper.append('<option class="link" data-com="false" value="' + value.BoardMembershipKey + '">' + value.long_name + '</option>');
							}
						}
						else {
							$wrapper.append('<option data-com="false" value="' + value.long_name + '">' + value.long_name + '</option>');
						}
					});
					$wrapper.addClass('loaded');
					$wrapper.bind('change', function () {
						if ($(this).val() != 'default') {
							$(".searchStaff").val($.trim($(this).val()));
							search(($("option:selected", this).attr('data-com') == 'true' ? true : false));
						}
					})
				}
			});
		}

		$("div.contact ul:hidden").show(200);
	});
	$('html, body').click(function (e) {
		if ($(e.target).closest('.contact').attr('id') != $(".contact").attr('id')) {
			$(".contact ul:visible").hide(200);
		}
	});

	var delay = (function () {
		var timer = 0;
		return function (callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	$('.searchStaff').keyup(function (event) {
		if (event.keyCode == 13 /* enter */) {
			search(false);
			event.preventDefault();
			event.stopPropagation();
			return false;
		} else {
			timeout = delay(function () {
				search(false);
			}, 400);
			;
		}
	});

	search = function (encodeBack) {
		value = $('.searchStaff').val();
		if (value != '') {
			encodedvalue = '';
			if (encodeBack) {
				encodedvalue = value.replace(/,/g, '%2C').replace(/-/g, '%2D').replace(/\./g, '%2E');
			}
			_searchroot = this;

			openRequest();
			$.ajax({
					url     : "/wp-admin/admin-ajax.php",
					data    : {
						s_search: true,
						term    : (encodeBack ? encodedvalue : value),
						get     : 'staff',
						dept    : 'infosys',
						action  : 'sudbury_api'
					},
					dataType: "text",
					cache   : false,
					error   : function () {
						closeRequest();
					},
					success : function (data) {
						closeRequest();

						try {
							data = $.parseJSON(data);
						} catch (e) {
							data = false;
						}

						$wrapper = $("#staffResults");
						$wrapper.html("<h2>Staff</h2>");
						$list = $("<ul/>");
						if (data.length == 0) {
							$wrapper.append("<li><i>No staff found.</i></li>");
						}
						else {
							$.each(data, function (i, lists) {

								$list.append("<li>" + lists.title + "</li>");
								var $a = $list.find('a.eml').last();
								var pre = "marto".replace('r', 'il') + ':' + $a.attr('data-ed');
								var dmn = ($a.attr('data-dmn') == '' ? 'sudburt'.replace('t', '') + 'y.ma.us' : $a.attr('data-dmn'));
								$a.attr('href', pre + '@' + '' + dmn).html(pre.substring(7, pre.length) + '@' + '' + dmn);

								$wrapper.append($list);
							});
						}

						$('.searchStaff').focus();
					}
				}
			)
			;

			openRequest();
			$.ajax({
				url     : "/wp-admin/admin-ajax.php",
				data    : {
					s_search: true,
					term    : value,
					get     : 'members',
					dept    : 'infosys',
					action  : 'sudbury_api'
				},
				dataType: "text",
				error   : function () {
					closeRequest();

				},
				cache   : false,
				success : function (data) {
					closeRequest();

					try {
						data = $.parseJSON(data);
					} catch (e) {
						data = false;
					}

					$wrapper = $("#memberResults");
					$wrapper.html("<h2>Board Members</h2>");
					$list = $("<ul/>");

					if (data.length == 0) {
						$wrapper.append("<li><i>No members found.</i></li>");
					} else {
						$.each(data, function (i, lists) {

							$list.append("<li>" + lists.title + "</li>");
							var $a = $list.find('a.eml').last();
							var pre = "marto".replace('r', 'il') + ':' + $a.attr('data-ed');
							var dmn = ($a.attr('data-dmn') == '' ? 'sudburt'.replace('t', '') + 'y.ma.us' : $a.attr('data-dmn'));
							$a.attr('href', pre + '@' + '' + dmn).html(pre.substring(7, pre.length) + '@' + '' + dmn);

							$wrapper.append($list);
						});
					}
					$('.searchStaff').focus();
				}
			});
		}
		else {
			$("#memberResults,#staffResults").html("<ul><li><i>No matches found.</i></li></ul>");
		}
		_searchroot = this;


	};

	openRequests = 0;
	closeRequest = function () {
		openRequests--;
		if (openRequests == 0) {
			$('#TopContactForm .loading').fadeOut();
		}
	};
	openRequest = function () {
		openRequests++;
		$('#TopContactForm .loading').fadeIn();

	};

	if ($("#main-col .news-article table").attr("cellPadding") != "") {
		$("#main-col .news-article table tr td").css({
			padding: $("#main-col .news-article table").attr("cellPadding") + 'px'
		});
	}
	if ($("#main-col .news-article table").attr("cellSpacing") != "") {
		$("#main-col .news-article table tr td").css({
			margin: $("#main-col .news-article table").attr("cellSpacing") + 'px'
		});
	}

	// $('.em-map-balloon-content').each(function (index, element) {
	// 	$(element).last('a').attr('href', 'https://maps.google.com/search?q='.encodeUrl('')).text('Directions');
	// });

	var filterLock = 0;

	var jsfilter = function () {
		var $this = $('#filterer');

		if ($this.length === 0)
			return;

		if ($this.data('total-results') === undefined) {
			// we haven't checked yet
			$this.data('total-results', $($this.data('results-placeholder')).text());
		}

		var terms = $this.val().trim().toLowerCase().split(' ');
		var groups = $(document);
		if ($this.data('groups')) {
			groups = $($this.data('groups'));
		}

		groups.each(function (index, group) {

			var $group = $(group)
			var $target = $group.find($this.data('target'));
			var results = 0;
			var myFilterLock = ++filterLock;

			$target.each(function (index, elem) {
				if (filterLock != myFilterLock) {
					console.log('Lock Lost')
					return false;
				}
				var $elem = $(elem);
				if (terms.length == 0) {
					$elem.css('display', 'table-row');
					results++;
				} else {
					var text = $elem.text().toLowerCase();
					for (var i = 0; i < terms.length; i++) {
						if (text.indexOf(terms[i]) == -1) {
							$elem.css('display', 'none');
							break;
						} else if (i == terms.length - 1) {
							$elem.css('display', 'table-row');
							results++;
						}
					}
				}
			});

			var total_results = $this.data('total-results');
			if (results > total_results) {
				results = total_results;
			}
			$group.find($this.data('results-placeholder')).html(results);
		});
	};

	// run filter on keyup
	var delayTime;
	$('#filterer').keyup(function (e) {
		clearTimeout(delayTime);
		if (e.which == 13) {
			jsfilter();
		} else {
			delayTime = setTimeout(jsfilter, 1);
		}
	});

	// run filter on click
	$('.filter-button').click(jsfilter);

	// run filter on page load
	jsfilter();

	function switch_tab($this, tab_name) {
		var $target = $(tab_name);

		$this.parents('ul').find('a').removeClass('tab-active');
		$this.find('a').addClass('tab-active');

		$target.parent().children().removeClass('panel-active');
		$target.addClass('panel-active');
	}

	$('.tabs li').click(function (e) {
		e.preventDefault();
		switch_tab($(this), $(this).find('a').attr('href'));
	});

	var selected_tab = '#' + getParameterByName('tab') + '-panel';
	$('.tabs li').each(function (i, e) {
		var $this = $(e);
		if ($this.find('a').attr('href') == (selected_tab)) {
			switch_tab($this, selected_tab);
		}
	});


	function set_table(table, data) {
		var $table = $(table)

		if (data.length == 0) {
			data = [{'No Results': ''}];
		}

		$thead = $('<thead/>').append($('<tr/>'))
		for (var key in data[0]) {
			$thead.append($('<th/>').html(key));
		}

		$tbody = $('<tbody/>')
		for (var row in data) {
			var $tr = $('<tr/>')
			for (var header in data[row]) {
				var cell = data[row][header];
				$tr.append($('<td/>').html(cell));
			}
			$tbody.append($tr);
		}

		$table.empty();
		$table.append($thead);
		$table.append($tbody);
	}

	function search_blogs(term) {
		$.ajax({
			url   : wpApiSettings.root + 'sudbury/v1/sites/?search=' + term,
			method: 'GET'
		}).done(function (response) {
			console.log(response);
			var departments_table = []
			var committees_table = []
			for (var key in response) {
				var site = response[key];

				var directions = '';
				var address = '';
				if (site.location !== undefined) {
					var directions_url = "https://www.google.com/maps?daddr=" + encodeURIComponent(site.address + ', ' + site.town + ', ' + site.state + ' ' + site.postcode);

					directions = $('<a/>').attr('href', directions_url).html('Directions')[0].outerHTML;
					address = $('<a/>').attr('href', site.location.link).html(site.location.name)[0].outerHTML + '<br>';
					address += site.location.address + '<br>' + site.location.town + ', ' + site.location.state + ' ' + site.location.postcode;
				}

				var row = {
					'Name'       : $('<a/>').attr('href', site.link).html(site.name)[0].outerHTML,
					'Address'    : address,
					'Directions' : directions,
					'Phone / Fax': (site.phone ? 'T: ' + site.phone + '<br>' : '') + (site.fax ? 'F: ' + site.fax : ''),
					'Email'      : $('<a/>').attr('href', 'mailto:' + site.email).html(site.email)[0].outerHTML,
				};

				if (site.types.indexOf('committee') >= 0) {
					committees_table.push(row);
				} else if (site.types.indexOf('department') >= 0) {
					departments_table.push(row);
				}
			}
			set_table('#committee-results', committees_table);
			set_table('#department-results', departments_table);

		});
		// [{'An Error Occurred': 'We could not search buildings at this time'}]);
	}


	function search_staff(term) {
		$.ajax({
			url   : wpApiSettings.root + 'sudbury/v1/staff/?search=' + term,
			method: 'GET'
		}).done(function (response) {
			console.log(response);
			var table = response.map(function (person) {
				return {
					'Name'           : person.full_name,
					'Position'       : person.title,
					'Office Location': person.building,
					'Department'     : person.dept,
				};
			});
			set_table('#staff-results', table);
			// [{'An Error Occurred': 'We could not search buildings at this time'}]);
		});
	}

	function search_members(term) {
		$.ajax({
			url   : wpApiSettings.root + 'sudbury/v1/members/?search=' + term,
			method: 'GET'
		}).done(function (response) {
			console.log(response);
			var table = response.map(function (member) {
				return {
					'Name'        : member.full_name,
					'Appointed By': member.appointed_by,
					'Position'    : member.position,
					'Started'     : member.appointed_year,
					'End of Term' : member.term_expiration,
				};
			});
			set_table('#member-results', table);
			// [{'An Error Occurred': 'We could not search buildings at this time'}]);
		});
	}

	function search_buildings(term) {
		$.ajax({
			url   : wpApiSettings.root + 'wp/v2/locations/?search=' + term,
			method: 'GET'
		}).done(function (response) {
			console.log(response);
			var table = response.map(function (post) {
				var directions_url = "https://www.google.com/maps?daddr=" + encodeURIComponent(post.address + ', ' + post.town + ', ' + post.state + ' ' + post.postcode);
				return {
					'Location Name': $('<a/>').attr('href', post.link).html(post.title.rendered)[0].outerHTML,
					'Address'      : post.address,
					'Directions'   : $('<a/>').attr('href', directions_url).html('Map')[0].outerHTML
				};
			});
			set_table('#location-results', table);
			// [{'An Error Occurred': 'We could not search buildings at this time'}]);
		});
	}

	$('.contact-search').submit(function (e) {
		e.preventDefault();
		var term = $(this).find('[name="term"]').val()

		search_buildings(term);

		search_blogs(term);

		search_staff(term);

		search_members(term);

	});

	$.ajaxSetup({
		beforeSend: function (xhr) {
			xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
		}
	});


})
;

function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, "\\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, " "));
}
