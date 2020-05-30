<?php
/**
 * The metabox for editing the the office location of a department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data Sudbury Department Settings Data Array
 */
function sudbury_office_location_meta_box( $data ) { ?>
	<div class="sudbury-metabox sudbury-contact-metabox">
		

<label for="sudbury_location_id"> Central Office Location<br>
			<?php
			$locations = sudbury_get_raw_locations( ARRAY_A );

			if ( empty( $locations ) ) {
				echo 'No Locations were found';
			} else {

				?>
				<select name="sudbury_location_id" id="sudbury_location_id">
					<option data-name="NOT SET"
							data-address="NOT SET"
							data-town="NOT SET"
							data-state="NOT SET"
							data-zip="0">Select an Office Location
					</option>
					<?php foreach ( $locations as $location ) : ?>
						<option value="<?php echo esc_attr( $location['location_id'] ); ?>"
								data-name="<?php echo esc_attr( $location['location_name'] ); ?>"
								data-address="<?php echo esc_attr( $location['location_address'] ); ?>"
								data-town="<?php echo esc_attr( $location['location_town'] ); ?>"
								data-state="<?php echo esc_attr( $location['location_state'] ); ?>"
								data-zip="<?php echo esc_attr( $location['location_postcode'] ); ?>"
							<?php selected( $location['location_id'], $data['sudbury_location_id'] ); ?>> <?php echo esc_html( sprintf( '%s (%s)', $location['location_name'], $location['location_address'] ) ); ?> </option>
					<?php endforeach; ?>
				</select>
			<?php } ?>
		</label><br>

		<label for="sudbury_address">Address Annotation<br>
			<textarea id="sudbury_address" name="sudbury_address" class="form-input-tip"><?php echo esc_textarea($data['sudbury_address']); ?></textarea>	
				
			<div class="sudbury_address_preview">
				Loading Preview...
			</div>
			
		</label>

		<script>
		(function ($){
		    // Update the address preview when the location is changed
		    // Used on the dept-info page for selecting the office Location
		    $('#sudbury_location_id, #sudbury_address').change(function () {
		        refresh_address_preview();
		    })

		    if ($('#sudbury_location_id').length > 0) {
		        refresh_address_preview();
		    }

		    /*
		     * Sets the Address Preview box
		     */
		    function refresh_address_preview() {
		        var $location = $('#sudbury_location_id option:selected');
		        var name = $location.attr('data-name')
		        var address = $location.attr('data-address')
		        var town = $location.attr('data-town')
		        var state = $location.attr('data-state')
		        var zip = $location.attr('data-zip')
		        var annotation = $('#sudbury_address').val().replace(/\n/g, '<br>')

		        var address_full = name + '<br />' + address + '<br />' + town + ', ' + state + '<br />' + zip + '<br /><i>' + annotation + '</i>'
		        $('.sudbury_address_preview').html(address_full)
		    }
	    })(jQuery)
	    </script>
	</div>
<?php
}


