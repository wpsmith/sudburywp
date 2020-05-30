<?php
/**
 * Sudbury API File
 *
 * Holding off on this until the wordpress JSON api in 4.1
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */

use airmoi\FileMaker\FileMaker;
use airmoi\FileMaker\FileMakerException;
use airmoi\FileMaker\FileMakerValidationException;

function sudbury_api() {
	if ( isset( $_REQUEST['delete_attachment'] ) ) {
		if ( ! wp_verify_nonce( $_REQUEST['_wp_nonce'], 'sudbury-delete-attachment' ) ) {
			wp_send_json_error( array( '400 Bad Request' ) );
			die();
		}


		$id = $_REQUEST['delete_attachment'];
		if ( ! is_numeric( $id ) ) {
			wp_send_json_error( array( '400 Bad Formatted Request' ) );
			die();
		}

		if ( ! current_user_can( 'delete_post', $id ) ) {
			wp_send_json_error( array( '403 Not Authorized' ) );
			die();
		}

		wp_delete_attachment( $id );
		wp_send_json_success();
	}

	/* ajax for front page menu */
	if ( isset( $_GET['get'] ) ) {

		$g = $_GET['get'];

		if ( 'sites' == $g ) {
			wp_send_json( array_filter( array_map( function ( $blog ) {
				return get_blog_details( $blog['blog_id'], true )->blogname;
			}, wp_get_sites( array( 'limit' => false, 'archived' => 0 ) ) ), function ( $b ) {
				return ! is_utility( $b['blog_id'] );
			} ) );
		} elseif ( 'getDeptList' == $g ) {
			/* Get List of Departments and Committees for the Contact Search */

			$list = foreach_blog( function ( $blog ) {
				if ( is_utility() || is_archived( $blog['blog_id'] ) ) {
					return null;
				}

				$item = array();

				$item['is_committee']  = is_committee();
				$item['is_department'] = is_department();
				if ( $name = get_option( 'sudbury_board_membership_key', false ) ) {
					$item['BoardMembershipKey'] = $name;
					$item['encode']             = true;
				} else {
					$item['BoardMembershipKey'] = get_bloginfo( 'name' );
					$item['encode']             = false;
				}

				$item['long_name'] = get_bloginfo( 'name' );

				return $item;
			} );

			// Condense Array
			$list = array_filter( $list, 'is_array' );
			uasort( $list, function ( $a, $b ) {
				return $a['long_name'] > $b['long_name'] ? 1 : 0;
			} );
			wp_send_json( $list );
		} elseif ( $g == "staff" ) {
			$search = isset( $_REQUEST['term'] ) && isset( $_REQUEST['s_search'] );

			/* ALSO HANDLES: staff search ?dept=<string>&g=staff&term=<string>&s_search */
			if ( $search ) {
				$term = $_REQUEST['term'];

				/* Replacements */
				$term = strtolower( $term );
				if ( strpos( 'building department', $term ) != - 1 ) {
					str_replace( 'building department', '"building department"', $term );
				} else {
					$term = str_replace( "department", "", $term );
					$term = str_replace( "committee", "", $term );
					$term = str_replace( "commission", "", $term );
				}

			}

			require_once( plugin_dir_path( __FILE__ ) . 'Filemaker/autoloader.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'Filemaker/FileMaker.php' );

			$fms = new FileMaker( 'personnel records', FILEMAKER_HOST, FILEMAKER_USER, FILEMAKER_PASS );

			$deptreq = $fms->newFindCommand( 'WebLayoutPublic' );
			if ( $search ) {
				$deptreq->addFindCriterion( 'Web Department Name Long', $term );
				$deptreq->addFindCriterion( 'Full Name', $term );
				$deptreq->addFindCriterion( 'Building Name', str_replace( "building", "", strtolower( $term ) ) );
				$deptreq->addFindCriterion( 'Web Keywords', $term );
				$deptreq->addFindCriterion( 'Title', $term );
				$deptreq->setLogicalOperator( FILEMAKER_FIND_OR );
			} else {
				$deptreq->addFindCriterion( 'Web Department Name Short', "==" . $_GET['dept'] );
			}
			$deptreq->addSortRule( 'Web Order', 1, FILEMAKER_SORT_ASCEND );
			$deptreq->addSortRule( 'Title', 2, FILEMAKER_SORT_ASCEND );
			$deptreq->addSortRule( 'Last Name', 3, FILEMAKER_SORT_ASCEND );
			try {
				$impl_data = $deptreq->execute();
			} catch ( FileMakerException $ex ) {
				_sudbury_log( "FILEMAKER: Error! Execution failed" );
				_sudbury_log( $ex->getMessage() );
			}

			if ( $impl_data && ! FileMaker::isError( $impl_data ) ) {
				$records = $impl_data->getRecords();
				$staff   = array();
				if ( ! empty( $records ) ) {
					foreach ( $records as $record ) {
						if ( $record->getField( "Status" ) != "A" ) {
							continue;
						}
						if ( $search ) {
							$domain = is_production_server() ? 'subdury.ma.us' : 'beta.sudbury.ma.us';
							$bid    = get_blog_id_from_url( $domain, '/' . $record->getField( "Web Department Name Short" ) . '/' );

							$dept = array();

							$dept['name']      = $record->getField( "Web Department Name Short" );
							$dept['long_name'] = $record->getField( "Web Department Name Long" );
							$dept['email']     = $record->getField( "Web Department Email" );
							$dept['telephone'] = $record->getField( "Web Department Telephone Number" );

							if ( strpos( $dept['telephone'], "<br" ) !== false ) {
								$dept['telephone'] = substr( $dept['telephone'], 0, strpos( $dept['telephone'], "<br" ) - 1 );
							}

							if ( $record->getField( "Web Email" ) == '' ) {
								if ( $dept['email'] != '' ) {
									$email = explode( '@', $dept['email'] );
								} else {
									$email[0] = '';
								}
							} else {
								$email = explode( '@', $record->getField( "Web Email" ) );
							}
							$staff[] = array(
								"title" => $record->getField( "Full Name" ) . '  -  ' . $record->getField( "Title" ) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right;">' . $dept['telephone'] . ( $dept['telephone'] && $email ? ' |' : '' ) . ' <a ' . ( $email[0] != '' ? 'class="eml"' : '' ) . 'href="mailto:webaster@sudbury.ma.us" data-dmn="' . ( $email[1] == 'sudbury.ma.us' ? '' : $email[1] ) . '" data-ed="' . $email[0] . '" data-will="martop">webmaster@sudbury.ma.us</a></span>',
								"link"  => "/departments/" . $dept['name'] . '/#staff'
							);
						} else {
							$staff[] = array(
								"title" => $record->getField( "Full Name" ) . '  -  ' . $record->getField( "Title" ),
								"link"  => "/departments/" . $record->getField( "Web Department Name Short" ) . '/#staff'
							);
						}
					}
				}
			}
			if ( $search ) {
				$blogs   = get_blogs( array( 'all' => true ) );
				$matches = array();
				foreach ( $blogs as $key => $blog ) {
					if ( false !== strpos( strtolower( $blog['title'] ), trim( $term ) ) ) {
						$matches[] = $blog;
					} elseif ( false !== strpos( strtolower( $blog['url'] ), trim( $term ) ) ) {
						$matches[] = $blog;
					}
				}
				if ( $matches ) {
					foreach ( $matches as $dept ) {
						switch_to_blog( $dept['id'] );
						$telephone = get_option( 'sudbury_telephone', '' );
						$email     = get_option( 'sudbury_email', '' );
						$email     = explode( '@', $email );
						$staff[]   = array(
							"title" => '<a href="' . site_url() . '">' . sudbury_get_the_site_name() . '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right;">' . $telephone . ( $telephone && $email ? ' |' : '' ) . '<a ' . ( $email[0] != '' ? 'class="eml"' : '' ) . 'href="mailto:webaster@sudbury.ma.us" data-ed="' . $email[0] . '" data-dmn="' . ( $email[1] == 'sudbury.ma.us' ? '' : $email[1] ) . '" data-will="martop">webmaster@sudbury.ma.us</a></span>'
						);

						restore_current_blog();
					}
				}
			}
			if ( ! isset( $staff ) ) {
				$staff = array();
			}

			wp_send_json( $staff );
		} elseif ( $g == "members" ) {
			if ( isset( $_REQUEST['term'] ) && isset( $_REQUEST['s_search'] ) && $_REQUEST['s_search'] == true ) {
				$search = true;
				$term   = $_REQUEST['term'];

				$url = "http://filemaker.sudbury.ma.us/fmi/xsl/Statistics/BoardMembershipSearch.xsl?Boards::Web+Keywords=*" . urlencode( $term ) . "*&Boards::Name+Formal=*" . urlencode( $term ) . "*&Boards::Name+Formal=*" . urlencode( $term ) . "*&Boards::Board+Name=*" . urlencode( $term ) . "*&-lop=or&";
				ini_set( "default_socket_timeout", 3 );
				$filemakerxml = @file_get_contents( $url );
				$members      = array();
				if ( $filemakerxml ) {
					$xml = new SimpleXMLElement( $filemakerxml );
					foreach ( $xml->RESULTSET->ROW as $row ) {
						$titlestring = "";


						if ( $row->COL[10]->DATA != "" && substr( $row->COL[10]->DATA, 0, 7 ) != "VACANCY" && $row->COL[1]->DATA != "Staff" ) {
							$titlestring .= $row->COL[10]->DATA;

							$row->COL[1]->DATA != "" ? "  -  " . $row->COL[1]->DATA : "";
							$membershipKey = str_replace( ".", "%2E", urlencode( $row->COL[3]->DATA ) );
							$membershipKey = str_replace( "-", "%2D", $membershipKey );
							$membershipKey = str_replace( " ", "+", $membershipKey );

							$bid = false;
							foreach_blog( function ( $b ) use ( &$bid, &$membershipKey ) {
								if ( $key = get_option( 'sudbury_board_membership_key', false ) ) {
									if ( urlencode( $key ) == $membershipKey ) {
										$bid = $b['blog_id'];
									}
								}
							} );
							$CommitteeInfo = array();

							if ( $bid ) {
								switch_to_blog( $bid );
								$CommitteeInfo['name']      = sudbury_get_blog_slug();
								$CommitteeInfo['long_name'] = sudbury_get_the_site_name();
								$CommitteeInfo['email']     = get_option( 'sudbury_email', '' );
								$CommitteeInfo['telephone'] = get_option( 'sudbury_telephone', '' );
								$CommitteeInfo['fax']       = get_option( 'sudbury_fax', '' );
								restore_current_blog();
							}

							// If we have information about this member's Committee then
							if ( $CommitteeInfo ) {
								$email = $CommitteeInfo['email'];
								if ( $CommitteeInfo['long_name'] ) {
									$titlestring .= $CommitteeInfo['long_name'] != "" ? "  -  " . $CommitteeInfo['long_name'] : "";
								}
								if ( $CommitteeInfo['email'] != "" ) {
									$email = explode( "@", $email );


									$titlestring .= $email[0] != "" ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right;"><a ' . ( $email[0] != '' ? 'class="eml"' : '' ) . 'href="mailto:webaster@sudbury.ma.us" data-ed="' . $email[0] . '" data-dmn="' . ( $email[1] == 'sudbury.ma.us' ? '' : $email[1] ) . '" data-will="martop">webmaster@sudbury.ma.us</a></span>' : "";
								} else {
									if ( $row->COL[12]->DATA != "" ) {
										$email = explode( "@", $row->COL[12]->DATA );

										$titlestring .= $email[0] != "" ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right;"><a ' . ( $email[0] != '' ? 'class="eml"' : '' ) . 'href="mailto:webaster@sudbury.ma.us" data-ed="' . $email[0] . '" data-dmn="' . ( $email[1] == 'sudbury.ma.us' ? '' : $email[1] ) . '"  data-will="martop">webmaster@sudbury.ma.us</a><!----></span>' : "";
									} else {
										$titlestring .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right;"><a href="mailto:webaster@sudbury.ma.us" data-will="martop">THERE webmaster@sudbury.ma.us</a></span>';
									}
								}
							} else {
								if ( $row->COL[3]->DATA != "" ) {
									$titlestring .= '  -  ' . $row->COL[3]->DATA;
								}

								if ( $row->COL[12]->DATA != "" ) {
									$titlestring .= '<span style="float:right;"><a href="mailto:' . $row->COL[12]->DATA . '">' . $row->COL[12]->DATA . '</a></span>';
								} else {
									$titlestring .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right;"><a href="mailto:webaster@sudbury.ma.us" data-will="martop">HERE 2:' . $bid . 'webmaster@sudbury.ma.us</a></span>';
								}
							}
						}

						$members[] = array(
							"title" => $titlestring,
							"link"  => ''
						);
					}
				}
				header( 'Content-type: text/plain' );
				print( json_encode( $members ) );
				exit;
			}
		}
	}

	if ( isset( $_REQUEST['verify_token'] ) ) {
		$token = $_REQUEST['verify_token'];

		if ( $data = get_transient( '_sudbury_token_' . $token ) ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( 'Invalid Token' );
		}

	}
}

add_action( 'wp_ajax_nopriv_sudbury_api', 'sudbury_api' );
add_action( 'wp_ajax_sudbury_api', 'sudbury_api' );


class Sudbury_API {
	function __construct() {
		add_action( 'wp_ajax_nopriv_sudbury_search', array( &$this, 'sudbury_search' ) );
		add_action( 'wp_ajax_sudbury_search', array( &$this, 'search' ) );
	}

	function search() {
		$term = '';
		if ( ! isset( $_REQUEST['term'] ) ) {
			wp_send_json_error( 'Provide a search term' );
		} else {
			$term = $_REQUEST['term'];
		}

		$datasets = array();

		if ( ! isset( $_REQUEST['dataset'] ) || ! is_array( $_REQUEST['dataset'] ) ) {
			wp_send_json_error( 'Request a dataset to search' );
		} else {
			$datasets = $_REQUEST['dataset'];
		}


		// Assume success
		$data = array( 'success' => true );

		foreach ( $datasets as $dataset ) {
			$result = array( 'success' => false );

			switch ( $dataset ) {
				case 'buildings':
					$result = $this->_search_buildings( $term );
					break;
			}
			$data['success']             &= $result['success'];
			$data['results'][ $dataset ] = $result;
		}
		wp_send_json_success( $data );
	}

	function _search_buildings( $term ) {
		$result = array( 'success' => true, 'resuults' => [] );

		$buildings = get_buildings( array( 's' => $term ) );

		foreach ( $buildings as $building ) {

			$allowed = array( 'post_title', 'post_excerpt', 'post_status' );
			$public  = array_filter(
				(array) $building,
				function ( $key ) use ( $allowed ) {
					return in_array( $key, $allowed );
				},
				ARRAY_FILTER_USE_KEY
			);


			$result['results'][] = $public;
		}

		return $result;
	}


}

new Sudbury_API();


function sudbury_rest_prepare_location( $data, $post, $context ) {
	$meta = get_post_meta( $post->ID );

	foreach ( $meta as $key => $values ) {
		if ( strpos( $key, '_location' ) === 0 ) {
			$data->data[ substr( $key, 10 ) ] = $values[0];
		}
	}

	return $data;
}

add_filter( 'rest_prepare_location', 'sudbury_rest_prepare_location', 10, 3 );


class Sudbury_Sites_API {

	function __construct() {
		add_filter( 'json_endpoints', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the additional API routes
	 *
	 * @param array $routes The routes from the WP REST API
	 *
	 * @return array The filtered array of API routes
	 */
	public function register_routes( array $routes ) {

		$routes['/sites'] = array(
			array( array( $this, 'get_sites' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

	/**
	 * Get the list of public sites
	 * @return array The list of public sites
	 */
	function get_sites() {


	}

}


class Sudbury_REST_Sites_Controller {

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->namespace     = '/sudbury/v1';
		$this->resource_name = 'sites';
		$this->base_query    = array(
			'public'   => 1,
			'archived' => 0,
			'mature'   => 0,
			'spam'     => 0,
			'deleted'  => 0,
			'limit'    => 0
		);
	}

	// Register our routes.
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			// Register our schema callback.
			'schema' => array( $this, 'get_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			// Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			// Register our schema callback.
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool True if the current user passes has permissions to view sites, otherwise false
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: Remove
		return true;
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the sites resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function get_items( $request ) {

		if ( ! empty( $request['search'] ) ) {
			$sites = $this->search( $request );
		} else if ( ! empty( $request['slug'] ) ) {
			$sites = $this->get_by_slug( $request['slug'] );
		} else {
			$args = $this->base_query;

			$sites = get_sites( $args );
		}

		$data = array();

		if ( empty( $sites ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $sites as $site ) {
			$response = $this->prepare_item_for_response( $site, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

	public function search( $request ) {
		$term    = $request['search'];
		$matched = array();

		$sites = get_sites( $this->base_query );

		foreach ( $sites as $site ) {
			$details = $this->get_site_details( $site );
			foreach ( $details as $key => $value ) {
				if ( is_string( $value ) && stripos( $value, $term ) !== false ) {
					$matched[] = $site;
					break;
				}
			}
		}

		return $matched;
	}

	public function get_by_slug( $slug ) {
		$query = $this->base_query;

		$query['path'] = '/' . $slug . '/';

		$sites = get_sites( $query );

		return $sites;
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool True if the current user passes has permissions to view sites, otherwise false
	 */
	public function get_item_permissions_check( $request ) {
		// TODO: Remove
		return true;

		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the sites resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return mixed An individual site
	 */
	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$site = get_site( $id );

		if ( empty( $site ) ) {
			return rest_ensure_response( array() );
		}

		$response = $this->prepare_item_for_response( $site, $request );

		// Return all of our post response data.
		return $response;
	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WP_Site         $site    The site object to prepare
	 * @param WP_REST_Request $request The current request
	 *
	 * @return mixed A Response
	 */
	public function prepare_item_for_response( $site, $request ) {
		$post_data = array();

		$schema = $this->get_item_schema( $request );

		// We are also renaming the fields to more understandable names.
		if ( isset( $schema['properties']['id'] ) ) {
			$post_data['id'] = (int) $site->blog_id;
		}

		$post_data = array_merge( $post_data, (array) $this->get_site_details( $site ) );

		//		if ( isset( $schema['properties']['details'] ) ) {
		//			$post_data['details'] =
		//		}

		if ( isset( $schema['properties']['link'] ) ) {
			$post_data['link'] = get_site_url( $site->blog_id );
		}

		if ( isset( $schema['properties']['name'] ) ) {
			$post_data['link'] = get_bloginfo();
		}


		return rest_ensure_response( $post_data );
	}

	/**
	 * Prepare a response for inserting into a collection of responses.
	 *
	 * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
	 *
	 * @param WP_REST_Response $response Response object.
	 *
	 * @return WP_REST_Response|array Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Get our sample schema for a post.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return array The sites schema
	 */
	public function get_item_schema( $request ) {
		$schema = array(
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'site',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => array(
				'id'      => array(
					'description' => esc_html__( 'Unique identifier for the site.', 'sudbury' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'link'    => array(
					'description' => esc_html__( 'The public URL for the site.', 'sudbury' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'details' => array(
					'description' => esc_html__( 'The details for the site.', 'sudbury' ),
					'type'        => 'object',
					'readonly'    => true
				),
			),
		);

		return $schema;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {
		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}

	public function get_site_details( $site ) {
		$blog_details = (array) get_blog_details( $site->blog_id );

		$details            = array();
		$details['name']    = $blog_details['blogname'];
		$details['link']    = $blog_details['siteurl'];
		$details['domain']  = $blog_details['domain'];
		$details['lang_id'] = $blog_details['lang_id'];
		$details['path']    = $blog_details['path'];
		$details['types']   = get_blog_option( $site->blog_id, 'sudbury_types', [] );
		$details['email']   = get_blog_option( $site->blog_id, 'sudbury_email', '' );
		$details['phone']   = get_blog_option( $site->blog_id, 'sudbury_telephone', '' );
		$details['fax']     = get_blog_option( $site->blog_id, 'sudbury_fax', '' );
		$location_id        = get_blog_option( $site->blog_id, 'sudbury_location_id', '' );
		$location           = sudbury_get_location( $location_id, ARRAY_A );
		if ( $location ) {
			$new_location = array();
			foreach ( $location as $key => $value ) {
				if ( strpos( $key, 'location_' ) === 0 ) {
					$new_location[ substr( $key, strlen( 'location_' ) ) ] = $value;
				} else {
					$new_location[ $key ] = $value;
				}
			}
			$location = $new_location;
			if ( $location['status'] == '1' && $location['private'] == '0' ) {
				$details['location']         = $location;
				$details['location']['link'] = sudbury_get_post_permalink( $location['post_id'], $location['blog_id'] );
			} else {
				$details['location'] = array(
					'private' => $location['private'],
					'status'  => $location['status']
				);
			}
		}

		$details['office_hours']     = get_blog_option( $site->blog_id, 'sudbury_office_hours', '' );
		$details['facebook']         = get_blog_option( $site->blog_id, 'sudbury_facebook_url', '' );
		$details['twitter']          = get_blog_option( $site->blog_id, 'sudbury_twitter_url', '' );
		$details['youtube']          = get_blog_option( $site->blog_id, 'sudbury_youtube_url', '' );
		$details['google_plus']      = get_blog_option( $site->blog_id, 'sudbury_google_plus_url', '' );
		$details['alt_homepage']     = get_blog_option( $site->blog_id, 'sudbury_redirect_url', '' );
		$details['keywords']         = get_blog_option( $site->blog_id, 'sudbury_keywords', [] );
		$details['archived_message'] = get_blog_option( $site->blog_id, 'sudbury_archived_message', '' );
		$details['relationships']    = array(
			'parent'       => (int) get_blog_option( $site->blog_id, 'sudbury_parent', 0 ),
			'counterparts' => array_map( 'intval', get_blog_option( $site->blog_id, 'sudbury_counterparts', [] ) ),
			'children'     => array_map( 'intval', get_blog_option( $site->blog_id, 'sudbury_children', [] ) )
		);

		return $details;
	}
}


class Sudbury_REST_Staff_Controller {

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->namespace     = '/sudbury/v1';
		$this->resource_name = 'staff';
	}

	// Register our routes.
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			// Register our schema callback.
			'schema' => array( $this, 'get_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			// Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			// Register our schema callback.
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool True if the current user passes has permissions to view sites, otherwise false
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: Remove
		return true;
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the sites resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function get_items( $request ) {

		if ( ! empty( $request['search'] ) ) {
			$staff = $this->search( $request );
		} else {
			$staff = get_site_option( 'sudbury_all_fm_personnel' );

		}

		$data = array();

		if ( empty( $staff ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $staff as $dept => $people ) {
			foreach ( $people as $person ) {
				$response = $this->prepare_item_for_response( $person, $request );
				$data[]   = $this->prepare_response_for_collection( $response );
			}
		}

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

	public function search( $request ) {
		$term    = $request['search'];
		$matched = array();

		$staff = get_site_option( 'sudbury_all_fm_personnel' );

		foreach ( $staff as $dept => $people ) {
			foreach ( $people as $person ) {
				foreach ( $person as $key => $value ) {
					if ( is_string( $value ) && stripos( $value, $term ) !== false ) {
						if ( ! isset( $matched[ $dept ] ) ) {
							$matched[ $dept ] = array();
						}
						$matched[ $dept ][] = $person;
						break;
					}
				}
			}
		}

		return $matched;
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool True if the current user passes has permissions to view sites, otherwise false
	 */
	public function get_item_permissions_check( $request ) {
		// TODO: Remove
		return true;

		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the sites resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return mixed An individual site
	 */
	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$site = get_site( $id );

		if ( empty( $site ) ) {
			return rest_ensure_response( array() );
		}

		$response = $this->prepare_item_for_response( $site, $request );

		// Return all of our post response data.
		return $response;
	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WP_Site         $person  The person being returned
	 * @param WP_REST_Request $request The current request
	 *
	 * @return mixed A Response
	 */
	public function prepare_item_for_response( $person, $request ) {
		$post_data = array();
		$sites_controller = new Sudbury_REST_Sites_Controller();
		$site = $sites_controller->get_site_details( $sites_controller->get_by_slug($person['dept'])[0] );

		$post_data['first_name'] = $person['first_name'];
		$post_data['last_name']  = $person['last_name'];
		$post_data['full_name']  = $person['full_name'];
		$post_data['phone']      = $person['phone'];
		$post_data['title']      = $person['title'];
		$post_data['building']   = $person['building'];
		$post_data['order']      = $person['order'];
		$post_data['email']      = $person['email'];
		$post_data['dept']       = $site;
		if ( $person['keywords'] ) {
			$post_data['keywords'] = explode( ',', $person['keywords'] );
		} else {
			$post_data['keywords'] = [];
		}

		return rest_ensure_response( $post_data );
	}

	/**
	 * Prepare a response for inserting into a collection of responses.
	 *
	 * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
	 *
	 * @param WP_REST_Response $response Response object.
	 *
	 * @return WP_REST_Response|array Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Get our sample schema for a post.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return array The sites schema
	 */
	public function get_item_schema( $request ) {
		$schema = array(
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'site',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => array(
				'id'      => array(
					'description' => esc_html__( 'Unique identifier for the site.', 'sudbury' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'link'    => array(
					'description' => esc_html__( 'The public URL for the site.', 'sudbury' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'details' => array(
					'description' => esc_html__( 'The details for the site.', 'sudbury' ),
					'type'        => 'object',
					'readonly'    => true
				),
			),
		);

		return $schema;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {
		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}


class Sudbury_REST_Members_Controller {

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->namespace     = '/sudbury/v1';
		$this->resource_name = 'members';
	}

	// Register our routes.
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			// Register our schema callback.
			'schema' => array( $this, 'get_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			// Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			// Register our schema callback.
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool True if the current user passes has permissions to view sites, otherwise false
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: Remove
		return true;
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the sites resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function get_items( $request ) {

		if ( ! empty( $request['search'] ) ) {
			$membership = $this->search( $request );
		} else {
			$membership = get_site_option( 'sudbury_all_board_membership' );

		}

		$data = array();

		if ( empty( $membership ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $membership as $board_name => $members ) {
			foreach ( $members as $member ) {
				$response = $this->prepare_item_for_response( $member, $request );
				$data[]   = $this->prepare_response_for_collection( $response );
			}
		}

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

	public function search( $request ) {
		$term    = $request['search'];
		$matched = array();

		$members = get_site_option( 'sudbury_all_board_membership' );

		foreach ( $members as $board_name => $members ) {
			foreach ( $members as $member ) {
				foreach ( $member as $key => $value ) {
					if ( is_string( $value ) && stripos( $value, $term ) !== false ) {
						if ( ! isset( $matched[ $board_name ] ) ) {
							$matched[ $board_name ] = array();
						}
						$matched[ $board_name ][] = $member;
						break;
					}
				}
			}
		}

		return $matched;
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool True if the current user passes has permissions to view sites, otherwise false
	 */
	public function get_item_permissions_check( $request ) {
		// TODO: Remove
		return true;

		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the sites resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return mixed An individual site
	 */
	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$site = get_site( $id );

		if ( empty( $site ) ) {
			return rest_ensure_response( array() );
		}

		$response = $this->prepare_item_for_response( $site, $request );

		// Return all of our post response data.
		return $response;
	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WP_Site         $member  The member being returned
	 * @param WP_REST_Request $request The current request
	 *
	 * @return mixed A Response
	 */
	public function prepare_item_for_response( $member, $request ) {
		$post_data = array();

		// TODO: Add First Name to the WebLayout
		$post_data['first_name']      = trim( explode( ',', $member['Last Name First'] )[1] );
		$post_data['last_name']       = $member['Last Name'];
		$post_data['full_name']       = $member['Name Formal'];
		$post_data['position']        = $member['Status'];
		$post_data['board']           = $member['Board Name'];
		$post_data['appointed_year']  = $member['First Appointment Year'];
		$post_data['appointed_by']    = $member['Appointed by'];
		$post_data['term']            = $member['Term'];
		$post_data['term_expiration'] = $member['Term Expiration'];
		$post_data['email']           = ( $member['Search Web Contact'] ? $member['Search Web Contact'] : '' );
		$post_data['site']            = $member['site'];

		if ( $member['Web Keywords'] ) {
			$post_data['keywords'] = explode( ',', $member['Web Keywords'] );
		} else {
			$post_data['keywords'] = [];
		}

		return rest_ensure_response( $post_data );
	}

	/**
	 * Prepare a response for inserting into a collection of responses.
	 *
	 * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
	 *
	 * @param WP_REST_Response $response Response object.
	 *
	 * @return WP_REST_Response|array Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Get our sample schema for a post.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return array The sites schema
	 */
	public function get_item_schema( $request ) {
		$schema = array(
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'site',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => array(
				'id'      => array(
					'description' => esc_html__( 'Unique identifier for the site.', 'sudbury' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'link'    => array(
					'description' => esc_html__( 'The public URL for the site.', 'sudbury' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'details' => array(
					'description' => esc_html__( 'The details for the site.', 'sudbury' ),
					'type'        => 'object',
					'readonly'    => true
				),
			),
		);

		return $schema;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {
		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}

// Function to register our new routes from the controller.
function prefix_register_my_rest_routes() {
	$sites_controller = new Sudbury_REST_Sites_Controller();
	$sites_controller->register_routes();
	$staff_controller = new Sudbury_REST_Staff_Controller();
	$staff_controller->register_routes();
	$members_controller = new Sudbury_REST_Members_Controller();
	$members_controller->register_routes();
}

add_action( 'rest_api_init', 'prefix_register_my_rest_routes' );

function sudbury_allow_em_in_rest( $post_type, $post_type_object ) {
	if ( in_array( $post_type, array( 'location', 'event' ) ) ) {
		$post_type_object->show_in_rest = true;
		$post_type_object->rest_base    = "{$post_type}s";
	}
}

add_action( "registered_post_type", 'sudbury_allow_em_in_rest', 10, 2 );
