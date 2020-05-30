<?php

/**
 * A shortcode that can display a generic list of all posts
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 * @deprecated See the filterable shortcode for this shortcode's replacement
 */
class All_Posts_Shortcode {

	var $post_type = 'post';

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_shortcode( 'all-news', array( &$this, 'news_shortcode' ) );
		add_shortcode( 'all-posts', array( &$this, 'news_shortcode' ) );
		add_shortcode( 'all-faqs', array( &$this, 'faqs_shortcode' ) );
		add_shortcode( 'all-documents', array( &$this, 'documents_shortcode' ) );
		add_shortcode( 'all-services', array( &$this, 'documents_shortcode' ) );
	}

	function news_shortcode( $atts, $content = '' ) {
		if ( ! $atts ) {
			$atts = array();
		}
		$atts = array_merge( array( 'post_type' => 'post' ), $atts );

		return $this->the_shortcode( $atts, $content );
	}

	function services_shortcode( $atts, $content = '' ) {
		if ( ! $atts ) {
			$atts = array();
		}
		$atts = array_merge( array( 'post_type' => 'service', 'orderby' => 'title', 'order' => 'ASC' ), $atts );

		return $this->the_shortcode( $atts, $content );
	}

	function documents_shortcode( $atts, $content = '' ) {
		if ( ! $atts ) {
			$atts = array();
		}

		$atts = array_merge( array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'search_form' => 'false',

		), $atts );

		return $this->the_shortcode( $atts, $content );
	}

	function faqs_shortcode( $atts, $content = '' ) {
		if ( ! $atts ) {
			$atts = array();
		}

		$atts = array_merge( array( 'post_type' => 'faq' ), $atts );

		return $this->the_shortcode( $atts, $content );
	}

	/**
	 * The Shortcode for the [all-news] shortcode.  The shortcode will show a List of all categories and then a search
	 * box to search for a News Article on the network.
	 *
	 * If a user clicks on a category link then they will be sent to ?category=<category name> which will display all the
	 * posts on the network this that category.
	 *
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	function the_shortcode( $atts, $content = '' ) {
		ob_start();

		if ( ! $atts ) {
			$atts = array();
		}

		$atts = array_merge( array(
			'post_type'   => 'post',
			'sort'        => 'post_title',
			'search_form' => 'false',
			'post_status' => 'publish',
			'cat_list'    => ''
		), $atts );

		$this->post_type = $atts['post_type'];
		$post_object     = get_post_type_object( $this->post_type );

		/*
		 * Can order by title or date
		 */
		$orderby = $atts['sort'];

		$cat = false;

		if ( isset( $_REQUEST['category'] ) ) {
			// sanitize the user's input
			$cat = preg_replace( '/[^a-zA-Z1-9 _-]/', '', $_REQUEST['category'] );
		}


		if ( $cat ) {
			echo '<hr>';
		}

		$taxes = array(
			'post'       => 'category',
			'faq'        => 'faq_categories',
			'attachment' => 'document_categories',
			'service'    => 'service_categories',
		);

		if ( 'true' == $atts['search_form'] ) {
			echo "<span>Search all all our {$post_object->labels->name} here " . ( $cat || ! isset( $taxes[ $this->post_type ] ) ? "" : "or click on one of the categories below" ) . "</span>";
			echo $this->get_search_form();
			echo '<div class="space"></div>';
		}


		if ( isset( $taxes[ $this->post_type ] ) ) {
			$tax   = $taxes[ $this->post_type ];
			$query = $atts; // Give $atts full control over the query
			if ( 'attachments' == $this->post_type ) {
				$query['post_mime_type'] = 'application*';
			}
			if ( $cat ) {
				$query[ $tax ] = $cat;
			}

			$posts = network_query_posts( $query );
			echo '<div class="articles">';
			if ( $posts ) {
				$callback = function ( $query ) use ( &$posts ) {
					return $posts;
				};
				add_filter( 'posts_pre_query', $callback );

				$temp_query = new WP_Query(array('nothing' => true));
				while ( $temp_query->have_posts() ) { 
					$temp_query->the_post();
					global $post;
					if ( sudbury_is_guest_post( get_the_ID() ) ) {
						continue;
					}

					// Only If we are listing a single category then output the summary of the content
					if ( $cat || ( 'false' == $atts['search_form'] && '' == $atts['cat_list'] ) ) {
						get_template_part( 'content', $this->post_type );
					}

				}
				wp_reset_postdata();
				remove_filter( 'posts_pre_query', $callback );

				$post = $temppost;
			} else {
				if ( isset( $atts['not_found'] ) ) {
					echo $atts['not_found'];
				} elseif ( $cat ) {
					echo "<p><b>Sorry, No {$post_object->labels->name} Found in this Category</b></p>";
				}
			}


			if ( ! $cat && ( 'true' == $atts['search_form'] || 'true' == $atts['cat_list'] ) ) {
				// Echo the List of Categories Ordered by $sorts
				echo $this->get_categories_list( $tax, $this->post_type, $atts );
			}
			echo '</div>';
		}
		network_reset_query();

		return ob_get_clean();
	}

	/**
	 * Gets the list of categories in HTML
	 *
	 * @param string $tax       The Taxonomy to list categories for
	 * @param string $post_type The Post Type that this list is for
	 *
	 * @return string The HTML Showing the List
	 */
	function get_categories_list( $tax, $post_type, $atts = array() ) {
		// Display a List of Categories

		$html      = '<h3>' . ( 'true' == $atts['search_form'] ? 'Or' : '' ) . ' Select a Category</h3><div class="categories-container">';
		$list_args = array(
			'taxonomy'     => $tax,
			'orderby'      => 'name',
			'show_count'   => false,
			'hide_empty'   => false,
			'hierarchical' => true,
			'echo'         => false,
			'title_li'     => '',
			'walker'       => new All_Tax_Walker()
		);

		// Get the List of Categories
		$list = wp_list_categories( $list_args );

		// Break it Up by item... be careful for hierarchical categories
		$list = explode( '<br />', $list );

		$n_columns = 2;
		$columns   = array_chunk( $list, ceil( count( $list ) / $n_columns ) );


		$html .= "<h3></h3>";
		$html .= '<hr>';

		// Generate the List of Columns
		foreach ( $columns as $column => $items ) {

			$html .= '<div class="categories-list"><ul>';

			$html .= implode( '', $items );

			$html .= '</ul></div>';
		}

		$html .= '<div class="clear"></div>';
		$html .= '</div>';


		return $html;
	}

	/**
	 * Returns a Search form to search network posts
	 * @return string
	 */
	function get_search_form() {
		if ( function_exists( 'global_site_search_form' ) ) {
			ob_start();

			$GLOBALS['global_site_search_post_type'] = $this->post_type;
			global_site_search_form();

			return ob_get_clean();
		} else {
			return 'The Global Site Search Plugin is not enabled... we cannot search the network at this time';

		}
		//return '<form action="" method="GET"> <input type="text" name="s" id="faq-search" class="all-search" placeholder="Search FAQs" /></form>';
	}
}

new All_Posts_Shortcode();


class All_Tax_Walker extends Walker_Category {
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		/*
		 * COPIED FROM PARENT FUNCTION... Need to replace URLS
		 */

		// For some reason wp_list_categories seems to think that it is necessary to define every argument even if it destroys things like $depth
		// Grrr WordPress Core
		unset( $args['depth'] );

		extract( $args );


		$cat_name = esc_attr( $category->name );

		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters( 'list_cats', $cat_name, $category );
		$link     = '<a href="?category=' . esc_attr( $category->slug ) . '" ';
		if ( $use_desc_for_title == 0 || empty( $category->description ) ) {
			$link .= 'title="' . esc_attr( sprintf( __( 'View all posts filed under %s' ), $cat_name ) ) . '"';
		} else {
			/**
			 * Filter the category description for display.
			 *
			 * @since 1.2.0
			 *
			 * @param string $description Category description.
			 * @param object $category    Category object.
			 */
			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
		}

		$link .= '>';
		$link .= $cat_name . '</a>';
		if ( ! empty( $feed_image ) || ! empty( $feed ) ) {
			$link .= ' ';

			if ( empty( $feed_image ) ) {
				$link .= '(';
			}

			$link .= '<a href="?category=' . esc_attr( $category->slug ) . '">';

			if ( empty( $feed ) ) {
				$alt = ' alt="' . sprintf( __( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
			} else {
				$title = ' title="' . $feed . '"';
				$alt   = ' alt="' . $feed . '"';
				$name  = $feed;
				$link .= $title;
			}

			$link .= '>';

			if ( empty( $feed_image ) ) {
				$link .= $name;
			} else {
				$link .= "<img src='$feed_image'$alt$title" . ' />';
			}

			$link .= '</a>';

			if ( empty( $feed_image ) ) {
				$link .= ')';
			}
		}
		if ( ! empty( $show_count ) ) {
			$link .= ' (' . number_format_i18n( $category->count ) . ')';
		}
		if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( ! empty( $current_category ) ) {
				$_current_category = get_term( $current_category, $category->taxonomy );
				if ( $category->term_id == $current_category ) {
					$class .= ' current-cat';
				} elseif ( $category->term_id == $_current_category->parent ) {
					$class .= ' current-cat-parent';
				}
			}
			$output .= ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}

		if ( 0 == $depth ) {

			$output .= '<br />';
		} // Adding a <br /> tag to be able to do an explode() on later
	}
}