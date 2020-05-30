jQuery(function ($) {
	$(document).ready(function () {
		$('#enable_expire').change(function () {
			if ($(this).is(':checked')) {
				$('.post-expiration-timestamp input, .post-expiration-timestamp select').removeAttr('disabled')
				$("#timestampdiv_expire .dhtmlxcalendar_container").fadeIn(250);
			} else {
				$('.post-expiration-timestamp input, .post-expiration-timestamp select').attr('disabled', 'disabled');
				$("#timestampdiv_expire .dhtmlxcalendar_container").fadeOut(250);
			}
		});

		/* based on /wp-admin/js/post.js?ver=3.9.1:815-841 */
		$timestampdiv_expire = $('#timestampdiv_expire');

		$timestampdiv_expire.siblings('a.edit-timestamp').click(function (event) {
			if (!$('#enable_expire').is(':checked')) {
				$("#timestampdiv_expire .dhtmlxcalendar_container").hide();
			}
			if ($timestampdiv_expire.is(':hidden')) {
				$timestampdiv_expire.slideDown('fast');
				$('#mm_expire').focus();
				$(this).hide();
			}
            backupValues();
			event.preventDefault();
		});

		$timestampdiv_expire.find('.cancel-timestamp').click(function (event) {
			$timestampdiv_expire.slideUp('fast').siblings('a.edit-timestamp').show().focus();
			$('#mm_expire').val($('#hidden_mm_expire').val());
			$('#jj_expire').val($('#hidden_jj_expire').val());
			$('#aa_expire').val($('#hidden_aa_expire').val());
			$('#hh_expire').val($('#hidden_hh_expire').val());
			$('#mn_expire').val($('#hidden_mn_expire').val());
			updateText();
			event.preventDefault();
		});
        
		$timestampdiv_expire.find('.save-timestamp').click(function (event) { // crazyhorse - multiple ok cancels
			if (updateText()) {
				$timestampdiv_expire.slideUp('fast');
				$timestampdiv_expire.siblings('a.edit-timestamp').show();
			}
			event.preventDefault();
		});

		// A wimpy version of wordpress's updateText()
		function updateText() {
			$text = $('#timestamp_expire b');

			if (!$('#enable_expire').is(':checked')) {
				$text.text('never');
			} else {
				$text.text($('#aa_expire').val() + '-' + $('#mm_expire').val() + '-' + $('#jj_expire').val() + ' ' + $('#hh_expire').val() + ':' + $('#mn_expire').val());
			}
			$timestampdiv_expire.slideUp();
			$timestampdiv_expire.siblings('a.edit-timestamp').show()
		}
        
        function backupValues() {
            $('#hidden_mm_expire').val($('#mm_expire').val());
            $('#hidden_jj_expire').val($('#jj_expire').val());
            $('#hidden_aa_expire').val($('#aa_expire').val());
            $('#hidden_hh_expire').val($('#hh_expire').val());
            $('#hidden_mn_expire').val($('#mn_expire').val());
        }
        $('#timestampdiv_expire select, #timestampdiv_expire input, #timestampdiv select, #timestampdiv input').change(function () {
  var mm = $('#mm').val();
  var jj = $('#jj').val();
  var aa = $('#aa').val();
  var hh = $('#hh').val();
  var mn = $('#mn').val();

  var start_date = new Date(aa, mm, jj, hh, mn, 0, 0);

  var mm_expire = $('#mm_expire').val();
  var jj_expire = $('#jj_expire').val();
  var aa_expire = $('#aa_expire').val();
  var hh_expire = $('#hh_expire').val();
  var mn_expire = $('#mn_expire').val();
  
  var expire_date = new Date(aa_expire, mm_expire, jj_expire, hh_expire, mn_expire, 0, 0);

  if ( $('#enable_expire').is(':checked') && start_date >= expire_date ) {
      if ($('#date_picker_error').length == 0) {
        $('#post').before('<div id="date_picker_error" class="error"><p>The expire date is earlier than the start date</p></div>');
        $('#post [type="submit"]').prop('disabled', true);
      }
  } else {
      if ($('#date_picker_error').length > 0) {
        $('#date_picker_error').remove()
        $('#post [type="submit"]').prop('disabled', false);
      }
  }
}); 
	})
})
