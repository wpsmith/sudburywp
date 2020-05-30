<?php
/**
 * Simple PHP Git deploy script
 *
 * Automatically deploy the code using PHP and Git.
 *
 * @version 1.3.1
 * @link    https://github.com/markomarkovic/simple-php-git-deploy/
 */

// =========================================[ Configuration start ]===

/**
 * It's preferable to configure the script using `deploy-config.php` file.
 *
 * Rename `deploy-config.example.php` to `deploy-config.php` and edit the
 * configuration options there instead of here. That way, you won't have to edit
 * the configuration again if you download the new version of `deploy.php`.
 */
if ( file_exists( basename( __FILE__, '.php' ) . '-config.php' ) ) {
	require_once basename( __FILE__, '.php' ) . '-config.php';
}

/**
 * Protect the script from unauthorized access by using a secret access token.
 * If it's not present in the access URL as a GET variable named `sat`
 * e.g. deploy.php?sat=Bett...s the script is not going to deploy.
 *
 * @var string
 */
if ( ! defined( 'SECRET_ACCESS_TOKEN' ) ) {
	define( 'SECRET_ACCESS_TOKEN', 'BetterChangeMeNowOrSufferTheConsequences' );
}

/**
 * The address of the remote Git repository that contains the code that's being
 * deployed.
 * If the repository is private, you'll need to use the SSH address.
 *
 * @var string
 */
if ( ! defined( 'REMOTE_REPOSITORY' ) ) {
	define( 'REMOTE_REPOSITORY', 'https://github.com/markomarkovic/simple-php-git-deploy.git' );
}

/**
 * The branch that's being deployed.
 * Must be present in the remote repository.
 *
 * @var string
 */
if ( ! defined( 'BRANCH' ) ) {
	define( 'BRANCH', 'master' );
}

/**
 * The location that the code is going to be deployed to.
 * Don't forget the trailing slash!
 *
 * @var string Full path including the trailing slash
 */
if ( ! defined( 'TARGET_DIR' ) ) {
	define( 'TARGET_DIR', '/tmp/simple-php-git-deploy/' );
}

/**
 * Whether to delete the files that are not in the repository but are on the
 * local (server) machine.
 *
 * !!! WARNING !!! This can lead to a serious loss of data if you're not
 * careful. All files that are not in the repository are going to be deleted,
 * except the ones defined in EXCLUDE section.
 * BE CAREFUL!
 *
 * @var boolean
 */
if ( ! defined( 'DELETE_FILES' ) ) {
	define( 'DELETE_FILES', false );
}

/**
 * The directories and files that are to be excluded when updating the code.
 * Normally, these are the directories containing files that are not part of
 * code base, for example user uploads or server-specific configuration files.
 * Use rsync exclude pattern syntax for each element.
 *
 * @var serialized array of strings
 */
if ( ! defined( 'EXCLUDE' ) ) {
	define( 'EXCLUDE', serialize( array(
		'.git',
	) ) );
}

/**
 * Temporary directory we'll use to stage the code before the update. If it
 * already exists, script assumes that it contains an already cloned copy of the
 * repository with the correct remote origin and only fetches changes instead of
 * cloning the entire thing.
 *
 * @var string Full path including the trailing slash
 */
if ( ! defined( 'TMP_DIR' ) ) {
	define( 'TMP_DIR', '/tmp/spgd-' . md5( REMOTE_REPOSITORY ) . '/' );
}

/**
 * Whether to remove the TMP_DIR after the deployment.
 * It's useful NOT to clean up in order to only fetch changes on the next
 * deployment.
 */
if ( ! defined( 'CLEAN_UP' ) ) {
	define( 'CLEAN_UP', true );
}

/**
 * Output the version of the deployed code.
 *
 * @var string Full path to the file name
 */
if ( ! defined( 'VERSION_FILE' ) ) {
	define( 'VERSION_FILE', TMP_DIR . 'VERSION' );
}

/**
 * Time limit for each command.
 *
 * @var int Time in seconds
 */
if ( ! defined( 'TIME_LIMIT' ) ) {
	define( 'TIME_LIMIT', 30 );
}

/**
 * OPTIONAL
 * Backup the TARGET_DIR into BACKUP_DIR before deployment.
 *
 * @var string Full backup directory path e.g. `/tmp/`
 */
if ( ! defined( 'BACKUP_DIR' ) ) {
	define( 'BACKUP_DIR', false );
}

/**
 * OPTIONAL
 * Whether to invoke composer after the repository is cloned or changes are
 * fetched. Composer needs to be available on the server machine, installed
 * globaly (as `composer`). See http://getcomposer.org/doc/00-intro.md#globally
 *
 * @var boolean Whether to use composer or not
 * @link http://getcomposer.org/
 */
if ( ! defined( 'USE_COMPOSER' ) ) {
	define( 'USE_COMPOSER', false );
}

/**
 * OPTIONAL
 * The options that the composer is going to use.
 *
 * @var string Composer options
 * @link http://getcomposer.org/doc/03-cli.md#install
 */
if ( ! defined( 'COMPOSER_OPTIONS' ) ) {
	define( 'COMPOSER_OPTIONS', '--no-dev' );
}

/**
 * OPTIONAL
 * The COMPOSER_HOME environment variable is needed only if the script is
 * executed by a system user that has no HOME defined, e.g. `www-data`.
 *
 * @var string Path to the COMPOSER_HOME e.g. `/tmp/composer`
 * @link https://getcomposer.org/doc/03-cli.md#composer-home
 */
if ( ! defined( 'COMPOSER_HOME' ) ) {
	define( 'COMPOSER_HOME', false );
}

/**
 * OPTIONAL
 * Email address to be notified on deployment failure.
 *
 * @var string Email address
 */
if ( ! defined( 'EMAIL_ON_ERROR' ) ) {
	define( 'EMAIL_ON_ERROR', false );
}

// ===========================================[ Configuration end ]===
$access = false;
// If there's authorization error, set the correct HTTP header.
list( $algo, $hash ) = explode( '=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2 ) + array( '', '' );
if ( ! in_array( $algo, hash_algos(), true ) ) {
	header( "HTTP/1.1 400 Bad Request" );
	header( "Reason: Hash algorithm '$algo' is not supported." );
} else {

	$rawPost = file_get_contents( 'php://input' );
	if ( $hash !== hash_hmac( $algo, $rawPost, SECRET_ACCESS_TOKEN ) ) {
		header( 'HTTP/1.1 403 Forbidden' );
	} else {
		$access = true;
	}
}
ob_start();

if ( ! $access ) {
	die( '<h2>ACCESS DENIED!</h2>' );
}
if ( SECRET_ACCESS_TOKEN === 'BetterChangeMeNowOrSufferTheConsequences' ) {
	die( "<h2>You're suffering the consequences!<br>Change the SECRET_ACCESS_TOKEN from it's default value!</h2>" );
}
?>
Running as <b><?php echo trim( shell_exec( 'whoami' ) ); ?></b>.
<?php
// Check if the required programs are available
$requiredBinaries = array( 'git', 'rsync' );
if ( defined( 'BACKUP_DIR' ) && BACKUP_DIR !== false ) {
	$requiredBinaries[] = 'tar';
	if ( ! is_dir( BACKUP_DIR ) || ! is_writable( BACKUP_DIR ) ) {
		die( sprintf( '<div class="error">BACKUP_DIR `%s` does not exists or is not writeable.</div>', BACKUP_DIR ) );
	}
}
if ( defined( 'USE_COMPOSER' ) && USE_COMPOSER === true ) {
	$requiredBinaries[] = 'composer --no-ansi';
}
foreach ( $requiredBinaries as $command ) {
	$path = trim( shell_exec( 'which ' . $command ) );
	if ( $path == '' ) {
		die( sprintf( '<div class="error"><b>%s</b> not available. It needs to be installed on the server for this script to work.</div>', $command ) );
	} else {
		$version = explode( "\n", shell_exec( $command . ' --version' ) );
		printf( '<b>%s</b> : %s' . "\n"
			, $path
			, $version[0]
		);
	}
}
?>

Environment OK.

<?php
// The commands
$commands = array( 'git fetch --all', 'git reset --hard origin/master' );

// ========================================[ Pre-Deployment steps ]===


// Update the submodules
$commands[] = sprintf(
	'git submodule update --init --recursive'
);

// Describe the deployed version

// Backup the TARGET_DIR
// without the BACKUP_DIR for the case when it's inside the TARGET_DIR
if ( defined( 'BACKUP_DIR' ) && BACKUP_DIR !== false ) {
	$commands[] = sprintf(
		"tar --exclude='%s*' -czf %s/%s-%s-%s.tar.gz %s*"
		, BACKUP_DIR
		, BACKUP_DIR
		, basename( TARGET_DIR )
		, md5( TARGET_DIR )
		, date( 'YmdHis' )
		, TARGET_DIR // We're backing up this directory into BACKUP_DIR
	);
}


// =======================================[ Run the command steps ]===
$output = '';
foreach ( $commands as $command ) {
	set_time_limit( TIME_LIMIT ); // Reset the time limit for each command
	if ( file_exists( TMP_DIR ) && is_dir( TMP_DIR ) ) {
		chdir( TMP_DIR ); // Ensure that we're in the right directory
	}
	$tmp = array();
	exec( $command . ' 2>&1', $tmp, $return_code ); // Execute the command
	// Output the result
	printf( '$ %s
%s
'
		, trim( $command )
		, trim( implode( "\n", $tmp ) )
	);
	$output .= ob_get_contents();
	ob_flush(); // Try to output everything as it happens

	// Error handling and cleanup
	if ( $return_code !== 0 ) {
		printf( '
<div class="error">
Error encountered!
Stopping the script to prevent possible data loss.
CHECK THE DATA IN YOUR TARGET DIR!
</div>
'
		);
		$error = sprintf(
			'Deployment error on %s using %s!'
			, $_SERVER['HTTP_HOST']
			, __FILE__
		);
		error_log( $error );
		if ( EMAIL_ON_ERROR ) {
			$output    .= ob_get_contents();
			$headers   = array();
			$headers[] = sprintf( 'From: Simple PHP Git deploy script <simple-php-git-deploy@%s>', $_SERVER['HTTP_HOST'] );
			$headers[] = sprintf( 'X-Mailer: PHP/%s', phpversion() );
			mail( EMAIL_ON_ERROR, $error, strip_tags( trim( $output ) ), implode( "\r\n", $headers ) );
		}
		break;
	}
}
?>

Done.
