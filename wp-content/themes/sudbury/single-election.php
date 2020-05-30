<?php
/**
 * The Template for displaying all single posts.
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
			<main id="main-col" <?php sudbury_main_col_class( '', 'full' ); ?>>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<?php
					/*
					 * Start Election Application Code
					 */
					$today = date( "m/d/Y g:i:s A" );


					include_once( get_template_directory() . "/elections/include.php" );

					$out = "";
					global $sudbury_elections, $election;

					if ( $sudbury_elections->election_id ) {
						$showsidebar    = false;
						$e_id           = $sudbury_elections->election_id;
						$e_id           = absint( $e_id ); // WP Sanitation of the ID
						$election_query = sprintf( "`elections` WHERE `id` = %d", $e_id );


						$out      .= "<div id=\"news-article\">";
						$election = mysqli_fetch_assoc( mysqli_query( $conn, "SELECT * FROM " . $election_query ) );
						if ( $election ) {
							$out .= "<h1>" . $election['type'] . " - " . date( "F d, Y", $election['date'] );
							if ( $election['type_note'] != '' ) {
								$out .= " <span class=\"section-header-links\"> (" . $election['type_note'] . ") | <a href=\"/elections/\">Back to List</a></span>";
							} else {
								$out .= " <span style=\"color:#000; font-size:14px;\"> <a href=\"/elections/\">Back to List</a></span>";
							}
							$out .= "</h1>";
							if ( $election['status'] != "OFFICIAL" ) {
								$out .= '<div class="alert unofficial"><img src="' . get_template_directory_uri() . '/lib/icons/error.png" alt="This election is UNOFFICIAL" /> This Election is UNOFFICIAL.</div><div class="clr"></div>';
							}
							if ( $election['description'] != "" ) {
								$out .= '<div style="padding:0 10px; border-radius: 5px;">' . $election['description'] . '</div>';
							}
							//draw the votes table
							$table = "";
							function popEmpty( $item ) {
								if ( isset( $item ) && ! empty( $item ) && $item != "" ) {
									return $item;
								}

								return;
							}

							$table            .= "<table cellpadding=\"3\" cellspacing=\"1\" style=\"width:100%\"><tbody>";
							$precincts_active = explode( ",", $election['pcts_counted'] );
							$precincts_active = array_filter( $precincts_active, "popEmpty" );
							$total            = in_array( 'total', $precincts_active ) ? true : false;
							if ( $total ) {
								$precincts_active = array();
							}

							$out .= '
        <style type="text/css">
        /*table { font-size: 14px; }*/
        td i { color: #666; font-size: 11px; padding-right: 5px; display: block; float: right; font-style: normal; }
        ul li a { color: blue; text-decoration: none; }
        ul li a:hover { color: black; }
        td.txt { line-height: 130%; padding: 10px; }

        .bgD { background: #FFF; }
	.election-header { background: #aaa; font-weight: bold; }
        .space { height:15px; }
        .cellwidth { width: 95px; }
        .totalwidth { width: 60px !important; text-align:right; border-left:none !important; }
        .totalwidth th { text-align:center; }
        .totals_party, .totals_office { font-weight:bold; }
        .totals_party { background-color:#EEE; }
        .totals_office { background: #3B619C; color:#fff; }

        ';
							if ( $total ) {

								$out .= '.firstcol { border-right:none; }';
							}
							$out .= '
        .grayed::before { content: "-"; }
        th, td { text-align:left; }
        .official, .unofficial { -webkit-border-radius: 10px; -moz-border-radius: 10px; font-weight:bold; font-size:1.2em; float: left; padding: 10px; margin: 0px 10px 10px 0px; font-size: 13px; }
        .official { background: #e7ffe1; border: 1px solid #55c936; }
        .unofficial { border: 1px solid #EFB6B6; background: #FDF2F2; }
        .clr { clear:both; }
        .h3 { margin-bottom: 10px; }
    </style>';


							function drawHeader( $str, $precincts_active ) {
								$ret = "<tr class=\"election-header\"><th>" . $str . "</th>";

								global $total;

								if ( ! $total ) {
									if ( in_array( 'pct1', $precincts_active ) ) {
										$ret .= '<th class="cellwidth">Precinct 1</th>';
									}
									if ( in_array( 'pct1a', $precincts_active ) ) {
										$ret .= '<th class="cellwidth">Precinct 1A</th>';
									}
									if ( in_array( 'pct2', $precincts_active ) ) {
										$ret .= '<th class="cellwidth">Precinct 2</th>';
									}
									if ( in_array( 'pct3', $precincts_active ) ) {
										$ret .= '<th class="cellwidth">Precinct 3</th>';
									}
									if ( in_array( 'pct4', $precincts_active ) ) {
										$ret .= '<th class="cellwidth">Precinct 4</th>';
									}
									if ( in_array( 'pct5', $precincts_active ) ) {
										$ret .= '<th class="cellwidth">Precinct 5</th>';
									}
									$ret .= "<th class=\"totalwidth\">Total</th></tr>";

									return $ret;
								} else {
									$ret .= '<th class="totalwidth"><b>Total</b></th></tr>';

									return $ret;
								}
							}

							$cc    = 0;
							$party = "";
							$pos   = "";
							function drawPartyTotals( $precincts_active, $totals_party, $party ) {
								global $total, $election;
								if ( ! $election["totals_by_party"] || $party == "" ) {
									return "";
								}
								$table .= '<tr class="totals_party"><td>Totals - ' . $party . "</td>";
								$TP    = 0;
								foreach ( $precincts_active as $key => $val ) {
									$table .= '<td class="cellwidth ' . $val . '">' . $totals_party[ $val ] . '</td>';
									$TP    += $totals_party[ $val ];
								}

								if ( $total ) {
									$table .= '<td class="totalwidth">' . $totals_party['total'] . '</td>';
								} else {
									$table .= '<td class="totalwidth">' . $TP . '</td>';
								}

								return $table . '</tr>';
							}

							function drawOfficeTotals( $precincts_active, $totals_office ) {
								global $total, $election;
								if ( ! $election["totals_by_office"] ) {
									return "";
								}
								$table = '<tr class="totals_office"><td>Totals for Office</td>';
								$TO    = 0;
								if ( ! empty( $precincts_active ) ) {
									foreach ( $precincts_active as $key => $val ) {
										$table .= '<td class="cellwidth ' . $val . '">' . $totals_office[ $val ] . '</td>';
										$TO    += $totals_office[ $val ];
									}
								} else {
									$TO = $totals_office['total'];
								}

								if ( $total ) {

									$table .= '<td class="totalwidth">' . $totals_office['total'] . '</td>';
								} else {
									$table .= '<td class="totalwidth">' . $TO . '</td>';
								}

								return $table . '</tr>';
							}

							$candidates    = mysqli_query( $conn, "SELECT * FROM `candidates` WHERE `election_id` = " . $e_id . " ORDER BY `office_order`, `position`, `order`, CASE WHEN party like 'DEMOCRAT%' THEN 0 WHEN party like 'REPUBLICAN%' THEN 1 ELSE 2 END, `party`, `name`" );
							$totals_party  = array();
							$totals_office = array();
							while ( $cand = mysqli_fetch_assoc( $candidates ) ) {
								if ( $cand['position'] != $pos ) {


									if ( $pos != "" ) {
										$table .= drawPartyTotals( $precincts_active, $totals_party, $party );
										$table .= drawOfficeTotals( $precincts_active, $totals_office );
									}
									$table         .= "</tbody></table><div style=\"hieght: 50px;\">&nbsp;</div><table cellpadding=\"3\" cellspacing=\"1\" style=\"width:100%\"><tbody>";
									$table         .= "<tr><td colspan=\"" . ( count( $precincts_active ) + 2 ) . "\" style=\"\"><h3>" . $cand['position'] . "</h3></td></tr>";
									$table         .= drawHeader( "Candidate Name", $precincts_active );
									$pos           = $cand['position'];
									$party         = ""; // reset party
									$totals_office = array(); // reset office totals
								}
								if ( $cand['party'] != $party ) {
									// write totals row
									if ( $party != "" ) {

										$table .= drawPartyTotals( $precincts_active, $totals_party, $party );
									}
									$totals_party = array();
									$table        .= "<tr class=\"election-party\"><td colspan=\"" . ( count( $precincts_active ) + 1 ) . "\"><b>" . $cand['party'] . "</b></td><td></td></tr>";
									$party        = $cand['party'];
								}
								$name  = str_replace( '_', '', $cand['name'] );
								$addr  = ( ! empty( $cand['address'] ) ) ? $cand['address'] : "";
								$notes = ( ! empty( $cand['notes'] ) ) ? " (" . $cand['notes'] . ")" : "";
								$table .= "<tr" . ( ( $cc % 2 == 0 ) ? "" : " class=\"bgD\"" ) . ">
<td class=\"firstcol\">" . $name . ( ( ! empty( $addr ) || ! empty( $notes ) ) ? "<i>" . $addr . $notes . "</i>" : "" ) . "</td>";

								foreach ( $cand as $key => $val ) {
									$f = explode( '_', $key );
									if ( $f[0] == 'votes' ) {
										if ( isset( $totals_party[ $f[1] ] ) ) {
											$totals_party[ $f[1] ] += $val;
										} else {
											$totals_party[ $f[1] ] = $val;
										}
										if ( isset( $totals_office[ $f[1] ] ) ) {
											$totals_office[ $f[1] ] += $val;
										} else {
											$totals_office[ $f[1] ] = $val;
										}
									}

								}

								if ( ! $total ) {
									if ( in_array( 'pct1', $precincts_active ) ) {
										$table .= '<td class="cellwidth pct1">' . $cand['votes_pct1'] . '</td>';
									} else {
										// $cand['votes_pct1'] = 0;
									}

									if ( in_array( 'pct1a', $precincts_active ) ) {
										$table .= '<td class="cellwidth pct1a">' . $cand['votes_pct1a'] . '</td>';
									} else {
										//$cand['votes_pct1a'] = 0;
									}

									if ( in_array( 'pct2', $precincts_active ) ) {
										$table .= '<td class="cellwidth pct2">' . $cand['votes_pct2'] . '</td>';
									} else {
										//$cand['votes_pct2'] = 0;
									}

									if ( in_array( 'pct3', $precincts_active ) ) {
										$table .= '<td class="cellwidth pct3">' . $cand['votes_pct3'] . '</td>';
									} else {
										//$cand['votes_pct3'] = 0;
									}

									if ( in_array( 'pct4', $precincts_active ) ) {
										$table .= '<td class="cellwidth pct4">' . $cand['votes_pct4'] . '</td>';
									} else {
										//$cand['votes_pct4'] = 0;
									}

									if ( in_array( 'pct5', $precincts_active ) ) {
										$table .= '<td class="cellwidth pct5">' . $cand['votes_pct5'] . '</td>';
									} else {
										//$cand['votes_pct5'] = 0;
									}
									$t     = ( intval( $cand['votes_pct1'] ) + intval( $cand['votes_pct1a'] ) + intval( $cand['votes_pct2'] ) + intval( $cand['votes_pct3'] ) + intval( $cand['votes_pct4'] ) + intval( $cand['votes_pct5'] ) );
									$table .= '<td class="totalwidth"><b>' . $t . '</b></td>';
									//$totals_party["total"] += $t;
									$totals_office["total"] += $t;
								} else {
									$table .= '<td class="totalwidth"><b>' . $cand['votes_total'] . '</b></td>';
									//Clear Array to keep colspan working
									$precincts_active = array();

								}


								$table .= '</tr>';
								$cc ++;
							}
							/* Last 2 Totals Rows (Party Totals and Office Totals) */

							$table .= drawPartyTotals( $precincts_active, $totals_party, $party );
							$table .= drawOfficeTotals( $precincts_active, $totals_office );

							$table .= "</tbody></table>";
							//see if we ran the loop or not.
							if ( $cc > 0 ) {
								$out .= $table;
							}
							if ( ! empty( $election['notes'] ) ) {
								$out .= "<p class=\"space\"></p><p>" . $election['notes'] . "</p>";
							}

							//ballot questions
							$cc1       = 0;
							$ballots   = "";
							$ballots   .= "<div style=\"hieght: 50px;\">&nbsp;</div>";
							$questions = mysqli_query( $conn, "SELECT * FROM `ballot_questions` WHERE `election_id` = " . $e_id . " ORDER BY `question_number`, `question_letter`" );
							while ( $que = mysqli_fetch_assoc( $questions ) ) {


								$ballots .= "<table cellpadding=\"3\" style=\"width:100%\" cellspacing=\"1\"><tbody><tr class=\"h3\"><td colspan=\"" .
								            ( count( $precincts_active ) + 2 ) . "\"><b style=\"\">BALLOT QUESTION " . $que['question_number'] . strtoupper( $que['question_letter'] ) . "</b>" .
								            ( ( ! empty( $que['notes'] ) ) ? " - " . $que['notes'] : "" ) . "</td></tr>";
								$ballots .= "<tr class=\"bgD\"><td colspan=\"" . ( count( $precincts_active ) + 2 ) . "\" class=\"txt\">" . nl2br( $que['question'] ) . "</td></tr>";
								if ( $que['results_no'] != true ) {
									$ballots .= "<tr class=\"election-header\"><td style=\"width:150px;\">Ballot Question " . $que['question_number'] . strtoupper( $que['question_letter'] ) . ", Results</td>";

									if ( in_array( 'pct1', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth">Precinct 1</td>';
									} else {
										$cand['votes_pct1'] = 0;
									}

									if ( in_array( 'pct1a', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth">Precinct 1A</td>';
									} else {
										$cand['votes_pct1a'] = 0;
									}

									if ( in_array( 'pct2', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth">Precinct 2</td>';
									} else {
										$cand['votes_pct2'] = 0;
									}

									if ( in_array( 'pct3', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth">Precinct 3</td>';
									} else {
										$cand['votes_pct3'] = 0;
									}

									if ( in_array( 'pct4', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth">Precinct 4</td>';
									} else {
										$cand['votes_pct4'] = 0;
									}

									if ( in_array( 'pct5', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth">Precinct 5</td>';
									} else {
										$cand['votes_pct5'] = 0;
									}

									$ballots .= '<td class="totalwidth">Totals</td></tr>';

									//Yes
									$ballots .= '<tr><td>Yes</td>';
									if ( in_array( 'pct1', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1">' . $que['yes_pct1'] . '</td>';
									} else {
										$que['yes_pct1'] = 0;
									}

									if ( in_array( 'pct1a', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1a">' . $que['yes_pct1a'] . '</td>';
									} else {
										$que['yes_pct1a'] = 0;
									}

									if ( in_array( 'pct2', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct2">' . $que['yes_pct2'] . '</td>';
									} else {
										$que['yes_pct2'] = 0;
									}

									if ( in_array( 'pct3', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct3">' . $que['yes_pct3'] . '</td>';
									} else {
										$que['yes_pct3'] = 0;
									}

									if ( in_array( 'pct4', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct4">' . $que['yes_pct4'] . '</td>';
									} else {
										$que['yes_pct4'] = 0;
									}

									if ( in_array( 'pct5', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct5">' . $que['yes_pct5'] . '</td>';
									} else {
										$que['yes_pct5'] = 0;
									}
									$ballots .= '<td class="totalwidth">' . ( $total ? ( $que['yes_total'] ) : ( $que['yes_pct1'] + $que['yes_pct1a'] + $que['yes_pct2'] + $que['yes_pct3'] + $que['yes_pct4'] + $que['yes_pct5'] ) ) . '</td>';
									$ballots .= '</tr>';
									//No
									$ballots .= '<tr><td>No</td>';
									if ( in_array( 'pct1', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1">' . $que['no_pct1'] . '</td>';
									} else {
										$que['no_pct1'] = 0;
									}

									if ( in_array( 'pct1a', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1a">' . $que['no_pct1a'] . '</td>';
									} else {
										$que['no_pct1a'] = 0;
									}

									if ( in_array( 'pct2', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct2">' . $que['no_pct2'] . '</td>';
									} else {
										$que['no_pct2'] = 0;
									}

									if ( in_array( 'pct3', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct3">' . $que['no_pct3'] . '</td>';
									} else {
										$que['no_pct3'] = 0;
									}

									if ( in_array( 'pct4', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct4">' . $que['no_pct4'] . '</td>';
									} else {
										$que['no_pct4'] = 0;
									}

									if ( in_array( 'pct5', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct5">' . $que['no_pct5'] . '</td>';
									} else {
										$que['no_pct5'] = 0;
									}

									$ballots .= '<td class="totalwidth">' . ( $total ? ( $que['no_total'] ) : ( $que['no_pct1'] + $que['no_pct1a'] + $que['no_pct2'] + $que['no_pct3'] + $que['no_pct4'] + $que['no_pct5'] ) ) . '</td>';
									$ballots .= '</tr>';

									//Blank
									$ballots .= '<tr><td>Blanks</td>';
									if ( in_array( 'pct1', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1">' . $que['blank_pct1'] . '</td>';
									} else {
										$que['blank_pct1'] = 0;
									}

									if ( in_array( 'pct1a', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1a">' . $que['blank_pct1a'] . '</td>';
									} else {
										$que['blank_pct1a'] = 0;
									}

									if ( in_array( 'pct2', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct2">' . $que['blank_pct2'] . '</td>';
									} else {
										$que['blank_pct2'] = 2;
									}

									if ( in_array( 'pct3', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct3">' . $que['blank_pct3'] . '</td>';
									} else {
										$que['blank_pct3'] = 0;
									}

									if ( in_array( 'pct4', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct4">' . $que['blank_pct4'] . '</td>';
									} else {
										$que['blank_pct4'] = 0;
									}

									if ( in_array( 'pct5', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct5">' . $que['blank_pct5'] . '</td>';
									} else {
										$que['blank_pct5'] = 0;
									}

									$ballots .= '<td class="totalwidth">' . ( $total ? ( $que['blank_total'] ) : ( $que['blank_pct1'] + $que['blank_pct1a'] + $que['blank_pct2'] + $que['blank_pct3'] + $que['blank_pct4'] + $que['blank_pct5'] ) ) . '</td>';
									$ballots .= '</tr>';

									//totals

									$t1     = $que['yes_pct1'] + $que['no_pct1'] + $que['blank_pct1'];
									$t1a    = $que['yes_pct1a'] + $que['no_pct1a'] + $que['blank_pct1a'];
									$t2     = $que['yes_pct2'] + $que['no_pct2'] + $que['blank_pct2'];
									$t3     = $que['yes_pct3'] + $que['no_pct3'] + $que['blank_pct3'];
									$t4     = $que['yes_pct4'] + $que['no_pct4'] + $que['blank_pct4'];
									$t5     = $que['yes_pct5'] + $que['no_pct5'] + $que['blank_pct5'];
									$total_ = $que['yes_total'] + $que['no_total'] + $que['blank_total'];


									$ballots .= "<tr class=\"totals_office\" style=\"font-weight:bold\"><td>Totals</td>";

									if ( in_array( 'pct1', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1">' . $t1 . '</td>';
									} else {
										$t1 = 0;
									}

									if ( in_array( 'pct1a', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct1a">' . $t1a . '</td>';
									} else {
										$t1a = 0;
									}

									if ( in_array( 'pct2', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct2">' . $t2 . '</td>';
									} else {
										$t2 = 0;
									}

									if ( in_array( 'pct3', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct3">' . $t3 . '</td>';
									} else {
										$t3 = 0;
									}

									if ( in_array( 'pct4', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct4">' . $t4 . '</td>';
									} else {
										$t4 = 0;
									}

									if ( in_array( 'pct5', $precincts_active ) ) {
										$ballots .= '<td class="cellwidth pct5">' . $t5 . '</td>';
									} else {
										$t5 = 0;
									}

									if ( ! $total ) {
										$tt      = $t1 + $t1a + $t2 + $t3 + $t4 + $t5;
										$ballots .= '<td class="totalwidth">' . $tt . '</td>';
									} else {
										$ballots .= '<td class="totalwidth">' . $total_ . '</td>';
									}
								}


								$ballots .= "</tbody></table><div class=\"space\"></div>";
								$cc1 ++;
							}

							$ballots .= "</tbody></table>";
							if ( $cc1 > 0 ) {
								$out .= $ballots;
							}
							$out .= "</div>";
						} else {
							$out .= '<div class="alert unofficial"><img src="' . get_template_directory_uri() . '/lib/icons/error.png" alt="Election Not Found" /> No Election with ID ' . $e_id . '</div ><div class="clr" ></div> ';
						}

					}


					echo $out;
					/*
					 * End Election Application
					 */
					?>

				</article><!-- #main-col -->
			</main>
		</div>
	</div>
</div>

<?php get_footer(); ?>
