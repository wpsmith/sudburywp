$(document).ready(function(e) {
    $("#SearchStaffInput").keyup(function () {
		var value = $(this).val();
		$.ajax({
			url: "/ajax.php",
			data: {ss: value},
			dataType: "text",
			error: function() {/*console.error("Ajax error")*/},
			success: function (data) {
				$resultsDiv = $('#ajaxSearchResults');
				$resultsDiv.html(data);
				try {
					data = $.parseJSON(data);
				} catch (e) {
					data = false;
				}
				
				

				
	

				$Staff = '<div class="staff">';
				$Comittee = '<div class="committee"><h2>';
				$.each(data.items, function(i, result) {
					$person = '';
					if (result.title == "Sudbury Staff")
					{
						$.each(result.items, function(i, result) {
							
							$person = '<h3>' + result.name + '</h3><div><p style="width:33%; text-align:center; float:left;">Department: ' + result.building + '</p><p style="width:33%; text-align:center; float:left;">Located at:  ' + result.building + '</p><p style="width:33%; text-align:center; float:left;">Phone:' + result.phone + '</p></div>';  
							$resultsDiv.append($person);
						});
					}
					else
					{
						$.each(result.items, function(i, result) {
							$person = '<h3>' + result.name + '</h3><p style=" float:left;">Located at:  ' + result.board + '</p>';  
							$resultsDiv.append($person);
						});
						 
					}
				
					
					
				});
			
			}
		});
	});
});