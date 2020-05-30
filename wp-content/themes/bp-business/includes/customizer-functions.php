<?php


/*
 * Custom control class
 *
 * Add description on control
 * */
if ( class_exists('WP_Customize_Control') ) {
class WPMUDEV_Customize_Control extends WP_Customize_Control {

	public $description = '';

	protected function render_content() {
		switch( $this->type ) {
			default:
				return parent::render_content();
			case 'text':
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php if ( isset($this->description) && !empty($this->description) ): ?>
					<span class="customize-control-description"><?php echo $this->description ?></span>
					<?php endif ?>
					<input type="text" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
				</label>
				<?php
				break;
			case 'checkbox':
				?>
				<label>
					<input type="checkbox" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); checked( $this->value() ); ?> />
					<?php echo esc_html( $this->label ); ?>
				</label>
				<?php if ( isset($this->description) && !empty($this->description) ): ?>
				<span class="customize-control-description"><?php echo $this->description ?></span>
				<?php endif ?>
				<?php
				break;
			case 'radio':
				if ( empty( $this->choices ) )
					return;

				$name = '_customize-radio-' . $this->id;

				?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( isset($this->description) && !empty($this->description) ): ?>
				<span class="customize-control-description"><?php echo $this->description ?></span>
				<?php endif ?>
				<?php
				foreach ( $this->choices as $value => $label ) :
					?>
					<label>
						<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
						<?php echo esc_html( $label ); ?><br/>
					</label>
					<?php
				endforeach;
				break;
			case 'custom-radio':
				if ( empty( $this->choices ) )
					return;

				$name = '_customize-radio-' . $this->id;

				?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( isset($this->description) && !empty($this->description) ): ?>
				<span class="customize-control-description"><?php echo $this->description ?></span>
				<?php endif ?>
				<?php
				foreach ( $this->choices as $value => $label ) :
					$screenshot_img = substr($value,0,-4);
					?>
					<label>
						<div class="theme-img">
							<img src="<?php echo get_template_directory_uri(); ?>/_inc/preset-styles/images/<?php echo $screenshot_img . '.png'; ?>" alt="<?php echo $screenshot_img ?>" />
						</div>
						<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
						<?php echo esc_html( $label ); ?><br/>
					</label>
					<?php
				endforeach;
				break;
			case 'select':
				if ( empty( $this->choices ) )
					return;

				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php if ( isset($this->description) && !empty($this->description) ): ?>
					<span class="customize-control-description"><?php echo $this->description ?></span>
					<?php endif ?>
					<select <?php $this->link(); ?>>
						<?php
						foreach ( $this->choices as $value => $label )
							echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
						?>
					</select>
				</label>
				<?php
				break;
			// Handle textarea
			case 'textarea':
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php if ( isset($this->description) && !empty($this->description) ): ?>
					<span class="customize-control-description"><?php echo $this->description ?></span>
					<?php endif ?>
					<textarea rows="10" cols="40" <?php $this->link(); ?>><?php echo esc_attr( $this->value() ); ?></textarea>
				</label>
				<?php
				break;
		}
	}

}
}

if ( class_exists('WP_Customize_Color_Control') ) {
class WPMUDEV_Customize_Color_Control extends WP_Customize_Color_Control {

	public $description = '';

	public function render_content() {
		$this_default = $this->setting->default;
		$default_attr = '';
		if ( $this_default ) {
			if ( false === strpos( $this_default, '#' ) )
				$this_default = '#' . $this_default;
			$default_attr = ' data-default-color="' . esc_attr( $this_default ) . '"';
		}
		// The input's value gets set by JS. Don't fill it.
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( isset($this->description) && !empty($this->description) ): ?>
			<span class="customize-control-description"><?php echo $this->description ?></span>
			<?php endif ?>
			<div class="customize-control-content">
				<input class="color-picker-hex" type="text" maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value' ); ?>"<?php echo $default_attr ?> />
			</div>
		</label>
		<?php
	}
}
}

if ( class_exists('WP_Customize_Image_Control') ) {

class WPMUDEV_Customize_Image_Control extends WP_Customize_Image_Control {

	public $description = '';

	public function render_content() {
		$src = $this->value();
		if ( isset( $this->get_url ) )
			$src = call_user_func( $this->get_url, $src );

		?>
		<div class="customize-image-picker">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( isset($this->description) && !empty($this->description) ): ?>
			<span class="customize-control-description"><?php echo $this->description ?></span>
			<?php endif ?>

			<div class="customize-control-content">
				<div class="dropdown preview-thumbnail">
					<div class="dropdown-content">
						<?php if ( empty( $src ) ): ?>
							<img style="display:none;" />
						<?php else: ?>
							<img src="<?php echo esc_url( set_url_scheme( $src ) ); ?>" />
						<?php endif; ?>
						<div class="dropdown-status"></div>
					</div>
					<div class="dropdown-arrow"></div>
				</div>
			</div>

			<div class="library">
				<ul>
					<?php foreach ( $this->tabs as $id => $tab ): ?>
						<li data-customize-tab='<?php echo esc_attr( $id ); ?>'>
							<?php echo esc_html( $tab['label'] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php foreach ( $this->tabs as $id => $tab ): ?>
					<div class="library-content" data-customize-tab='<?php echo esc_attr( $id ); ?>'>
						<?php call_user_func( $tab['callback'] ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="actions">
				<a href="#" class="remove"><?php _e( 'Remove Image' ); ?></a>
			</div>
		</div>
		<?php
	}
}
}


function bpbusiness_customize_register( $wp_customize ) {
	global $options, $options2, $options3, $shortname, $shortprefix;
	$options_list = array_merge($options, $options2, $options3);
	$sections = array(
		array(
			'section' => 'homepage',
			'title' => __("Front page settings", TEMPLATE_DOMAIN),
			'priority' => 30
		),array(
			'section' => 'header',
			'title' => __("Header Settings", TEMPLATE_DOMAIN),
			'priority' => 31
		),array(
			'section' => 'messages',
			'title' => __("Member and non-member message settings", TEMPLATE_DOMAIN),
			'priority' => 32
		),array(
			'section' => 'members',
			'title' => __("Community page settings", TEMPLATE_DOMAIN),
			'priority' => 33
		),array(
			'section' => 'rss',
			'title' => __("RSS feed settings", TEMPLATE_DOMAIN),
			'priority' => 34
		),array(
			'section' => 'presetstyle',
			'title' => __("Style Select:", TEMPLATE_DOMAIN),
			'priority' => 35
		),array(
			'section' => 'headers',
			'title' => __("Text styling", TEMPLATE_DOMAIN),
			'priority' => 36
		),array(
			'section' => 'links',
			'title' => __("Link styling", TEMPLATE_DOMAIN),
			'priority' => 37
		),array(
			'section' => 'navlinks',
			'title' => __("Navigation styling", TEMPLATE_DOMAIN),
			'priority' => 38
		),array(
			'section' => 'layout',
			'title' => __("Layout styling", TEMPLATE_DOMAIN),
			'priority' => 39
		),array(
			'section' => 'form',
			'title' => __("Form styling", TEMPLATE_DOMAIN),
			'priority' => 40
		)

	);
	// Add sections
	foreach ( $sections as $section ) {
		$wp_customize->add_section( $shortname . $shortprefix . $section['section'], array(
			'title' => $section['title'],
			'priority' => $section['priority']
		) );
	}
	// Add settings and controls
	$opt_section = '';
	foreach ( $options_list as $o => $option ) {
		if ( ! bpbusiness_option_in_customize($option) )
			continue;
		$transport = 'postMessage';
		$wp_customize->add_setting( $option['id'], array(
			'default' => ( isset($option['std']) ? $option['std'] : '' ),
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => $transport
		) );
		$control_param = array(
			'label' => strip_tags($option['name']),
			'description' => ( isset($option['description']) && !empty($option['description']) ? $option['description'] : '' ),
			'section' => $shortname . $shortprefix . $option['inblock'],
			'settings' => $option['id'],
			'priority' => $o // make sure we have the same order as theme options :)
		);
		if ( $option['type'] == 'color' || $option['type'] == 'colorpicker' ) {
			$wp_customize->add_control( new WPMUDEV_Customize_Color_Control( $wp_customize, $option['id'].'_control', $control_param ) );
		}
		else if ( $option['type'] == 'image' ) {
			$control_param['type'] = $option['type'];
			$wp_customize->add_control( new WPMUDEV_Customize_Image_Control( $wp_customize, $option['id'].'_control', $control_param) );
		}
		else if ( $option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'checkbox' ) {
			$control_param['type'] = $option['type'];
			$wp_customize->add_control( new WPMUDEV_Customize_Control( $wp_customize, $option['id'].'_control', $control_param) );
		}
		else if ( $option['type'] == 'custom-radio' ) {
			$control_param['type'] ='custom-radio';
			// @TODO choices might get removed in future
			$choices = array();
			foreach ( $option['options'] as $choice )
				$choices[$choice] = $choice;
			$control_param['choices'] = $choices;
			$wp_customize->add_control( new WPMUDEV_Customize_Control( $wp_customize, $option['id'].'_control', $control_param) );
		}
		else if ( $option['type'] == 'select' || $option['type'] == 'select-preview' ) {
			$control_param['type'] = 'select';
			// @TODO choices might get removed in future
			$choices = array();
			foreach ( $option['options'] as $choice )
				$choices[$choice] = $choice;
			$control_param['choices'] = $choices;
			$wp_customize->add_control( new WPMUDEV_Customize_Control( $wp_customize, $option['id'].'_control', $control_param) );
		}
	}

	// Support Wordpress custom background
	$wp_customize->get_setting('background_color')->transport = 'postMessage';
	$wp_customize->get_setting('background_image')->transport = 'postMessage';
	$wp_customize->get_setting('background_repeat')->transport = 'postMessage';
	$wp_customize->get_setting('background_position_x')->transport = 'postMessage';
	$wp_customize->get_setting('background_attachment')->transport = 'postMessage';
	$wp_customize->get_setting('header_image')->transport = 'postMessage';
	$wp_customize->get_setting('blogname')->transport = 'postMessage';
	$wp_customize->get_setting('blogdescription')->transport = 'postMessage';
}
add_action('customize_register', 'bpbusiness_customize_register');

function bpbusiness_customize_preview() {
	global $options, $shortname, $shortprefix;
	?>
	<div id="theme-customizer-css"></div>

	<script type="text/javascript">
		var theme_prefix = "<?php echo $shortname . $shortprefix ?>";
		var theme_url = "<?php echo get_template_directory_uri() ?>";
	</script>
	<?php
}

function bpbusiness_customize_init() {
	global $theme_version;
	add_action('wp_footer', 'bpbusiness_customize_preview', 21);
	wp_enqueue_script( 'bpbusiness-theme-customizer', get_template_directory_uri() . '/_inc/js/theme-customizer.js', array( 'customize-preview' ), $theme_version );
	wp_enqueue_script( 'bpbusiness-theme-customizer-options', get_template_directory_uri() . '/_inc/js/theme-customizer-options.js', array( 'customize-preview' ), $theme_version );
}
add_action( 'customize_preview_init', 'bpbusiness_customize_init' );

// Add additional styling to better fit on Customizer options
function  bpbusiness_customize_controls_footer() {
	global $options, $shortname, $shortprefix;
	?>
	<style type="text/css">
		.customize-control-title { line-height: 18px; padding: 2px 0; }
		.customize-control-description { font-size: 11px; color: #666; margin: 0 0 3px; display: block; }
		.customize-control input[type="text"], .customize-control textarea { width: 98%; line-height: 18px; margin: 0; }
	</style>
	<?php
}
add_action('customize_controls_print_footer_scripts', 'bpbusiness_customize_controls_footer');



function bpbusiness_option_in_customize( $option ){
	global $shortname, $shortprefix, $bp_existed;
	$ids = array(
		//$shortname . $shortprefix  . "bp_bar",
		//$shortname . $shortprefix  . "feature_id",
		//$shortname . $shortprefix  . "feature_image_size",
		//$shortname . $shortprefix  . "feature_navigation",
		//$shortname . $shortprefix  . "feature_link",
		//$shortname . $shortprefix  . "feature_link_title",
		//$shortname . $shortprefix  . "news_cat",
		//$shortname . $shortprefix  . "news_image_size",
		//$shortname . $shortprefix  . "homesidebar",
		//$shortname . $shortprefix  . "header_image",
		//$shortname . $shortprefix  . "header_logo",
		//$shortname . $shortprefix  . "header_image_square",
		//$shortname . $shortprefix  . "header_logo_square",
		//$shortname . $shortprefix  . "header_title",
		//$shortname . $shortprefix  . "header_description_on",
		//$shortname . $shortprefix  . "header_description",
		//$shortname . $shortprefix  . "join_message",
		//$shortname . $shortprefix  . "members_message",
		//$shortname . $shortprefix  . "community_id",
		//$shortname . $shortprefix  . "community_navigation",
		//$shortname . $shortprefix  . "community_link",
		//$shortname . $shortprefix  . "community_link_title",
		//$shortname . $shortprefix  . "rss_link",
		//$shortname . $shortprefix  . "custom_style",
		$shortname . $shortprefix  . "body_font",
		$shortname . $shortprefix  . "headline_font",
		$shortname . $shortprefix  . "h1_colour",
		$shortname . $shortprefix  . "h2_colour",
		$shortname . $shortprefix  . "h3_colour",
		$shortname . $shortprefix  . "h4_colour",
		$shortname . $shortprefix  . "latest_header_colour",
		$shortname . $shortprefix  . "text_colour",
		$shortname . $shortprefix  . "latest_text_colour",
		$shortname . $shortprefix  . "blockquote_colour",
		$shortname . $shortprefix  . "list_colour",
		$shortname . $shortprefix  . "link_colour",
		$shortname . $shortprefix  . "link_hover_colour",
		$shortname . $shortprefix  . "footer_link_colour",
		$shortname . $shortprefix  . "navigation_link_colour",
		$shortname . $shortprefix  . "navigation_hover_colour",
		$shortname . $shortprefix  . "navigation_background_hover_colour",
		$shortname . $shortprefix  . "user_link_colour",
		$shortname . $shortprefix  . "user_background_link_colour",
		$shortname . $shortprefix  . "user_link_hover_colour",
		$shortname . $shortprefix  . "user_background_hover_colour",
		$shortname . $shortprefix  . "user_link_border_colour",
		$shortname . $shortprefix  . "latest_colour",
		$shortname . $shortprefix  . "header_background_image",
		$shortname . $shortprefix  . "header_image_repeat",
		$shortname . $shortprefix  . "header_background_colour",
		$shortname . $shortprefix  . "navigation_background_image",
		$shortname . $shortprefix  . "navigation_image_repeat",
		$shortname . $shortprefix  . "navigation_background_colour",
		$shortname . $shortprefix  . "featured_colour",
		$shortname . $shortprefix  . "content_colour",
		$shortname . $shortprefix  . "images_colour",
		$shortname . $shortprefix  . "latest_background_image",
		$shortname . $shortprefix  . "latest_image_repeat",
		$shortname . $shortprefix  . "latest_background_colour",
		$shortname . $shortprefix  . "latest_border_colour",
		$shortname . $shortprefix  . "footer_background_colour",
		$shortname . $shortprefix  . "footer_border_colour",
		$shortname . $shortprefix  . "information_background_colour",
		$shortname . $shortprefix  . "information_border_colour",
		$shortname . $shortprefix  . "box_background_colour",
		$shortname . $shortprefix  . "box_border_colour",
		$shortname . $shortprefix  . "login_background_colour",
		$shortname . $shortprefix  . "login_border_colour",
		$shortname . $shortprefix  . "main_border_colour",
		$shortname . $shortprefix  . "button_colour",
		$shortname . $shortprefix  . "button_text",
		$shortname . $shortprefix  . "dark_button_bgcolour",
		$shortname . $shortprefix  . "dark_button_colour",
		$shortname . $shortprefix  . "form_background_colour",
		$shortname . $shortprefix  . "form_text_colour",
		$shortname . $shortprefix  . "label_text_colour",
		$shortname . $shortprefix  . "search_background_colour",
		$shortname . $shortprefix  . "search_text_colour",
		$shortname . $shortprefix  . "block_background_colour",
		$shortname . $shortprefix  . "block_border_colour",
		$shortname . $shortprefix  . "comment_background_colour",
		$shortname . $shortprefix  . "comment_border_colour",
		$shortname . $shortprefix  . "comment_list_border_colour",
	);
	if ( $option['inblock'] == 'admin' && $bp_existed == 'false' )
		return false;
	if ( in_array( $option['id'], $ids ) )
		return true;
	return false;
}

?>