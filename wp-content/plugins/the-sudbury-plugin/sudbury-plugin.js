// The Sudbury Plugin Javascript Includes
// Authors: Eddie Hurtig
// Copyright 2010-2018 The Town of Sudbury
// Contact: Information Systems at https://sudbury.ma.us/infosys/

jQuery(document).ready(function ($) {
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
				var department_link = '';
				if ( person.dept ) {
					department_link = $('<a/>').attr('href', person.dept.link).html( person.dept.name )[0].outerHTML;
				} 
				return {
					'Name'           : person.full_name,
					'Position'       : person.title,
					'Office Location': person.building,
					'Department'     : department_link,
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
				var committee_link = member.board;
				if ( member.site ) {
					committee_link = $('<a/>').attr('href', member.site.siteurl).text(member.site.blogname)[0].outerHTML;
				} 
				return {
					'Name'        : member.full_name,
					'Committee'   : committee_link,
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
});
