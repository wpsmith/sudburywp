<style type="text/css">
.metabox_label_column {
	text-align:left;
}
.wp-media-buttons {
	display:none;
}
</style>
<table> 
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="slide_color_scheme_class">Slide Color Scheme</label>
        </th>
        <td>
            <input type="radio" name="slide_color_scheme_class" value="scheme-blue" <?php if (@get_post_meta($post->ID, 'slide_color_scheme_class', true)=='scheme-blue') {echo 'checked="checked"';} ?> /> Blue<br>
			<input type="radio" name="slide_color_scheme_class" value="scheme-green" <?php if (@get_post_meta($post->ID, 'slide_color_scheme_class', true)=='scheme-green') {echo 'checked="checked"';} ?> /> Green<br>
			<input type="radio" name="slide_color_scheme_class" value="scheme-yellow" <?php if (@get_post_meta($post->ID, 'slide_color_scheme_class', true)=='scheme-yellow') {echo 'checked="checked"';} ?> /> Yellow
        </td>
    <tr>
	<tr valign="top">
        <th class="metabox_label_column">
            <label for="slide_display_order">Display Order</label>
        </th>
        <td>
            <input type="text" id="slide_display_order" name="slide_display_order" value="<?php echo @get_post_meta($post->ID, 'slide_display_order', true); ?>" />
        </td>
    <tr>
</table>