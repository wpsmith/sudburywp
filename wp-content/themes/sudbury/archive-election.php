<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */

get_header();
?>


<div id="cont">
	<div class="container-fluid container-fluid-xl">
		<div class="row">
			<?php sudbury_department_tabs(); ?>
			<main id="main-col" <?php sudbury_main_col_class(); ?>>

				<?php
				$today = date( "m/d/Y g:i:s A" );


				include_once( get_template_directory() . "/elections/include.php" );

				$out = "";
				global $sudbury_elections;

				$out   = "<h1 class=\"page-title\">Elections Archive</h1>";
				$year  = 0;
				$query = mysqli_query( $conn, "SELECT * FROM `elections` WHERE `status` = 'OFFICIAL' ORDER BY `date` DESC" );

				if ( $query !== false ) {
					while ( $row = mysqli_fetch_assoc( $query ) ) {
						if ( $year != $row['year'] ) {
							if ( $year != 0 ) {
								$out .= '</table><div class="foot"></div></div>';
							}
							$year = $row['year'];

							$out .= '<div class="tablecap"><div class="cap">' . $year . ' Elections</div>

								<table cellspacing=0>
								<tr>
									<th width="35%">Title</th>
									<th width="35%">Date</th>
									<th>Note</th>
								</tr>';
						}
						$out .= '<tr><td class="coltitle"><a href="/election/' . $row['id'] . '">' . $row['type'] . '</a></td><td>' . date( 'F d, Y', $row['date'] ) . '</td><td>' . ( ( ! empty( $row['type_note'] ) ) ? $row['type_note'] : "" ) . '</td></tr>';

					}
					$out .= '</table><div class="foot"></div></div>';
				} else {
					$out .= "<p>There is a problem connecting to the elections database. Please contact the webmaster.</p>";
				}


				echo $out;
				?>
			</main>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
