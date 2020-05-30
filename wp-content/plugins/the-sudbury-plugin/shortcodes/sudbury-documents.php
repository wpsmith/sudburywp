<?php

/**
 * A shortcode to show a list of buildings, their addresses, and contact info
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Documents_Shortcode {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'sudbury-documents', array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts = array(), $content = null ) {
		$defaults = array();
		if ( ! $atts ) {
			$atts = array();
		}

		$atts = array_merge( $defaults, $atts );

		if ( isset( $_REQUEST['category'] ) ) {
			$active_cat = sanitize_key( $_REQUEST['category'] );
		} else {
			$active_cat = "all-categories";
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$orderby = sanitize_key( $_REQUEST['orderby'] );
		} else {
			$orderby = 'post_date';
		}

		$orders = array(
			'post_date'  => 'DESC',
			'post_title' => 'ASC',
		);

		$order = $orders[ $orderby ];

		$query = array(
			'post_mime_type' => 'application*',
			'post_type'      => 'attachment',
			'posts_per_page' => - 1,
			'orderby'        => $orderby,
			'order'          => $order,
			'post_status'    => 'inherit',
			'meta_query'     => array(
				// 'relation' => 'OR',
				//array(
				//'key'     => 'sudbury_start_date_timestamp',
				//'value'   => time(),
				//'compare' => '<',
				//),
				//array(
				//	'key'     => 'sudbury_start_date_timestamp',
				//	'value'   => 'NULL',
				//	'compare' => '==',
				//),

			),
		);

		if ( ! ( $show_expired = ( isset( $_REQUEST['include_archived'] ) || is_archived( get_current_blog_id() ) ) ) ) {
			$extra_query['key']     = 'sudbury_end_date_timestamp';
			$extra_query['value']   = time();
			$extra_query['compare'] = '>';
			$query['meta_query'][]  = $extra_query;
		}

		$total_documents = 0;

		$meta          = sudbury_get_relationship_meta();
		$related_blogs = array();
		foreach ( $meta as $bid => $relations ) {
			foreach ( $relations as $relation ) {
				if ( isset( $relation['retrieve_documents'] ) && $relation['retrieve_documents'] ) {
					$related_blogs[] = $bid;
					continue;
				}
			}
		}
		$related_blogs   = array_unique( $related_blogs );
		$related_blogs[] = get_current_blog_id();
		$page_url        = $_SERVER['REQUEST_URI'];

		$documents = array();
		if ( $active_cat != 'all-categories' ) {
			$query['tax_query'] = array(
				array(
					'taxonomy' => 'document_categories',
					'field'    => 'slug',
					'terms'    => $active_cat,
				),
			);
		}

		ob_start();
		// End Setup
		foreach ( $related_blogs as $blog ) : ?>
			<?php switch_to_blog( $blog ); ?>
			<?php $documents_query = new WP_Query( $query ); ?>
			<?php if ( $documents_query->have_posts() ) : ?>
				<?php while ( $documents_query->have_posts() ) :
					$documents_query->the_post();
					if ( get_post_meta( get_the_ID(), 'sudbury_meeting_attachment', true ) || get_post_meta( get_the_ID(), 'sudbury_meeting_document', true ) || get_post_meta( get_the_id(), '_sudbury_origional_title', true ) === 'Meeting Document' ) {
						continue;
					}

					ob_start();

					$total_documents ++;
					?>
					<tr class="document<?php if ( sudbury_is_post_archived( get_the_ID() ) ) : ?> archived<?php endif; ?>">
						<td class="coltitle">
							<a href="<?php echo wp_get_attachment_url( get_the_ID() ); ?>"><?php the_title(); ?></a>
							<br />
							<span style="font-size: 10px;line-height: 1.5">
								<?php echo get_the_content(); ?>
								&nbsp;
							</span>
						</td>
						<td>
							<?php echo get_the_date(); ?>
						</td>
						<td style="vertical-align: top;"><?php $terms = get_the_terms( get_the_ID(), 'document_categories' ); ?>
							<?php if ( is_array( $terms ) && ! empty( $terms ) ) : ?>
								<?php echo implode( '<br />', sudbury_get_term_paths( $terms ) ); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php $documents[ get_post()->$orderby . random_string( 4 ) ] = ob_get_clean(); ?>
				<?php endwhile; ?>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
			<?php restore_current_blog(); ?>
		<?php endforeach; ?>

		<?php if ( $documents ) : ?>

			<div class="tablecap documents-table">
				<h4>
					<?php bloginfo( 'name' ); ?> -
					<?php if ( ! $show_expired ) : ?>
						All Active Documents <?php if ( $active_cat != "all-categories" ) { ?> from CATEGORY: <?php echo $active_cat;
						} ?>
					<?php else : ?>
						All Documents <?php if ( $active_cat != "all-categories" ) { ?> from CATEGORY: <?php echo $active_cat;
						} ?> (
						<span style="background-color: #f7e3ed; padding:0px;"> Including Archived </span>)
					<?php endif; ?>
				</h4>


				<table cellspacing="0" style="display: table;">
					<thead>
					<tr>
						<th width="60%">
							<a href="<?php echo esc_attr( add_query_arg( [ 'orderby' => 'post_title' ], $page_url ) ); ?>">Title</a>
						</th>
						<th width="20%">
							<a href="<?php echo esc_attr( add_query_arg( [ 'orderby' => 'post_date' ], $page_url ) ); ?>">Date</a>
						</th>
						<th width="20%">Categories</th>
					</tr>
					</thead>
					<tbody>
					<?php if ( 'ASC' == $order ) { ?>
						<?php ksort( $documents ); ?>
					<?php } else { ?>
						<?php krsort( $documents ); ?>
					<?php } ?>

					<?php echo implode( '', $documents ); ?>
					</tbody>
				</table>
				<div class="foot"></div>
				<div class="space"></div>
				<p class="post-count documents-count"> Showing
					<span class="post-showing documents-showing"><?php echo esc_html( $total_documents ); ?></span> of
					<span
						class="post-total documents-total"><?php echo esc_html( $total_documents ); ?> <?php echo _n( 'document', 'documents', $total_documents ); ?>
						from <?php bloginfo( 'name' ); ?></span>
				</p>
			</div>

		<?php else : ?>
			Sorry No Documents were found <?php if ( $active_cat != "all-categories" ) { ?> from CATEGORY: <?php echo $active_cat;
			} ?>
		<?php endif; ?>


		<?php if ( ! $show_expired ) { ?>
			<p><b>Looking for something in particular? <a href="?include_archived">Show Archived Documents</a></b></p>
		<?php } else { ?>
			<p><b>Notice:</b> Showing Archived Documents
		<?php } ?>


		<?php
		$html = ob_get_clean();

		// Cache this puppy
		return $html;
	}
}

new Sudbury_Documents_Shortcode();
