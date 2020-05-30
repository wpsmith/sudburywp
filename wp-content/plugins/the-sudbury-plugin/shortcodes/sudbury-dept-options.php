<?php

/**
 * Shortcodes for options like department phone and emails
 * Shortcode to list board members for a committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin
 */
class Generic_Options_Shortcode {

	/**
	 * Constructor (registers actions and filters)
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Registers the shortcode
	 */
	function init() {
		add_shortcode( 'email', array( &$this, 'email_shortcode' ) );
		add_shortcode( 'phone', array( &$this, 'phone_shortcode' ) );
		add_shortcode( 'fax', array( &$this, 'fax_shortcode' ) );
		add_shortcode( 'parent', array( &$this, 'parent_shortcode' ) );
		add_shortcode( 'children', array( &$this, 'children_shortcode' ) );
		add_shortcode( 'siteurl', array( &$this, 'siteurl_shortcode' ) );
		add_shortcode( 'title', array( &$this, 'title_shortcode' ) );
		add_shortcode( 'tagline', array( &$this, 'tagline_shortcode' ) );
		add_shortcode( 'home', array( &$this, 'home_shortcode' ) );
	}

	/**
	 * Displays the current Department or Committee's Email Address and defaults to webmaster@sudbury.ma.us if not found
	 *
	 * @param array $atts An array of attributes for the shortcode.  set link=false
	 * @param null  $content
	 *
	 * @return mixed|string|void
	 */
	function email_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array( 'link' => 'true' ), $atts );

		$email = get_option( 'sudbury_email', 'webmaster@sudbury.ma.us' );

		$email = sanitize_email( $email ); // good for both esc_atr and esc_html
		
		if ( 'true' == $atts['link'] ) {
			return sprintf( '<a href="mailto:%s">%s</a>', $email, $email );
		} else {
			return $email;
		}
	}

	function phone_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array(), $atts );

		$phone = get_option( 'sudbury_telephone', '' );

		return esc_html( $phone );
	}

	function fax_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array(), $atts );

		$fax = get_option( 'sudbury_fax', '' );

		return esc_html( $fax );
	}

	function parent_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array( 'field' => 'longname', 'link' => true ), $atts );

		$parent = get_option( 'sudbury_parent' );
		if ( ! $parent ) {
			return '';
		}

		$blog = get_blog_details( $parent, true );
		$text = 'Parent ' . sudbury_get_site_type();

		if ( 'shortname' == $atts['field'] ) {
			$text = sudbury_get_blog_slug( $parent );
		} elseif ( 'longname' == $atts['field'] ) {
			$text = $blog->blogname;
		} elseif ( 'parents' == $atts['field'] ) {
			return sudbury_the_relationship_path( get_current_blog_id(), array(
				'echo'  => false,
				'links' => $atts['link']
			) );
		}

		if ( $atts['link'] ) {
			return sprintf( '<a href="%s">%s</a>', $blog['path'], esc_html( $text ) );
		} else {
			return esc_html( $text );
		}
	}

	function children_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array( 'field' => 'longname', 'link' => true, 'delimiter' => '<br />' ), $atts );

		$children = get_option( 'sudbury_children' );
		if ( ! $children ) {
			return '';
		}

		foreach ( $children as $child ) {
			$blog = get_blog_details( $child, true );

			if ( 'shortname' == $atts['field'] ) {
				$text[ $child ] = sudbury_get_blog_slug( $child );
			} elseif ( 'longname' == $atts['field'] ) {
				$text[ $child ] = $blog->blogname;
			}
		}

		// Combine it all into a list
		return array_reduce( array_keys( $text ), function ( $list, $key ) use ( &$text, &$atts ) {
			if ( $atts['link'] ) {
				$list .= sprintf( '<a href="%s">%s</a>%s', esc_url( $key ), esc_html( $text[ $key ] ), $atts['delimiter'] );
			} else {
				$list .= esc_html( $text ) . $atts['delimiter'];
			}

			return $list;
		} );
	}


	function siteurl_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array( 'link' => true ), $atts );

		$siteurl = get_bloginfo( 'url' );
		if ( $atts['link'] ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( $siteurl ), esc_html( $siteurl ) );
		} else {
			return esc_html( $siteurl );
		}
	}

	function tagline_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array( 'link' => false ), $atts );

		$tagline = get_bloginfo( 'description' );
		if ( $atts['link'] ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( get_bloginfo( 'url' ) ), esc_html( $tagline ) );
		} else {
			return esc_html( $tagline );
		}
	}

	function title_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array( 'link' => true ), $atts );

		$title = get_bloginfo( 'name' );
		if ( $atts['link'] ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( get_bloginfo( 'url' ) ), esc_html( $title ) );
		} else {
			return esc_html( $title );
		}
	}

	function home_shortcode( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array(), $atts );

		return sprintf( '<a href="%s">Back to %s</a>', esc_url( get_bloginfo( 'url' ) ), esc_html( get_bloginfo( 'name' ) ) );

	}
}

new Generic_Options_Shortcode();
