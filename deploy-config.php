<?php
/**
 * Deployment configuration
 *
 * It's preferable to configure the script using this file istead of directly.
 *
 * Rename this file to `deploy-config.php` and edit the
 * configuration options here instead of directly in `deploy.php`.
 * That way, you won't have to edit the configuration again if you download the
 * new version of `deploy.php`.
 *
 * @version 1.3.1
 */

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/**
 * Defines a constant by ENV variable with a declared fallback value.
 *
 * @param string $name Name of the environment variable.
 * @param mixed $fallback
 */
function define_by_env( $name, $fallback ) {
	$name  = strtoupper( $name );
	$value = getenv( $name );
	if ( "" !== $value ) {
		define( $name, $value );
	} else {
		define( $name, $fallback );
	}
}

/**
 * Protect the script from unauthorized access by using a secret access token.
 * If it's not present in the access URL as a GET variable named `sat`
 * e.g. deploy.php?sat=Bett...s the script is not going to deploy.
 *
 * @var string
 */
define_by_env( 'SECRET_ACCESS_TOKEN', "" );

/**
 * The address of the remote Git repository that contains the code that's being
 * deployed.
 * If the repository is private, you'll need to use the SSH address.
 *
 * @var string
 */
define_by_env( 'REMOTE_REPOSITORY', 'https://github.com/edhurtig/sudbury.git' );

/**
 * The branch that's being deployed.
 * Must be present in the remote repository.
 *
 * @var string
 */
define_by_env( 'BRANCH', 'master' );

/**
 * The location that the code is going to be deployed to.
 * Don't forget the trailing slash!
 *
 * @var string Full path including the trailing slash
 */
define_by_env( 'TARGET_DIR', '/tmp/deploy/' );

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
define_by_env( 'DELETE_FILES', false );

/**
 * The directories and files that are to be excluded when updating the code.
 * Normally, these are the directories containing files that are not part of
 * code base, for example user uploads or server-specific configuration files.
 * Use rsync exclude pattern syntax for each element.
 *
 * @var serialized array of strings
 */
define_by_env( 'EXCLUDE', serialize( array(
	'.git',
) ) );

/**
 * Temporary directory we'll use to stage the code before the update. If it
 * already exists, script assumes that it contains an already cloned copy of the
 * repository with the correct remote origin and only fetches changes instead of
 * cloning the entire thing.
 *
 * @var string Full path including the trailing slash
 */
define_by_env( 'TMP_DIR', '/tmp/spgd-' . md5( REMOTE_REPOSITORY ) . '/' );

/**
 * Whether to remove the TMP_DIR after the deployment.
 * It's useful NOT to clean up in order to only fetch changes on the next
 * deployment.
 */
define_by_env( 'CLEAN_UP', true );

/**
 * Output the version of the deployed code.
 *
 * @var string Full path to the file name
 */
define_by_env( 'VERSION_FILE', TMP_DIR . 'VERSION' );

/**
 * Time limit for each command.
 *
 * @var int Time in seconds
 */
define_by_env( 'TIME_LIMIT', 180 );

/**
 * OPTIONAL
 * Backup the TARGET_DIR into BACKUP_DIR before deployment.
 *
 * @var string Full backup directory path e.g. `/tmp/`
 */
define_by_env( 'BACKUP_DIR', false );

/**
 * OPTIONAL
 * Whether to invoke composer after the repository is cloned or changes are
 * fetched. Composer needs to be available on the server machine, installed
 * globaly (as `composer`). See http://getcomposer.org/doc/00-intro.md#globally
 *
 * @var boolean Whether to use composer or not
 * @link http://getcomposer.org/
 */
define_by_env( 'USE_COMPOSER', true );

/**
 * OPTIONAL
 * The options that the composer is going to use.
 *
 * @var string Composer options
 * @link http://getcomposer.org/doc/03-cli.md#install
 */
define_by_env( 'COMPOSER_OPTIONS', '--no-dev' );

/**
 * OPTIONAL
 * The COMPOSER_HOME environment variable is needed only if the script is
 * executed by a system user that has no HOME defined, e.g. `www-data`.
 *
 * @var string Path to the COMPOSER_HOME e.g. `/tmp/composer`
 * @link https://getcomposer.org/doc/03-cli.md#composer-home
 */
define_by_env( 'COMPOSER_HOME', false );

/**
 * OPTIONAL
 * Email address to be notified on deployment failure.
 *
 * @var string Email address
 */
define_by_env( 'EMAIL_ON_ERROR', false );
