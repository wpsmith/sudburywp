$(document).ready(function(e) {
    
	var pcts = [ "pct1", "pct1a", "pct2", "pct3", "pct4", "pct5" ];
	
	$('.news-article table').each(function(index, element) {
		var current = $(this);
		$.each(pcts,function (index, element) {
			var setOut = false; 
			var arr = current.find('.' + element);
			$.each(arr,function(index, element) {
				if (index == arr.length-1)
				{
					setOut = true;	
				}
				if ($(this).html() != "0")
				{
					return false;	
				}
				
			});
			
			if (setOut)
			{
				$.each(arr,function(index, element) {
					$(this).addClass("grayed").html("");
				});
			}
		});
	});
});