<?php 
/**
 * User Options Page Content
 *
 * (1) Handle all user-account options configuring
 *
 * @package regular_board
 */
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( isset ( $_POST['options'] ) ) {
	include ( plugin_dir_path(__FILE__) . '/regular_board_user_options_form_action.php' );
} ?>
<?php 
/** Begin User Options Form
 ** This form will allow the user to set certain aspects of their account.
 */ ?>
<div id="reply" class="reply">
	<form method="post" name="regularboard" action="<?php echo $current_page; ?>?a=options">
	<?php echo wp_nonce_field( 'regularboard' ); ?>
	<?php 
	/** Begin User Options Form Elements
	 ** Allows the user to set certain options for their account.
	 ** (1) User Avatar
	 ** (2) User Username
	 ** (3) User Password
	 ** (4) User Board Subscription
	 ** (5) User Following
	 ** (6) User Slogan
	 ** (7) User Always Anonymous
	 */
	/** (1) Begin User avatar
	 ** Allow the user to set an avatar image to display on their public profile.
	 ** ( maybe set up a thumbnailing ability to grab the image and create a smaller 
	 ** ( version of it server-side for faster loading for larger images? )
	 */
	if ( $profile_email ) {
	?>
	<section class="profile-section">
		<label class="small-left" for="avatar">
			<u>user photo</u>
			<hr />
			set an image for your user profile.<br />
			<strong>must</strong> be a valid image.<br />
			( .jpg, .png, .gif )
		</label>
		<?php if ( $profileavatar ) { ?>
			<img class="thumb right" src="<?php echo $profileavatar; ?>" alt="profile image" />
		<?php } else { ?>
			<i class="fa fa-picture-o"></i>
		<?php }?>
		<input type="text" name="avatar" id="avatar" value="<?php echo $profileavatar; ?>" />
	</section>
	<?php 
	}
	/** End User Avatar
	 */
	/** Begin User Username
	 ** To allow the user to log back in, the user must have a username and password
	 ** If the user neglected to sign up with the traditional method (using the quick
	 ** button, then they will need to be able to set their username at some point should 
	 ** they want to continue using their existing account.
	 */
	if ( !$profile_email ) { ?>
		<section class="profile-section">
			<label class="small-left" for="email">
				<u>username</u>
				<hr />
				set a username with which to log back in with.<br />
				<strong>don't forget to set a password as well.</strong><br />
				you will only need to do this once.
			</label>
			<i class="fa fa-lock"></i>
			<input type="text" name="email" id="email" />
		</section>
	<?php }
	/** End User Username
	 */
	/** Begin User Password
	 ** Allow the user to set a password.  If the user has already set a password, 
	 ** require that they enter their previous password as well as a new password 
	 ** to update the password that is already set.
	 */
	/** If no password has been set before
	 */
	if ( !$profilepassword ) { ?>
		<section class="profile-section">
			<label class="small-left" for="password">
				<u>password</u>
				<hr />
				set a password so you can log back in.<br />
				password also needed for post editing and deletion.
			</label>
			<i class="fa fa-key"></i>
			<input type="text" name="password" id="password" placeholder="<?php echo $random_password; ?>" />
		</section>
	<?php }
	/** If a password has been set
	 */
	if ( $profilepassword ) { ?>
		<section class="profile-section">
			<label class="small-left">
				<u>change password</u>
				<hr />
				&mdash; enter your current password first<br />
				&mdash; enter your new password second<br />
				save your profile to update your password.
			</label>
			<i class="fa fa-key"></i>
			<input type="text" name="oldpassword" id="oldpassword" placeholder="Enter current password" />
			<input type="text" name="newpassword" id="newpassword" placeholder="Enter new password" />
		</section>
	<?php }
	/** End User Password
	 */
	/** Begin User Display Name
	 ** Allow the user to set a name that they wish to be displayed with their posts,
	 ** and that they wish to be publicly known by (all connection requests and follows
	 ** will depend on this name, however, should the user change it, all occurrences of
	 ** the name in the database will also be changed to reflect that.
	 */ 
	if ( $profile_email ) { ?>	
	<section class="profile-section">
		<label class="small-left" for="USERNAME">
			<u>display name</u>
			<hr />
			set a name that you would like to be known by.<br />
			names are unique &mdash; choose wisely.<br />
			you can change this name at any time.
		</label>
		<i class="fa fa-user"></i>
		<input type="text" name="USERNAME" id="USERNAME" placeholder="Your memorable name" 
		<?php if ( $profile_name != 'null' && $profile_name ) { ?>
			value = "<?php echo $profile_name; ?>"
		<?php } ?>
		/>
	</section>
	<?php 
	}
	/** End User Display Name
	 */
	/** Begin User Board Subscription
	 ** If use boards is set as such, and there are boards created, 
	 ** this option will be available to the user, allowing them to 
	 ** designate a comma-separated list of boards to which they wish 
	 ** to be subscribed.
	 */
	if ( $profile_email ) { 
		if ( $protocol == 'boards' ) {
			if ( count ( $getboards ) > 0 ) {
				if ( !$thisboard ) { ?>
				<section class="profile-section">
						<label class="small-left" for="boards">
							<u>subscribe to boards</u>
							<hr />
							comma-separated list of boards to subscribe to<br />
							example: <strong>board_1, board_2, board_3</strong><br />
							see your feed at <a href="<?php echo $this_page; ?>?a=subscribed">this link</a>
						</label>
						<i class="fa fa-sitemap"></i>
						<input type="text" name="boards" id="boards" value="<?php echo $boards; ?>" placeholder="Boards" />
					</section>
				<?php }
			}
		}
	}
	/** End User Board Subscription
	 */
	/** Begin User Following
	 ** Allow the user to designate a comma-separated list of usernames that they wish to 
	 ** follow, which acts like the subscribed list, but instead outputs a feed of 
	 ** specific user-generated content.
	 */ 
	if ( $profile_email ) { ?>
	<section class="profile-section">
		<label class="small-left" for="follow">
			<u>follow other users</u>
			<hr />
			comma-separated list of usernames to follow<br />
			example: <strong>user_1, user_2, user_3</strong><br />
			see your followed feed at <a href="<?php echo $this_page; ?>?a=following">this link</a>
		</label>
		<i class="fa fa-group"></i>
		<input type="text" name="follow" id="follow" value="<?php echo $profilefollow; ?>" placeholder="Usernames" />
	</section>
	<?php 
	}
	/** End User Following
	 */
	/** Begin User Slogan
	 ** Allow the user to affix a line of text to their public profile.
	 */ 
	if ( $profile_email ) {  ?>
	<section class="profile-section">
		<label class="small-left" for="slogan">
			<u>profile slogan</u>
			<hr />
			a line of text for your public profile<br />
			example: a quote, pickup line, or anecdote
		</label>
		<i class="fa fa-microphone"></i>
		<input type="text" name="slogan" id="slogan" value="<?php echo $profileslogan; ?>" />
	</section>
	<?php 
	}
	/** End User Slogan
	 */
	/** Begin User Always Anonymous
	 ** Allow the user to determine whether or not they always wish to post anonymously, 
	 ** which will prevent any of their posts from being publicly tied to their profile.
	 */ 
	if ( $profile_email ) {  ?>
	<section class="profile-section">
		<label class="small-left">
			<u>always anonymous</u>
			<hr />
			post anonymously?<br />
			anonymous posts are not tied to your public profile<br />
			can be turned on/off at your leisure
		</label>
		<i class="fa fa-volume-off"></i>
		<select name="heaven" id="heaven">
			<option <?php if ( $profileheaven == 0 ){ ?> selected="selected" <?php } ?> value="0">no</option>
			<option <?php if ( $profileheaven == 1 ){ ?> selected="selected" <?php } ?> value="1">yes</option>
		</select>
	</section>
	<?php 
	}
	/** End User Always Anonymous
	 */ ?>
		<input type="submit" name="options" id="options" value="Save these options" />
	</form>
	<?php 
	/** End User Options Form
	 */ ?>
		
	<?php 
	/** Begin Connections
	 ** If the user has incoming connections, they will be displayed here.
	 ** They can then decide to either decline or accept the connection invitation.
	 */
	if ( count ( $my_waiting ) > 0 ) {
		foreach ( $my_waiting as $waiting ) {
			$this_form = $waiting->friends_id;
			if ( isset ( $_POST['accept' . $this_form . ''] ) ) {
				$wpdb->query ( "UPDATE $regular_board_friends SET friends_mutual = 1 WHERE friends_id = $this_form" );
				echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=options"></p>';
			}
			if ( isset ( $_POST['decline' . $this_form . ''] ) ) {
				$wpdb->delete ( $regular_board_friends, array ( 'friends_id' => $this_form ), array ( '%d' ) );
				echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=options"></p>';
			} ?>
			<form class="friend_request" method="post" action="<?php echo $current_page; ?>?a=options">
			<section>
				<label>
					<?php echo sanitize_text_field ( $waiting->friends_connector ); ?> wants to connect
				</label>
				<input type="submit" name="decline<?php echo $waiting->friends_id; ?>" value="Decline" />
				<input type="submit" name="accept<?php echo $waiting->friends_id; ?>" value="Accept" />
			</section>
			</form>
		<?php }
	} ?>
	<?php 
	/** End Connections
	 */ ?>
</div>
<script type="text/javascript">
	document.title = 'Options';
</script>