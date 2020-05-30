<?php

/**
 * A very generalized shorcode for displaying large lists of posts and allowing that list
 * to be filtered client side by Javascript using only the data that is presented in the table
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Filterable {

	var $excluded_cat_names = array(
		'Front Page News',
		'Department Page News',
		'Sticky (Stick to Top of List)',
		'Pinned Post',
		'Internal News',
		'Uncategorized'
	);

	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		$this->excluded_cat_names = apply_filters( 'filterable_excluded_cat_names', $this->excluded_cat_names );
		add_shortcode( 'filterable', array( &$this, 'shortcode' ) );

	}


	function shortcode( $atts = array(), $content = null ) {
		$defaults = array(
			'query_post_type'  => 'post',
			'fields'           => '',
			'headers'          => '',
			'title'            => '',
			'network'          => 'false',
			'corner_link_url'  => '',
			'corner_link_text' => '',
			'cache'            => 'true',
			'hide_guests'      => 'true',
			'filter_tax'       => '',
			'select_term'      => '',
			'mid_link'         => '',
			'mid_link_text'    => ''
		);

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		$atts = array_merge( $defaults, $atts );

		$allow_save = true;
		$reload     = false;

		if ( isset( $_REQUEST['category'] ) && $_REQUEST['category'] != 'all-categories' ) {
			$atts['select_term'] = sanitize_key( $_REQUEST['category'] );
			$reload              = true;
			$allow_save          = false;
		}
		if ( isset( $_REQUEST['reload'] ) ) {
			_sudbury_log( '[NOTICE] Cache Reload Request Received' );
			set_time_limit( 600 );

			// DO not try setting $atts['cache'] to false because that will muck up the $atts hash
			$reload = true;
		}


		$network = 'true' == $atts['network'];
		$labels  = get_post_type_object( $atts['query_post_type'] )->labels;

		$query = array();

		foreach ( $atts as $key => $value ) {
			if ( strstartswith( 'query_', $key ) ) {
				if ( '__CURRENTTIME__' == $value ) {
					$value = (string) current_time( 'timestamp' );
				} elseif ( '__TIME__' == $value ) {
					$value = (string) time();
				}
				$query[ substr( $key, 6 ) ] = html_entity_decode( $value );
			}
		}

		if ( $atts['select_term'] && $atts['filter_tax'] ) {
			if ( in_array( 'query_' . $atts['filter_tax'], array_keys( $atts ) ) ) {
				echo "Config Error: filterable shortcode should not use 'query_{$atts['filter_tax']}' and 'filter_tax={$atts['filter_tax']}' at the same time";
			} else {
				$cat_query_arg           = ( $atts['filter_tax'] == 'category' ? 'category_name' : $atts['filter_tax'] );
				$query[ $cat_query_arg ] = $atts['select_term'];
			}
		}

		if ( $atts['title'] ) {
			if ( $atts['title'] == 'inherit' ) {
				$atts['title'] = get_the_title();
			}
			?>

			<?php if ( $atts['filter_tax'] ) { ?>


				<form method="post" action="">
					<div class="input-group mb-3">
						<select id="categorySelect" name="category" class="form-control">
							<option value='all-categories' <?php selected( $atts['select_term'], '' ); ?>>All Categories</option>
							<?php switch_to_blog( 1 );
							$terms = get_terms( $atts['filter_tax'], array( 'hide_empty' => false ) );
							foreach ( $terms as $term ) { ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $atts['select_term'], $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
							<?php }
							restore_current_blog();
							?>
						</select>
						<div class="input-group-append">
							<input type="submit" value="Submit" class="btn btn-secondary">
						</div>
					</div>
				</form>
			<?php } ?>

			<?php
		}

		$fields    = array_map( 'trim', explode( ',', $atts['fields'] ) );
		$headers   = array_map( 'trim', explode( ',', $atts['headers'] ) );
		$colstyles = array_map( 'trim', explode( ',', $atts['colstyles'] ) );
		?>

		<div class="filter-form">
			<div class="input-group mb-3">
				<input id="filterer" type="text" name="phrase"
					   value="<?php echo esc_attr( isset( $_GET['filter'] ) ? $_GET['filter'] : '' ); ?>"
					   placeholder="Start typing to filter <?php echo strtolower( $labels->name ); ?>"
					   data-target=".filter-table tbody tr" autocomplete="off"
					   data-results-placeholder=".documents-showing" class="search-input form-control" aria-label="Filter <?php echo strtolower( $labels->name ); ?>" aria-describedby="basic-addon2">

				<div class="input-group-append">
					<input class="btn btn-primary search-button filter-button" type="submit" value="filter" />
				</div>
			</div>
		</div>


		<?php

		// If we want cached content THEN
		//    If the Cache File is missing OR it is time to reload the cache THEN
		//        If there is not already a cache reload happening THEN
		//            > Kick over the cache with a reload request
		//        If There is a cache file THEN
		//            > Render the cached file
		//        ELSE
		//			  > Render a message to come back
		//    ELSE
		//        > Render the cached results
		$transient_hash = wp_hash( serialize( $atts ) . '|' . get_the_ID() );
		$cache_file     = wp_upload_dir()['basedir'] . '/cache/filterable-' . wp_hash( serialize( $atts ) . '-' . get_the_ID() ) . '.part.html';
		$old_csv_files  = wp_upload_dir()['basedir'] . '/cache/' . sanitize_title( $atts['title'] ) . '_*_' . wp_hash( serialize( $atts ) . '-' . get_the_ID() ) . '.csv';
		$csv_file       = wp_upload_dir()['basedir'] . '/cache/' . sanitize_title( $atts['title'] ) . '_' . date( 'Y-m-d_H-i-s', current_time( 'timestamp' ) ) . '_' . wp_hash( serialize( $atts ) . '-' . get_the_ID() ) . '.csv';
		$csv_link       = wp_upload_dir()['baseurl'] . '/cache/' . sanitize_title( $atts['title'] ) . '_' . date( 'Y-m-d_H-i-s', current_time( 'timestamp' ) ) . '_' . wp_hash( serialize( $atts ) . '-' . get_the_ID() ) . '.csv';
		// We like to cache this shortcode and we do not want to reload the cache manually then lets look into pulling from the cache
		if ( 'true' == $atts['cache'] && ! $reload ) {
			// If the file is missing or the cache has expired, then lets kick off a reload
			if ( ! file_exists( $cache_file ) || false === get_transient( 'filterable-' . $transient_hash ) ) {
				// Lets just make sure that we are not trying to rebuild the cache right now
				if ( $started_job = ( ! get_transient( 'filterable-building-' . $transient_hash ) ) ) {
					$url = add_query_arg( array(
						'force-https' => 'off',
						'reload'      => 'true',
					), 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
					// Kick off an async request.  WP HTTP API is not capable of these kinds of requests.
					sudbury_non_blocking_http( $url );
					_sudbury_log( "[NOTICE] kicked off reload to $url" );
				}
				$job_status = ( $started_job == 1 ? 'running' : 'started' );
				// In the case that the file did exist, then lets show the outdated cache (this is the normal thing to do)
				if ( file_exists( $cache_file ) ) {
					return '<!-- cached --><!-- reloading -->' . file_get_contents( $cache_file );
				} else {
					// Well we are going to just show this message instead
					return "<p>Fetching Updated Content to Cache... Please Check back in 2 minutes</p><p><strong>Hash: </strong>$transient_hash</p><p><strong>Job Status:</strong>$job_status</p>";
				}
			} else {
				return '<!-- cached -->' . file_get_contents( $cache_file );
			}

		}
		set_transient( 'filterable-building-' . $transient_hash, true, 600 );

		ob_start();
		if ( $atts['mid_link'] && $atts['mid_link_text'] ) {
			echo '<p> <a href="' . $atts['mid_link'] . '" style="font-size: 11px;color: #3B619C;padding-right: 10px;"> ' . $atts['mid_link_text'] . '</a></p>';
		}

		if ( $network ) {
			$posts = network_query_posts( $query );
		} else {
			$posts = get_posts( $query );
		}

		$total = 0;
		$posts = array_map( function ( $obj ) {
			return (array) $obj;
		}, $posts );

		?>

		<div class="tablecap">
			<div class="cap"><?php echo esc_html( $atts['title'] ); ?>
				<?php if ( $atts['select_term'] ) : ?>
					: CATEGORY <?php echo esc_html( $atts['select_term'] ); ?>
				<?php endif; ?>

				<span class="pull-right">
				<?php if ( $atts['corner_link_url'] ) : ?>
					<a href="<?php echo esc_attr( $atts['corner_link_url'] ); ?>" class=""><?php echo esc_html( $atts['corner_link_text'] ); ?></a> &bull;
				<?php endif; ?>

					<?php if ( $atts['select_term'] ) : ?>
						<a href="<?php echo esc_url( $csv_link ); ?>" class="">
						<i class="fa fa-file-excel text-warning" style=""></i>
						Not available</a>
					<?php else: ?>
						<?php if ( 'true' === $atts['cache'] ) : ?>
							<a href="<?php echo esc_url( $csv_link ); ?>" class="">
							<i class="fa fa-file-excel text-success" style=""></i>
							Download CSV </a>
						<?php endif; ?>
					<?php endif; ?>
				</span>

			</div>
			<table cellspacing="0" style="display: table;" class="filter-table">
				<thead class="headers">
				<?php
				foreach ( $headers as $i => $header ) {
					echo '<th style="' . $colstyles[ $i ] . '" >' . $header . '</th>';
				} ?>
				</thead>
				<tbody>
				<?php
				$csv = array(
					array(
						'Department/Committee',
						'Post ID',
						'Title',
						'Status',
						'Published Date',
						'Modified Date',
						'Categories',
						'Link',
					)
				);
				foreach ( $posts as $post ) {

					if ( ! is_internal() && ! get_blog_details( $post['BLOG_ID'] )->public ) {
						continue;
					}
					switch_to_blog( $post['BLOG_ID'] );
					if ( sudbury_is_meeting_document( (object) $post ) ) {
						restore_current_blog();
						continue;
					}

					if ( sudbury_is_multi_post( $post['ID'] ) && ! sudbury_is_root_post( $post['ID'] ) && 'true' == $atts['hide_guests'] ) {
						restore_current_blog();
						continue;
					}

					if ( isset( $query['post_status'] ) ) {
						if ( ( is_array( $query['post_status'] ) && ! in_array( $post['post_status'], $query['post_status'] ) ) || $post['post_status'] != $query['post_status'] ) {
							restore_current_blog();
							continue;
						}
					}

					if ( is_utility() ) {
						restore_current_blog();
						continue;
					}
					// don't show post if its only category is Internal News
					$post_categories = get_the_terms( $post['ID'], 'category' );
					if ( is_array( $post_categories ) && ( count( $post_categories ) == 1 ) && ( $post_categories[0]->slug == 'internal-news' ) ) {
						restore_current_blog();
						continue;
					}

					echo '<tr class="content ' . $post['post_status'] . '">';
					foreach ( $fields as $field ) {
						if ( strstartswith( 'meta_', $field ) ) {

							$field_value = get_post_meta( $post['ID'], substr( $field, 5 ), true );

						} elseif ( strstartswith( 'terms_', $field ) ) {
							$field_value = $this->reduce_terms( get_the_terms( $post['ID'], substr( $field, 6 ) ) );
						} elseif ( 'post_title_link' == $field ) {

							$field_value = '<a href="' . get_permalink( $post['ID'] ) . '">' . $post['post_title'] . '</a>';

						} elseif ( 'blogname' == $field ) {
							$field_value = get_blog_option( $post['BLOG_ID'], 'blogname' );
						} elseif ( 'post_date' == $field ) {
							$field_value = mysql2date( 'l, F j, Y', $post[ $field ] );
						} else {
							$field_value = $post[ $field ];
						}
						echo '<td class="fields-' . esc_attr( $field ) . ' field-searchable">' . $field_value . ' </td>';
					}
					echo '</tr>';

					$taxes = array(
						'post'       => 'category',
						'faq'        => 'faq_categories',
						'attachment' => 'document_categories',
						'service'    => 'service_categories',
					);
					$csv[] = array(
						get_blog_option( $post['BLOG_ID'], 'blogname' ),
						$post['ID'],
						$post['post_title'],
						$post['post_status'],
						$post['post_date'],
						$post['post_modified'],
						$this->reduce_terms( get_the_terms( $post['ID'], $taxes[ $post['post_type'] ] ) ),
						get_permalink( $post['ID'] ),
					);

					restore_current_blog();
					$total ++;
				}
				if ( empty( $posts ) ) { ?>
					<tr>
						<td class="text-center" colspan="<?php echo count( $headers ); ?>">No <?php echo strtolower( $labels->name ); ?> matches the criteria
						<td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<div class="foot"></div>
		</div>
		<p class="post-count documents-count"> Showing
			<span class="post-showing documents-showing"><?php echo $total; ?></span> of
			<span class="post-total documents-total"><?php echo $total; ?></span> | Generated
			at <?php echo date( 'l, F j, Y \a\t g:ia', current_time( 'timestamp' ) ); ?>
			<?php if ( 'true' == $atts['cache'] ) : ?>
				<span class="hide-if-logged-out">| <a href="?reload">Reload</a></span>
			<?php endif; ?>
		</p>

		<div class="query-details debug">
			<ul>
				<?php foreach ( $query as $key => $value ) : ?>
					<li><b><?php esc_html_e( $key ); ?></b>: <?php esc_html_e( $value ); ?><br></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
		$html = ob_get_clean();

		// Cache this puppy
		if ( $allow_save && ( 'true' == $atts['cache'] || $reload ) ) {
			set_transient( 'filterable-' . $transient_hash, 1, 3600 * 24 );
			// For soft caching
			if ( file_put_contents( $cache_file, $html ) ) {
				_sudbury_log( '[success] Refreshed Cache for ' . $cache_file );
			} else {
				_sudbury_log( '[error] Putting Content in Cache File: ' . $cache_file );
			}
			$files = glob( $old_csv_files );
			foreach ( $files as $file ) {
				unlink( $file );
			}
			if ( file_put_contents( $csv_file, $this->array_to_csv( $csv ) ) ) {
				_sudbury_log( '[success] Saved new CSV File ' . $cache_file );

			} else {
				_sudbury_log( '[error] Putting Content in CSV File: ' . $cache_file );
			}
		}
		delete_transient( 'filterable-building-' . $transient_hash );

		return $html;
	}

	/**
	 * Reduces the List of terms to A List of Terms and their parents
	 *
	 * @param array $terms The List of Terms
	 *
	 * @return string The List of terms
	 */
	function reduce_terms( $terms ) {
		if ( ! is_array( $terms ) ) {
			return '';
		}
		$terms = array_filter( $terms, function ( $term ) {
			return ! in_array( $term->name, $this->excluded_cat_names );
		} );

		foreach ( $terms as $i => $term ) {
			foreach ( $terms as $j => $other_term ) {
				if ( $term->parent && $term->parent == $other_term->term_id ) {
					unset( $terms[ $j ] );
				}
			}
		}

		$rendered = array();
		$terms    = array_map( function ( $term ) use ( &$rendered ) {
			$parents  = array();
			$return   = sudbury_get_term_path( $term->parent, $term->taxonomy, false, ' - ', false, $parents ) . $term->name;
			$rendered = array_merge( $rendered, $parents );

			return $return;
		}, $terms );

		return implode( ',<br/>', $terms );
	}

	/**
	 * Returns the list of parents for a term
	 *
	 * @param        $id
	 * @param        $tax
	 * @param bool   $link
	 * @param string $separator
	 * @param bool   $nicename
	 * @param array  $visited
	 *
	 * @return mixed|null|string|WP_Error
	 */
	function get_term_parents( $id, $tax, $link = false, $separator = '/', $nicename = false, $visited = array() ) {
		$chain  = '';
		$parent = get_term( $id, $tax );
		if ( null === $parent ) {
			return '';
		}
		if ( is_wp_error( $parent ) ) {
			return $parent;
		}
		if ( $nicename ) {
			$name = $parent->slug;
		} else {
			$name = $parent->name;
		}

		if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
			$visited[] = $parent->parent;
			$chain     .= $this->get_term_parents( $parent->parent, $tax, $link, $separator, $nicename, $visited );
		}

		if ( $link ) {
			$chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '">' . $name . '</a>' . $separator;
		} else {
			$chain .= $name . $separator;
		}

		return $chain;
	}

	/**
	 * Converts a 2 dimensional array to an escaped CSV String
	 *
	 * @param $arr
	 *
	 * @return string
	 */
	function array_to_csv( $arr ) {

		return implode( "\n", array_map( function ( $row ) {
			return implode( ',', array_map( function ( $field ) {
				return '"' . str_replace( '"', '""', $field ) . '"';
			}, $row ) );
		}, $arr ) );
	}

	/**
	 * Just for fun, probably does not work
	 * @deprecated
	 *
	 * @param $str
	 *
	 * @return array
	 */
	function csv_to_array( $str ) {
		return array_map( function ( $row ) {
			return array_map( function ( $field ) {
				return str_replace( "\0", '"', $field );
			}, explode( '","', str_replace( '""', "\0", $row ) ) );
		}, explode( "\n", $str ) );
	}
}

new Sudbury_Filterable();
