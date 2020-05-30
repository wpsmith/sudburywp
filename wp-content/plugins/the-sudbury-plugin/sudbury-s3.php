<?php
require dirname( __FILE__ ) . '/aws/aws-autoloader.php';

use Aws\Common\Aws;
class Sudbury_S3_Manager {
	public $bucket = '';

	public $region = '';

	/**
	 * Constructor
	*/
	function __construct( $bucket, $region='us-east-1' ) {
		add_action( 'init', array( &$this, 'init' ) );
		add_filter( 'wp_get_attachment_url', array( &$this, 'attachment_url' ), 1, 2 );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ), 1, 2 );
		add_action( 's3_upload_attachment', array( &$this, 'upload_attachment'), 10, 1 );
		$this->bucket = $bucket;
		$this->region = $region;
	}

	function init() {
		add_action('add_attachment', array( &$this, 'enqueue_for_upload' ), 1);
		add_action('edit_attachment', array( &$this, 'enqueue_for_upload' ), 1);
		add_filter('image_make_intermediate_size', array( &$this, 'image_make_intermediate_size' ), 1000 );
	}
	/**
	 * Replaces the file URL with the S3 URL Link
	 */
	function attachment_url( $url, $attachment_id ) {
		if ( defined( 'USE_AMAZON_S3' ) && USE_AMAZON_S3 && $this->is_synchronized( $attachment_id ) ) {
			$path = get_attached_file( $attachment_id );
			$path = str_replace( ABSPATH, '', $path );
			$new_url = "https://s3-{$this->region}.amazonaws.com/{$this->bucket}/$path";
			return $new_url;
		}
		return $url;
	}

	/**
	 * Put a warning on the page while the document is being uploaded
	 */
	function admin_notices() {	
		// Post Save Validation (non-critical warning/advisory type things)
		if ( 'post' != get_current_screen()->base ) {
			return;
		}

		$post  = get_post();

		if ( ! $post ) {
			return;
		}

		if ( $post->post_type != 'attachment' ) {
			return;
		}

		$queued = wp_next_scheduled( 's3_upload_attachment', array( $post->ID ) );

		if ( $queued ) : ?>
			<div class="notice notice-info">
				<p><span class="dashicons dashicons-update"></span> <?php _e( 'This document is queued for upload to the Amazon S3 Cloud', 'sudbury' ); ?></p>
			</div>
		<?php elseif ( ! $this->is_synchronized( $post->ID ) ): ?>
			<div class="notice notice-warning">
				<p><span class="dashicons dashicons-update"></span> <?php _e( 'The automatic sync with S3 seems to have failed, this is not a major concern as the document will continure to be served from the Town Servers, however Information Systems would like to look into this document so please let us know.', 'sudbury' ); ?></p>
			</div>
		<?php endif;
	}

	/**
	 * Immediately upload new files to S3 when an attachment is uploaded
	 */
	function upload_attachment( $attachment_id ) {	
		$this->upload( get_attached_file( $attachment_id ) );
		$this->mark_as_uploaded( $attachment_id );
	}

	function image_make_intermediate_size( $filename ) {
		$this->upload( $filename );
		return $filename;
	}

	function upload( $file ) {
		_sudbury_log( "[INFO] Beginning upload of attachment {$file} to S3" );
		// Create the AWS service builder, providing the path to the config file
		$aws = Aws::factory('/mnt/dev0/nginx/sudbury.ma.us/aws-config.php');

		$client = $aws->get('s3');

		$mime = wp_check_filetype( $file )['type'];
		$key = str_replace( ABSPATH, '', $file );

		try {
			$result = $client->putObject(array(
				'Bucket'      => $this->bucket,
				'ContentType' => $mime,
				'Key'         => $key, 
				'SourceFile'  => $file,
			));

			_sudbury_log( '[SUCCESS] Finished Uploading File to S3' );
			_sudbury_log( $result );
		} catch (Exception $e) {
			_sudbury_log( '[ERROR] Failed to upload file to S3' );
			_sudbury_log( $e->getMessage() );
		}

		return $result;
	}

	function mark_as_uploaded( $attachment_id ) {
		global $blog_id;
		_sudbury_log("[S3] Marking attachment $attachment_id (blog $blog_id) as uploaded");
		return update_post_meta( $attachment_id, 'sudbury_s3_synchronized', 1 );
	}

	function is_synchronized( $attachment_id ) {
		$result = get_post_meta( $attachment_id, 'sudbury_s3_synchronized', true );
		if ( $result === '') {
			$this->mark_as_uploaded( $attachment_id );
			return true;
		} else {
			return (bool) $result;
		}
	}

	function enqueue_for_upload( $attachment_id ) {
		update_post_meta( $attachment_id, 'sudbury_s3_synchronized', 0 );
		wp_schedule_single_event( time(), 's3_upload_attachment', array( $attachment_id ) );
	}
}

$GLOBALS['sudbury_s3_manager'] = new Sudbury_S3_Manager( 'cdn.sudbury.ma.us', 'us-west-2' );

