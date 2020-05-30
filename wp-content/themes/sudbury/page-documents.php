<?php
/*
Template Name: Special - Documents List Page
*/
inject_content( function ( $content ) {

// adding ability to send a category in url
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
		$extra_query['type']    = 'numeric';
		$query['meta_query'][]  = $extra_query;
	}

	$total_documents     = 0;
	$original_blog_id    = get_current_blog_id();
	$printed_filter_form = false;
	$meta                = sudbury_get_relationship_meta();
	$related_blogs       = array();
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
	?>
	<p>
	<span class="dropdown-cats">
		<div class="filter-form1">
 <?php
 if ( isset( $_POST['categorySelect'] ) ) {
	 $active_cat = $_POST['categorySelect'];
 }
 ?>
			<form method="post" action="">
				<label for="categorySelect">Category: </label>

				<div class="input-group mb-3">
					<select id="categorySelect" name="categorySelect" class="form-control">
        <option value='all-categories' <?php echo $active_cat == 'all-categories' ? ' selected="selected"' : ''; ?>>All Categories</option>
						<?php
						$current = get_current_site();
						foreach ( $related_blogs as $blog ) {
							// switch to the blog
							switch_to_blog( $blog );

							$site = get_site_url();

							// get_categories args
							$args       = array(
								'taxonomy'   => 'document_categories',
								'hide_empty' => 0
							);
							$categories = get_categories( $args );
							// find out if any posts in category; only want categories with posts
							$i = 0;
							foreach ( $categories as $category ) {
								//if ( !(is_category_hidden($category=>term_id, 'document_categories')) ) {
								if ( $category->slug != 'meeting-documents' ) { // remove Meeting Documents
									$p_args = array(
										'post_mime_type' => 'application*',
										'post_type'      => 'attachment',
										'tax_query'      => array(
											array(
												'taxonomy' => 'document_categories',
												'field'    => 'slug',
												'terms'    => esc_attr( $category->slug ),
											)
										)
									);
									if ( ! $show_expired ) {
										$extra_args['key']      = 'sudbury_end_date_timestamp';
										$extra_args['value']    = time();
										$extra_args['compare']  = '>';
										$p_args['meta_query'][] = $extra_query;
									}
									$cat_posts = get_posts( $p_args );
									if ( $cat_posts ) {
										$parent_child_name = get_category_parents( $category->term_id, false, ' - ' );
										if ( ! empty( $parent_child_name ) ) {
											$parent_child_name = substr( $parent_child_name, 0, - 3 );
										} else {
											$parent_child_name = $category->name;
										}
										$catnames[] = array(
											'name' => $parent_child_name,
											'slug' => $category->slug
										);
									}
								} // check for meeting-documents category slug.
								//}
							}
							// return to the current site
							restore_current_blog();
						}

						//$final_cats = array_unique($catnames);
						$tempArr    = array_unique( array_column( $catnames, 'slug' ) );
						$final_cats = array_intersect_key( $catnames, $tempArr );
						asort( $final_cats );

						foreach ( $final_cats as $final_cat ) {

							?>
							<option value='<?php echo esc_html( $final_cat['slug'] ) ?>' <?php echo $active_cat == $final_cat['slug'] ? ' selected="selected"' : ''; ?>><?php echo esc_html( $final_cat['name'] ); ?></option>
						<?php } ?>

			</select>
				<div class="input-group-append">
	<input type="submit" value="Submit" class="btn btn-secondary">
					</div>
			</div>
</form>
<div class="clear"></div>
		</div></span>
	</p>
	<?php $documents = array(); ?>
	<?php if ( $active_cat != 'all-categories' ) {
		$query['tax_query'] = array(
			array(
				'taxonomy' => 'document_categories',
				'field'    => 'slug',
				'terms'    => $active_cat,
			),
		);
	}
	?>
	<?php foreach ( $related_blogs as $blog ) : ?>
		<?php d('Swiched to Related blog: ' . _d($blog)); ?> 
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
						<a href="<?php echo site_url( '?attachment_id=' . get_the_ID() ); ?>"><?php the_title(); ?></a>
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
					<td style="vertical-align: top;"><?php bloginfo( 'name' ); ?>
					</td>
				</tr>
				<?php $documents[ get_post()->$orderby . random_string( 4 ) ] = ob_get_clean(); ?>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
		<?php restore_current_blog(); ?>
	<?php endforeach; ?>

	<?php if ( $documents ) : ?>
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

				<span class="pull-right">
		<?php if ( ! $show_expired ) { ?>
			<a href="<?php echo esc_attr( add_query_arg( [ 'include_archived' => true ], $page_url ) ); ?>">Include Archived Items</a>
		<?php } else { ?>
			<a href="<?php echo esc_attr( remove_query_arg( 'include_archived', $page_url ) ); ?>">Exclude Archived Documents</a>
		<?php } ?></span>
			</h4>


			<table cellspacing="0" style="display: table;">
				<thead>
				<tr>
					<th width="40%">
						<a href="<?php echo esc_attr( add_query_arg( [ 'orderby' => 'post_title' ], $page_url ) ); ?>">Title</a>
					</th>
					<th width="20%">
						<a href="<?php echo esc_attr( add_query_arg( [ 'orderby' => 'post_date' ], $page_url ) ); ?>">Date</a>
					</th>
					<th width="20%">Categories</th>
					<th width="20%">Site</th>
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
			<span style="background-color: #f7e3ed; padding:1px;">in pink</span></p>
	<?php } ?>

<?php } );

get_template_part( 'page', 'fullwidth' );
