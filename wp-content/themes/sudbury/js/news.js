/**
 * @author wetherbeei
 */
$(document).ready(function () {
	$("div.story a.readmore").each(function() {
		$this = $(this);
		$this.click(function() {
			$story = $(this).closest(".story").children(".text");
			$.get("/ajax.php?g=news&id=" + $story.parent().attr("data-id"), function(data) {
				if (data != "") {
					$story.html(data).addClass("selected-news");
				}
			});
			$(this).remove();
			return false;
		}).hide();
	});
	$("div.story").hover(function () {
		$("a.readmore", this).stop(true, true).fadeIn();
	}, function () {
		$("a.readmore", this).stop(true, true).fadeOut();
	});            
});
