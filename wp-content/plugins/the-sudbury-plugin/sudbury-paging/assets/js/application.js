$(document).ready(function () {
	if ($.cookie('paging_favorites') != null) {
		var data = JSON.parse($.cookie('paging_favorites'));
		$.each(data.fav, function (i, e) {
			$("#favorite-list").append($("#employee-list li a[data-id='" + e + "']").parent());
			$(".favorite-none").css("display", "none");
		});
	}
	$('#message-text').keyup(function () {
		if ($(this).val().length > 140) {
			$(".message-text-group").addClass("error")
			$(".message-counter").addClass("bk-error")
			$(".message-text-help-inline").fadeIn(500)
		}
		else if ($(".message-text-group").hasClass("error")) {
			$(".message-text-group").removeClass("error")
			$(".message-counter").removeClass("bk-error")
			$(".message-text-help-inline").fadeOut(500)
		}
		$(".message-counter").text(140 - $(this).val().length)
	})
	// Update Chars Remaining On Page Load
	$('#message-text').keyup();
	$(".form-search").submit(function (e) {
		e.preventDefault();
		$("#filter-employees").keyup();
	})
	$(".scrollto").click(function (e) {
		e.preventDefault();
		$('html, body').animate({
			scrollTop: $($(this).attr("href")).offset().top - 300
		}, 750);
	})
	$(".employee").click(function () {
		$("#message-to").val($(this).attr("data-number"))
		$("#message-name").val($(this).attr("data-name"))
		$("#post-id").val($(this).attr("data-id"))
		$("#blog-id").val($(this).attr("data-blog"))
		$("#service-provider").val($(this).attr("data-service"))
		$("#loading-bar").hide(500).addClass("active").addClass("progress-striped")
		$("#progress-bar").removeClass("progress-danger")
		$("#progress-message").html("Sending")

	})
	$("#filter-employees").keyup(function (e) {
		searchText = $("#filter-employees").val().toLowerCase();
		if (searchText != "") {
			$("#employee-list li").each(function (index, element) {
				if ($(element).html().toLowerCase().indexOf(searchText) != -1) {
					$(element).css("display", "block")
				}
				else {
					$(element).css("display", "none")
				}
			})
		}
		else {
			$("#employee-list li").each(function (index, element) {
				$(element).css("display", "block")
			})
		}
	})

	$(".remove-favorite").click(function (e) {
		e.preventDefault()
		removeFavorite(this)
	})
	function removeFavorite($this) {
		var currentCookie = JSON.parse($.cookie('paging_favorites'));
		var index = currentCookie.fav.indexOf($($this).parent().attr("data-id"));
		currentCookie.fav.splice(index, 1)
		$.cookie('paging_favorites', JSON.stringify(currentCookie), {expires: 365});

		$($this).parent().parent().slideUp(500);
		$("#employee-list").prepend($($this).parent().parent());
		$("#employee-list li:first").slideDown();
	}

	$("#add-favorite").click(function () {

		$("#favorite-list").append($("#employee-list li[style*='display: block;']:first").css("display", "none"));
		$(".favorite-none").slideUp(500, function () {
			$(".favorite-none").remove()
		});
		$("#favorite-list li:last").slideDown(500).find("a b").bind("click", function (e) {
			e.preventDefault()
			removeFavorite(this)
		});
		if ($.cookie('paging_favorites') != null) {
			var currentCookie = JSON.parse($.cookie('paging_favorites'));
			currentCookie.fav.push($("#favorite-list li:last a").attr("data-id"))
			$.cookie('paging_favorites', JSON.stringify(currentCookie), {expires: 365});
		}
		else {
			$.cookie('paging_favorites', JSON.stringify({fav: [$("#favorite-list li:last a").attr("data-id")]}), {expires: 365});
		}
		$("#filter-employees").val("").keyup()
	})
	$("#paging-form").submit(function (e) {
		e.preventDefault()
		$("#loading-bar").hide(500, function () {
			$("#loading-bar").show(500)
		})
		$("#progress-bar").addClass("active").addClass("progress-striped").removeClass("progress-danger").removeClass("progress-success")
		$("#progress-message").html("Sending")


		$.ajax({
			cache  : false,
			type   : "POST",
			url    : "api.php",
			data   : $("#paging-form").serialize(),
			success: function (msg) {
				console.log(msg);
				$("#success-modal").modal()
				$("#progress-bar").removeClass("active").removeClass("progress-striped")
				if (msg.message) {
					$("#progress-bar").addClass("progress-success")
					$("#progress-message").html(msg.message)

				}
				else {
					$("#progress-bar").addClass("progress-danger")
					if (msg.error) {
						$("#progress-message").html(msg.error)
					} else {
						$("#progress-message").html(msg)
					}
				}


			},
			error  : function (msg) {
				$("#progress-bar").addClass("progress-danger").removeClass("active").removeClass("progress-striped")
				$("#progress-message").html("Error! <hr> Code: " + msg.error + "<br>" + msg.responseText)
			}
		})

	})
})