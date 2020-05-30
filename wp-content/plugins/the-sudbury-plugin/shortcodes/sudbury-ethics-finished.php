<?php

/**
 * Shortcodes for hooking into the start and the end of the Ethics Exam
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Ethics_Exam {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'scripts' ), 999 );
	}

	function init() {
		add_shortcode( 'ethics-finish', array( &$this, 'finish' ) );
		add_shortcode( 'ethics-start', array( &$this, 'start' ) );

	}

	function scripts() {
		wp_register_script( 'jquery-cookie', get_template_directory_uri() . '/js/jquery.cookie.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-cookie' );
	}

	function start() {

		?>

		<p>
		<span style="line-height: 1.5">Thank you for taking the time to to complete this training as required by State Law.<br />
		Please follow all of the steps to ensure your Certificate of Completion is recorded at the Town Clerk's Office.</span>
		</p>
		<h2>
			Step 1:</h2>
		<p>
			If you <span style="text-decoration: underline;">have not</span> received
			<strong>The Summary of the Conflict of Interest Law</strong>, please fill out the form at:
			<strong><a href="http://sudbury.ma.us/ethics/">http://sudbury.ma.us/ethics/</a></strong></p>
		<h2>
			Step 2:</h2>
		<p>
			Please Enter your <strong>Name</strong>, <strong>Email</strong>, <strong>Title/Committee</strong>, and
			<strong>Work&nbsp;Location</strong>&nbsp;below and then click &quot;<span style="color: #008000"><strong>Start Training</strong></span>&quot;
		</p>
		<form action="#" id="exam-form" method="post">
			<label for="ethics-username">Your Name: </label><br />
			<input id="ethics-username" placeholder="Required*" style="width:300px;" type="text" />

			<p>
				&nbsp;*This name will appear on your certificate</p>

			<p>
				<label for="ethics-email">Your Email: </label><br />
				<input id="ethics-email" placeholder="Required*" style="width:300px;" type="text" /></p>

			<p>
				<label for="ethics-title">Your Job Title&nbsp;or Committee Name: </label><br />
				<input id="ethics-title" placeholder="Required*" style="width:300px;" type="text" /></p>

			<p>
				<label for="ethics-location">Your Work Location (Town/School Employees Only): </label><br />
				<input id="ethics-location" placeholder="Required (Employees only)*" style="width:300px;" type="text" /></p>

			<p>
				&nbsp;</p>

			<p>
				<input id="examStart" type="submit" value="Start Training" /></p>
		</form>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$("#exam-form").submit(function (e) {
					e.preventDefault();
					var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;


					if ($("#ethics-username").val() == "") {
						alert("Please Enter Your Name.")
					}
					else if (!re.test($("#ethics-email").val())) {
						alert("Please enter a valid email address");
					}
					else if ($("#ethics-title").val() == "") {
						alert("Please enter a valid title or comittee name");
					} else {

						$.cookie("ethics-username", $("#ethics-username").val(), {expires: 7, path: '/'});
						$.cookie("ethics-email", $("#ethics-email").val(), {expires: 7, path: '/'});
						$.cookie("ethics-title", $("#ethics-title").val(), {expires: 7, path: '/'});
						$.cookie("ethics-location", $("#ethics-location").val(), {expires: 7, path: '/'});

						alert("Thanks!  You will now be directed to the training at http://sudbury.ma.us/ethics-exam/")
						$("#exam-form").append('<p>If you have not been redirected then <a href="http://sudbury.ma.us/ethics-exam/">click here</a></p>')
						window.location.href = "http://sudbury.ma.us/ethics-exam/";
					}
				});
			});
		</script>

	<?php

	}


	function finish() {
		$today = date( "m/d/Y g:i:s A" );

		$username = $_COOKIE['ethics-username'];
		$email    = $_COOKIE['ethics-email'];
		$title    = $_COOKIE['ethics-title'];
		$location = $_COOKIE['ethics-location'];


		if ( ! $username ) {
			?>
			<div class="space"></div>
			<h2 style="vertical-align:middle;">
				<img src="<?php bloginfo( 'stylesheet_directory' ); ?>/lib/img/dialog-important-2.png" alt="That's and error">&nbsp;&nbsp;406 - Not Acceptable
			</h2>
			<p>
				Error username not found, Please check that you have enabled cookies. <p>&nbsp;</p><br />
			<b>What to do now?</b><br />
			<br />Please click the back button in your browser and print your certificate manually
			</p>
			<?php
			return;
		}


		$head = "<html><head><style type='text/css'>.bodytxt { font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-style: normal; font-weight: normal; color: #003366; } .answers { font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-style: italic; font-weight: normal; font-variant: normal; color: #003366; } .answers2 { font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-style: normal; font-weight: bold; color: #CC9900; } .numb { font-family: Arial, Helvetica, sans-serif; font-size: 10px; font-style: normal; font-weight: normal; color: #999999; letter-spacing: normal; } .numb1 { font-family: Arial, Helvetica, sans-serif; font-size: 10px; font-style: normal; font-weight: bold; color: #003366; } .answers3 { font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-style: normal; font-weight: bold; font-variant: normal; color: #000066; } .numb2 { font-family: Arial, Helvetica, sans-serif; font-size: 10px; font-style: normal; line-height: normal; font-weight: normal; color: #CCCCCC; } .bodytxtlink { font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-style: normal; line-height: normal; font-weight: bold; color: #CC9900; text-decoration: underline; } .bodytxtlinkinst { font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-style: normal; line-height: normal; font-weight: bold; color: #003366; text-decoration: underline; } .highlight { font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-weight: bold; color: #CC9900; } .highlight2 { font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: bold; color: #CC9900; } .bodytxtlinkbig { font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: bold; color: #CC9900; text-decoration: underline; } .bigtitle { font-family: Arial, Helvetica, sans-serif; font-size: 24px; font-weight: bold; color: #003366; } </style></head><body>";
		$foot = "</body></html>";
		$body = "<div align='center' class='table-no-border white-bg'><table width='464' border='0' cellpadding='0' cellspacing='0'><!--DWLayoutTable-->  <tr>    <td height='56' align='center' valign='top' class='bigtitle'><div align='center'><br>      STATE ETHICS COMMISSION    RECEIPT</div></td>  </tr></table> <table> <tr>    <td width='21' valign='top'><img src='http://sudbury.ma.us/ethics/images/A.gif' width='21' height='42' /></td>  <td height='14' valign='top'><img src='http://sudbury.ma.us/ethics/images/linOrg.gif' width='443' height='14' /></td>  </tr> </table><table> <tr>    <td height='52' align='center' valign='top' class='answers3'><br>     Conflict of Interest Law Online Training Program<br><br></td>  </tr>  <tr>    <td height='38' align='center' valign='top'> <label>        <span class='answers3'>was completed by: " . $username . ", " . $title . ", " . $location . "        </span><br><br>        </label>      <span class='answers3'>on</span><br>    </form></td>  </tr>  <tr>    <td height='34' align='center' valign='top'><small><font color='003366' face='Arial' size='3'><b>" . date( 'F j, Y, g:i a', current_time( 'timestamp' ) ) . "</b></font></small></td>  </tr> </table><table> <tr>    <td height='25' align='right' valign='top'><img src='http://sudbury.ma.us/ethics/images/btmBluline.gif' width='440' height='25' /></td>    <td width='16' valign='top'><img src='http://sudbury.ma.us/ethics/images/btmBlu.gif' alt='box' width='16' height='25' /></td></tr></table></div>";
		// The message

		$subject = 'Ethics Training Certificate: ' . $username . ", " . $title . ", " . $location;
		$message = $head . $body . $foot;

		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'From: ethics@sudbury.ma.us' . "\r\n" . 'Reply-To: webmaster@sudbury.ma.us' . "\r\n";


		echo $message;
// Send Email
		if ( ! wp_mail( 'townclerk@sudbury.ma.us,thompsonm@sudbury.ma.us,hurtige@sudbury.ma.us,' . $email, $subject, $message, $headers ) ) {
			echo "MAIL SYSTEM OFFLINE - print this certificate as proof of completion";
		}

		$conn = mysqli_connect( ETHICS_DB_HOST, ETHICS_DB_USER, ETHICS_DB_PASSWORD, ETHICS_DB_NAME );

		$res = mysqli_query( $conn, "INSERT INTO `ethics`.`ethics` (`id`, `name`, `email`, `committee`, `location`, `datetime`) VALUES (NULL, '" . esc_sql( $username ) . "', '" . esc_sql( $email ) . "' , '" . esc_sql( $title ) . "'  , '" . esc_sql( $location ) . "', '" . current_time( 'mysql' ) . "' )" );
		?> <p style="text-align:center;"> <?php 
		if ( $res === false ) {
			?>
			An error occured when submitting this training to the permanant recordi. Please print this page and keep for your records.
		<?php } else { ?>
 			This training has been permanently recordedi.  Thank you!
		<?php } ?>
		</p> <?php
		/* Remove the cookies
		setcookie("ethics-username", "", time()-3600);
		setcookie("ethics-email", "", time()-3600);
		setcookie("ethics-location", "", time()-3600);
		setcookie("ethics-title", "", time()-3600);
		*/
	}

}

new Ethics_Exam();
