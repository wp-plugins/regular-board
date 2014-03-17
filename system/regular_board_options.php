<?php 

/**
 * Regular Board Options
 *
 * (1) Display the options for Regular Board on the admin settings page,
 * (1) including bans management, options configuration, and board creation/editing.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
function regular_board_admin_css( $hook ){
	if( 'settings_page_regular_board' != $hook )
	return;
	wp_register_style ( 'regular_board_css', plugins_url() . '/' . plugin_basename(dirname(__FILE__)) . '/css/regular_board_admin.css' );
	wp_enqueue_style ( 'regular_board_css' );
}	
if( current_user_can( 'manage_options' )) {
	add_action('admin_menu','regular_board_options_page');
	function regular_board_options_page() {
		add_options_page('Regular Board','Regular Board','manage_options','regular_board','regular_board_options_page_content'); 
	}
	function regular_board_options_page_content() {
		
		global $wpdb;
		$regular_board_posts   = $wpdb->prefix . 'regular_board_posts';
		$regular_board_boards  = $wpdb->prefix . 'regular_board_boards';
		$regular_board_users   = $wpdb->prefix . 'regular_board_users';
		$regular_board_bans    = $wpdb->prefix . 'regular_board_bans';
		$regular_board_reports = $wpdb->prefix . 'regular_board_reports';
		$regular_board_logs    = $wpdb->prefix . 'regular_board_logs';		
		$date                  = date('Y-m-d H:i:s');
		
		if ( intval ( get_option ( 'regular_board_installation' ) ) == 0 ) {
			if ( isset ( $_POST['install'] ) ) {
				update_option ( 'regular_board_installation', '1' );
				regular_board_installation();
			} else {
				echo '
				<div id="regular_board_options">
					<div>
						<p>
							Regular Board Standalone Edition (<em>Regular Board++</em>)<br />
							Brought to you by <em>The Regular Board Development Team</em>
						</p>
					</div>
					<div>
						<p>
							Regular Board is a light-weight WordPress powered anonymous forum,
							designed to be easily deployed on any WordPress installation with 
							little-to-no setup or necessary knowledge with programming.
						</p>
					</div>
					<div>
						<p>
							Regular Board is <strong>beta</strong> software, meaning that while 
							we do our best to ensure that it is bug free and works as intended, 
							we acknowledge that there may be bugs in the software.  Your use of 
							the software indicates that you are aware of this fact, and will 
							not hold the creators of this software liable for any damages that your 
							WordPress installation may incur from the use of this software, or from 
							your modification to the code of this plugin.
						</p>					
					</div>
					<div>
						<p>Copyright (C) 2014 Regular Board Development Group</p>
						<p>Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:</p>
						<p>The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.</p>
						<p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</p>
					</div>
					<div>
						<p>
							I have read and understand the above agreement, and agree completely with what is stated.
							By clicking on <em>Proceed with installation</em>, I hereby grant <em>Regular Board</em> 
							express permission to be installed onto this WordPress installation.
						</p>
						<form method="post"><input type="submit" name="install" value="Proceed with installation." /></form>
					</div>
				</div>';
			}
		}
		if ( intval ( get_option ( 'regular_board_installation' ) ) == 1 ) {
		
			/**
			 * Settings navigation to set focus on different areas of admin
			 * screen for Regular Board.
			 */
		
			if(isset($_REQUEST['regular_board_focus'])){
				update_option ( 'regular_board_focus', $_REQUEST['regular_board_focus'] );
			}
			
			echo '<div id="regular_board_options">
					<div>
						<h1>Regular Board</h1>
					</div>
					<div>
						<form method="post">
							<section>
								<label>
									Admin sections
								</label>
								<select name="regular_board_focus" onchange="this.form.submit()">
									<option value="">Admin sections</option>
									<option value="info">How-to / Information</option>
									<option value="bans">Add / manage bans</option>
									<option value="boards">Create / Delete / Edit a board</option>
									<option value="options">Installation options</option>
								</select>
							</section>
						</form>
					</div>';
				
			if ( get_option ( 'regular_board_focus' ) == 'nothing' ) {
			
			}
			
			/**
			 * Information and how-to for Regular Board
			 */
			if ( get_option ( 'regular_board_focus' ) == 'info' ) {
				echo '<div>
					<p><strong>Child templating</strong> is easy:</p>
					<p>
						01: Create a folder in your root WordPress install.<br />
						02: Name the folder <code>regular_board_child</code>.<br />
						03: Copy the appropriate files <strong>from</strong> the Regular Board plugin folder <strong>to</strong> this new folder.<br />
						04: Edit them.
					</p>
					<p> <em>Supported files for templating:</em><br /> 
						<code>[1] regular_board_loop.php</code>
						<code>[1] regular_board_post_form.php</code>
					</p>
					<p> <strong>Be careful!</strong>: If you don\'t know what you\'re doing, it is quite possible that you can break 
					the functionality of this plugin with a child template.  Always make sure you have some solid grasp 
					on what the code is, and what it is that you are editing (or adding) before doing so.</p>
				</div>';
			}
			
			/**
			 * Create/edit a board
			 */
			if ( get_option ( 'regular_board_focus' ) == 'boards' ) { 

				$grabBoards = $wpdb->get_results ( "SELECT * FROM $regular_board_boards WHERE board_shortname != ''" );
				
				$editBoard          = '';
				
				if(isset($_REQUEST['regular_board_edit'])){
					$editBoard = $wpdb->get_results ( "SELECT * FROM $regular_board_boards WHERE board_shortname = '" . $_REQUEST['regular_board_edit'] . "'" );	
				}
				
				$board_name         = '';
				$board_shortname    = '';
				$board_description  = '';
				$board_moderators   = '';
				$board_janitors     = '';
				$board_locked       = '';
				$board_logged       = '';
				$board_wipe         = '';
				
				if ( $editBoard ) {
					if ( count ( $editBoard ) > 0 ) {
						foreach ( $editBoard as $edit ) { 
							$board_name         = $edit->board_name;
							$board_shortname    = $edit->board_shortname;
							$board_description  = $edit->board_description;
							$board_moderators   = $edit->board_mods;
							$board_janitors     = $edit->board_janitors;
							$board_locked       = $edit->board_locked;
							$board_logged       = $edit->board_logged;
							$board_wipe         = $edit->board_wipe;
						}
					}
				}
				
				echo '<div>
					<form method="post">
						<section>
							<label>
								Edit a board
							</label>
							<select name="regular_board_edit" onchange="this.form.submit()">
								<option value="">Edit a board</option>';
									foreach ( $grabBoards as $grabbed ) {
										echo '<option value="' . $grabbed->board_shortname . '">' . $grabbed->board_shortname . ' / ' . $grabbed->board_name . '</option>';
									}
								echo '</select>
						</section>
					</form>
				</div>';
			
				if ( isset ( $_POST['save_newboard'] ) && $_REQUEST['board_shortname'] ) {
					$regular_board_board = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_boards WHERE board_shortname = '" . $_REQUEST['board_shortname'] . "'" );
					if ( $regular_board_board == 0 ) {
						$wpdb->query( $wpdb->prepare("INSERT INTO $regular_board_boards ( board_id,  board_date,  board_name,  board_shortname,  board_description,  board_mods,  board_janitors,  board_postcount, board_locked, board_logged, board_wipe ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s ) ", '', $date, str_replace('\\','',$_REQUEST['board_name']), str_replace('\\','',$_REQUEST['board_shortname']), str_replace('\\','',$_REQUEST['board_description']), str_replace('\\', '', $_REQUEST['board_mods']), str_replace('\\','',$_REQUEST['board_janitors']), 0, str_replace('\\','',$_REQUEST['board_locked']), str_replace('\\','',$_REQUEST['board_logged']), str_replace('\\','',$_REQUEST['board_wipe']) ) );
						echo '<div><section><label>' . $_REQUEST['board_name'] . ' added.</label></section></div>';
					} else {
						$wpdb->delete ( $regular_board_boards, array ( 'board_shortname' => $_REQUEST['board_shortname'] ), array ( '%s' ) );
						$wpdb->query( $wpdb->prepare("INSERT INTO $regular_board_boards ( board_id,  board_date,  board_name,  board_shortname,  board_description,  board_mods,  board_janitors,  board_postcount, board_locked, board_logged, board_wipe ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s ) ", '', $date, str_replace('\\','',$_REQUEST['board_name']), str_replace('\\','',$_REQUEST['board_shortname']), str_replace('\\','',$_REQUEST['board_description']), str_replace('\\', '', $_REQUEST['board_mods']), str_replace('\\','',$_REQUEST['board_janitors']), 0, str_replace('\\','',$_REQUEST['board_locked']), str_replace('\\','',$_REQUEST['board_logged']), str_replace('\\','',$_REQUEST['board_wipe']) ) );
						echo '<div><section><label>' . $_REQUEST['board_name'] . ' Updated.</label></section></div>';
					}
				}
				echo '<div><form method="post">
						<section><label>Board name: </label><input name="board_name" type="text" value="' . $board_name . '"/></section>
						<section><label>Board shortname: </label><input name="board_shortname" type="text" value="' . $board_shortname . '"/></section>
						<section><label>Board description: </label><input name="board_description" type="text" value="' . $board_description . '"/></section>
						<section><label>Board moderators: </label><textarea name="board_mods" />' . $board_moderators . '</textarea></section>
						<section><label>Board janitors: </label><textarea name="board_janitors" />' . $board_janitors . '</textarea></section>
						<section><label>This board is locked: </label><select name="board_locked" /><option value="0"'; if ( $board_locked == 0 ) { echo ' selected="selected"'; } echo '>No.</option><option value="1"'; if ( $board_locked == 1 ) { echo ' selected="selected"'; } echo '>Yes.</option></select></section>
						<section><label>Must be logged in: </label><select name="board_logged" /><option value="0"'; if ( $board_logged == 0 ) { echo ' selected="selected"'; } echo '>No.</option><option value="1"'; if ( $board_logged == 1 ) { echo ' selected="selected"'; } echo '>Yes.</option></select></section>
						<section><label>Wipe this board every...: </label><input name="board_wipe" type="text" value="' . $board_wipe . '"/></section>
					<section><input type="submit" name="save_newboard" value="'; if ( $board_name ) { echo 'Edit'; } else { echo 'Create'; } echo ' this board" /></section>
				</form></div>';
				
				echo '<div>
					<p><label>Information (for board creation)</label></p>
					<p><label>Board name: The (long) form of a board name.</label></p>
					<p><label>Board shortname: The (short) form of a board name (the name used to reach the board via URL).</label></p>
					<p><label>Board description: A (short) description about the board.</label></p>
					<p><label>Board moderators: A (comma-separated) list of WordPress usernames OR user ids (from Regular Board _users) who can perform moderator actions on threads and replies.</label></p>
					<p><label>Board janitors: A (comma-separated) list of WordPress usernames OR user ids (from Regular Board _users) who can delete threads and replies.</label></p>
					<p><label>This board is locked: Lock the board, allowing only the WordPress admin account(s) to post.</label></p>
					<p><label>Must be logged in: Users must be logged into the WordPress installation to interact with the board.</label></p>
					<p><label>Wipe this board every...: (Format: (amount) (length: second, minute, hour, day, week, month, year) - completely wipe the board every (length) and start fresh.  (Example: 1 day will wipe that particular board clear every day, while 3 hour (or hours) will wipe it clean every 3 hours.)</label></p>
				</div>';

				if ( isset ( $_POST['delete_this_board'] ) && $_REQUEST['board_delete'] ) {
						$wpdb->delete ( $regular_board_boards, array ( 'board_shortname' => $_REQUEST['board_delete'] ), array ( '%s' ) );
					if ( $_REQUEST['assign_posts'] != 'regular_board_delete_these_posts' ) {
						$wpdb->query ( "UPDATE $regular_board_posts SET post_board = '" . $_REQUEST['assign_posts'] . "' WHERE post_board = '" . $_REQUEST['board_delete'] . "'" );
					} else {
						$wpdb->delete ( $regular_board_posts, array ( 'post_board' => $_REQUEST['board_delete'] ), array ( '%s' ) );
					}
				}
				
				$getboards = $wpdb->get_results ( "SELECT * FROM $regular_board_boards" );
				
				echo '<div><form method="post">
						<section><label>Enter a board (short)name to delete it</label><select name="board_delete">';
						foreach ($getboards as $boards ) {
							echo '<option value="' . $boards->board_shortname . '">' . $boards->board_shortname . ' / ' . $boards->board_name . '</option>';
						}
						echo '</select></section>
						<section><label>Assign posts to this board</label><select name="assign_posts">
						<option value="regular_board_delete_these_posts">Move these posts to...</option>
						<option value="regular_board_delete_these_posts">Don\'t move them anywhere - delete them.</option>';
						foreach ($getboards as $boards ) {
							echo '<option value="' . $boards->board_shortname . '">' . $boards->board_shortname . ' / ' . $boards->board_name . '</option>';
						}						
						echo '</select></section>
						<section><input type="submit" name="delete_this_board" value="Delete this board" /></section>
						</form>';
			
				$regular_board_mainrules = $wpdb->get_var( "SELECT board_rules FROM $regular_board_boards WHERE board_shortname = 'mainrules'" );
				$regular_board_rules     = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_boards WHERE board_shortname = 'mainrules'" );
			}
			
			/**
			 * Add/manage bans
			 */
			if ( get_option ( 'regular_board_focus' ) == 'bans' ) {
				
				$getusers = $wpdb->get_results ( "SELECT * FROM $regular_board_bans" );
				
				if(isset($_POST['BAN']) && $_REQUEST['IP'] != ''){
					$ip = sanitize_text_field ( wp_hash ( $_REQUEST['IP'] ) );
					$message = ' (Banned by admin).';
					$length  = 0;
					$wpdb->query($wpdb->prepare("INSERT INTO $regular_board_bans 
						( 
							banned_id,
							banned_date,
							banned_ip,
							banned_banned,
							banned_message,
							banned_length
						) 
						VALUES ( 
							%d,
							%s,
							%s,
							%d,
							%s,
							%d
						)",
							'',
							$date,
							$ip,
							1,
							$message,
							$length
						)
					);
					
				}
				echo '<div>
					<form method="post">
						<section><label>Ban an IP address</label><input type="text" name="IP" id="IP" placeholder="IP Address to ban (standard format or long format)" /></section>
						<section><input type="submit" name="BAN" value="Ban this IP" /></section>
					</form>
				</div>';
				
				echo '<div>
					<form method="post">';
					if ( count ( $getusers ) > 0 ) {
						foreach ( $getusers as $bans ) {
							echo '<section><label>';
							if ( $bans->banned_message ) {
								echo $bans->banned_message;
							} else { 
								echo 'No ban reason given.';
							}
							echo '</label><input type="submit" value="Unban ' . $bans->banned_ip . '?" name="unban' . $bans->banned_id . '" /></section>';
							if ( isset ( $_POST['unban' . $bans->banned_id] ) ) {
								$wpdb->delete ( $regular_board_bans, array ( 'banned_id' => $bans->banned_id), array ( '%d' ) );
								echo '<section><label>Unbanned.</label></section>';
							}
						}
					}else{
						echo '<section><label>No bans (yet) - great!</label></section>';
					}
				echo '</form>
				</div>';
			}
			
			/**
			 * Options for Regular Board.
			 */
			if ( get_option ( 'regular_board_focus' ) == 'options' ) { 
				if ( isset ( $_POST['uninstall'] ) ) {
					delete_option ( 'regular_board_installation' );
					require_once ( 'regular_board_uninstallation.php' );
					regular_board_uninstallation();
				} elseif ( isset ( $_POST['reinstall'] ) ) {
					$wpdb->query ( "DROP TABLE $regular_board_posts" );
					$posts = "CREATE TABLE $regular_board_posts(
					post_id BIGINT(20) NOT NULL AUTO_INCREMENT , 
					post_parent BIGINT(20) NOT NULL ,
					post_name TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_date TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_email TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_title TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_comment LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_type TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_url TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_board TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_moderator TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_last TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_sticky TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_locked TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_password TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_userid BIGINT(20) NOT NULL , 
					post_public BIGINT(20) NOT NULL ,
					post_report TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
					post_reportcount BIGINT(20) NOT NULL ,
					PRIMARY KEY  (post_id)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta ( $posts );					
				} else {

						if ( isset ( $_POST['save'] ) ) {
							update_option ( 'regular_board_ascii', str_replace ( array ('\\', '"' ), '', $_REQUEST['ascii']) );
							update_option ( 'regular_board_announcements', str_replace ( '\\', '', $_REQUEST['announcements'] ) );
							update_option ( 'regular_board_hideannouncements', str_replace ( '\\', '', $_REQUEST['hideannouncements'] ) );
							update_option ( 'regular_board_robots', str_replace ( '\\', '', $_REQUEST['robots'] ) );
							update_option ( 'regular_board_maxlinks', str_replace ( '\\', '', $_REQUEST['maxlinks'] ) );
							update_option ( 'regular_board_css_url', str_replace ( '\\', '', $_REQUEST['cssurl'] ) );
							update_option ( 'regular_board_wipedisplay', str_replace ( '\\', '', $_REQUEST['wipedisplay'] ) );
							update_option ( 'regular_board_enableurl', str_replace ( '\\', '', $_REQUEST['enableurl'] ) );
							update_option ( 'regular_board_enablerep', str_replace ( '\\', '', $_REQUEST['enablerep'] ) );
							update_option ( 'regular_board_maxbody', str_replace ( '\\', '', $_REQUEST['maxbody'] ) );
							update_option ( 'regular_board_maxreplies', str_replace ( '\\', '', $_REQUEST['maxreplies'] ) );
							update_option ( 'regular_board_maxtext', str_replace ( '\\', '', $_REQUEST['maxtext'] ) );
							update_option ( 'regular_board_boards', str_replace ( '\\', '', $_REQUEST['boards'] ) );
							update_option ( 'regular_board_userflood', str_replace ( '\\', '', $_REQUEST['userflood'] ) );
							update_option ( 'regular_board_imgurid', str_replace ( '\\', '', $_REQUEST['imgurid'] ) );
							update_option ( 'regular_board_dnsbl', str_replace ( '\\', '', $_REQUEST['dnsbl'] ) );
							update_option ( 'regular_board_modcode', str_replace ( '\\', '', $_REQUEST['modcode'] ) );
							update_option ( 'regular_board_usermodcode', str_replace ( '\\', '', $_REQUEST['usermodcode'] ) );					
							update_option ( 'regular_board_postsper', str_replace ( '\\', '', $_REQUEST['postsper'] ) );
							update_option ( 'regular_board_roll', str_replace ( '\\', '', $_REQUEST['roll'] ) );
							update_option ( 'regular_board_floodgate', str_replace ( '\\', '', $_REQUEST['floodgate'] ) );
							update_option ( 'regular_board_archivegate', str_replace ( '\\', '', $_REQUEST['archivegate'] ) );
							update_option ( 'regular_board_ids', str_replace ( '\\', '', $_REQUEST['ids'] ) );
							update_option ( 'regular_board_displayboards', str_replace ( '\\', '', $_REQUEST['displayboards'] ) );
							update_option ( 'regular_board_displaymenu', str_replace ( '\\', '', $_REQUEST['displaymenu'] ) );
							update_option ( 'regular_board_search', str_replace ( '\\', '', $_REQUEST['search'] ) );
							update_option ( 'regular_board_postingoptions', str_replace ( '\\', '', $_REQUEST['postingoptions'] ) );
							update_option ( 'regular_board_lazyload', str_replace ( '\\', '', $_REQUEST['lazyload'] ) );
							update_option ( 'regular_board_autourl', str_replace ( '\\', '', $_REQUEST['autourl'] ) );
							update_option ( 'regular_board_formatting', str_replace ( '\\', '', $_REQUEST['formatting'] ) );
							update_option ( 'regular_board_frontpage', str_replace ( '\\', '', $_REQUEST['frontpage'] ) );
							update_option ( 'regular_board_bannedimage', str_replace ( '\\', '', $_REQUEST['bannedimage'] ) );
							update_option ( 'regular_board_boardbanner', str_replace ( '\\', '', $_REQUEST['boardbanner'] ) );
							update_option ( 'regular_board_enableblog', str_replace ( '\\', '', $_REQUEST['enableblog'] ) );
							update_option ( 'regular_board_registration', str_replace ( '\\', '', $_REQUEST['registration'] ) );
							update_option ( 'regular_board_accountsper', str_replace ( '\\', '', $_REQUEST['accountsper'] ) );
							update_option ( 'regular_board_totaluserallowed', str_replace ( '\\', '', $_REQUEST['accountstotal'] ) );
						}
						
						function regular_board_enableurl_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_enableurl' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_enableurl' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_enablerep_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_enablerep' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_enablerep' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_wipedisplay_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_wipedisplay' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_wipedisplay' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_iddisplay_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_ids' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_ids' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_search_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_search' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_search' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_postingoptions_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_postingoptions' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_postingoptions' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_robots_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_robots' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_robots' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_lazyload_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_lazyload' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_lazyload' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_hideannouncements_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_hideannouncements' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_hideannouncements' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_autourl_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_autourl' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_autourl' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_formatting_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_formatting' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_formatting' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}
						function regular_board_blog_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_enableblog' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_enableblog' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}						
						function regular_board_wipeper_option() {
							echo '<option value="thread"'; if ( get_option ( 'regular_board_wipeper' ) == strtolower ( 'thread' ) ) { echo ' selected="selected"'; } echo '>Thread</option>';
							echo '<option value="board"'; if ( get_option ( 'regular_board_wipeper' ) == strtolower ( 'board' ) ) { echo ' selected="selected"'; } echo '>Board</option>';
						}
						function regular_board_registration_option() {
							echo '<option value="0"'; if ( get_option ( 'regular_board_registration' ) == 0 ) { echo ' selected="selected"'; } echo '>No</option>';
							echo '<option value="1"'; if ( get_option ( 'regular_board_registration' ) == 1 ) { echo ' selected="selected"'; } echo '>Yes</option>';
						}						
						
						if ( isset ( $_POST['wipesave'] ) ) {
							$current_timestamp = date ( 'Y-m-d H:i:s' );
							update_option ( 'regular_board_wipeall',  str_replace ( '\\', '', $_REQUEST['wipeall' ] ) );
							update_option ( 'regular_board_wipeper', str_replace ( '\\', '', $_REQUEST['wipeper'] ) );
							update_option ( 'regular_board_protected', str_replace ('\\', '', $_REQUEST['protected'] ) );
							update_option ( 'regular_board_wipealldate', $current_timestamp );
						}						
						
						echo '
						<div>
							<p>
								Usage: Deploy your Regular Board installation on a page or post by inserting the shortcode into the post/page: <code>[regular_board]</code>. 
							</p>
						</div>
						<div>
							<form method="post">
								<section><label>00:: Wipe boards on a regular basis?  Never for never, intervals for intervals (example: 1 day for every day)</label><input type="text" name="wipeall" value="' . get_option ( 'regular_board_wipeall' ) . '" /></section>
								<section><label>01:: Per thread or board?</label><select name="wipeper" id="wipeper">'; regular_board_wipeper_option(); echo '</select></section>
								<section><label>02:: Comma-separated list of boards that are protected from wipes</label><textarea name="protected" id="protected">' . get_option ( 'regular_board_protected' ) . '</textarea></section>
								<section><input type="submit" name="wipesave" value="Save wipe settings" /></section>
							</form>
							<form method="post">
								<section><label>00:: Welcome message for front page of boards</label><textarea name="frontpage" id="frontpage">'. get_option ( 'regular_board_frontpage' ) . '</textarea></section>
								<section><label>01:: Enable URL embeds (for new topics):</label><select id="enableurl" name="enableurl">'; regular_board_enableurl_option(); echo '</select></section>
								<section><label>02:: Enable URL embeds (for replies):</label><select id="enablerep" name="enablerep">'; regular_board_enablerep_option(); echo '</select></section>
								<section><label>03:: Max body (comment):</label><input id="maxbody" name="maxbody" type="text" value="' . get_option ( 'regular_board_maxbody' ) . '" /></section>
								<section><label>04:: Max replies (per thread):</label><input id="maxreplies" name="maxreplies" type="text" value="' . get_option ( 'regular_board_maxreplies' ) . '" /></section>
								<section><label>05:: Max text:</label><input id="maxtext" name="maxtext" type="text" value="' . get_option ( 'regular_board_maxtext' ) . '" /></section>
								<section><label>06:: Board list:</label><input id="boards" name="boards" type="text" value="' . get_option ( 'regular_board_boards' ) . '" /></section>
								<section><label>07:: Time between posts (in seconds):</label><input id="floodgate" name="floodgate" type="text" value="' . get_option ( 'regular_board_floodgate' ) . '" /></section>
								<section><label>08:: Time until comments are turned off (in seconds):</label><input id="archivegate" name="archivegate" type="text" value="' . get_option ( 'regular_board_archivegate' ) . '" /></section>
								<section><label>09:: Users who can bypass flood:</label><input id="userflood" name="userflood" type="text" value="' . get_option ( 'regular_board_userflood' ) . '" /></section>
								<section><label>10:: <a href="https://api.imgur.com/oauth2/addclient">imgur.com application id</a>:</label><input id="imgurid" name="imgurid" type="text" value="' . get_option ( 'regular_board_imgurid' ) . '" /></section>
								<section><label>11:: <a href="//www.dnsbl.info/dnsbl-list.php">DNSBL server list</a>:</label><textarea id="dnsbl" name="dnsbl">' . get_option ( 'regular_board_dnsbl' ) . '</textarea></section>
								<section><label>12:: Code to display next to moderator posts:</label><input type="text" id="modcode" name="modcode" value="' . get_option ( 'regular_board_modcode' ) . '" /></section>
								<section><label>13:: Code to display next to user moderator posts:</label><input type="text" id="usermodcode" name="usermodcode" value="' . get_option ( 'regular_board_usermodcode' ) . '" /></section>
								<section><label>14:: Posts per page:</label><input type="text" id="postsper" name="postsper" value="' . get_option ( 'regular_board_postsper' ) . '" /></section>
								<section><label>15:: Range for rolls:</label><input type="text" id="roll" name="roll" value="' . get_option ( 'regular_board_roll' ) . '" /></section>
								<section><label>16:: Display wipe countdown for boards:</label><select name="wipedisplay" id="wipedisplay">'; regular_board_wipedisplay_option(); echo '</select></section>
								<section><label>17:: Display IDs:</label><select name="ids" id="ids">'; regular_board_iddisplay_option(); echo '</select></section>
								<section><label>18:: Auto-link images and urls in comments:</label><select name="autourl" id="autourl">'; regular_board_autourl_option(); echo '</select></section>
								<section><label>19:: Use Regular Board formatting for comments:</label><select name="formatting" id="formatting">'; regular_board_formatting_option(); echo '</select></section>
								<section><label>20:: Display search form:</label><select name="search" id="search">'; regular_board_search_option(); echo '</select></section>
								<section><label>21:: URL to your <em>custom</em> stylesheet (<strong>optional</strong>):</label><input type="text" id="cssurl" name="cssurl" value="' . get_option ( 'regular_board_css_url' ) . '" /></section>
								<section><label>22:: Enable posting options:</label><select name="postingoptions" id="postingoptions">'; regular_board_postingoptions_option(); echo '</select></section>
								<section><label>23:: Maximum amount of links allowed:</label><input type="text" name="maxlinks" id="maxlinks" value="' . get_option ( 'regular_board_maxlinks' ) . '" /></section>
								<section><label>24:: Enable no index, no follow:</label><select name="robots" id="robots">'; regular_board_robots_option(); echo '</select></section>
								<section><label>25:: Enable Lazy Load:</label><select name="lazyload" id="lazyload">'; regular_board_lazyload_option(); echo '</select></section>
								<section><label>26:: Category id for board announcements:</label><input type="text" id="announcements" name="announcements" value="' . get_option ( 'regular_board_announcements' ) . '" /></section>
								<section><label>27:: Hide announcements from the front page of the blog:</label><select name="hideannouncements" id="hideannouncements">'; regular_board_hideannouncements_option(); echo '</select></section>
								<section><label>28:: ASCII for header (completely optional, and completely useless.)</label><textarea name="ascii" id="ascii">' . get_option ( 'regular_board_ascii' ) . '</textarea></section>
								<section><label>29:: Image to show users who are banned.  Useless, really.</label><input type="text" name="bannedimage" id="bannedimage" value="' . get_option ( 'regular_board_bannedimage' ) . '" /></section>
								<section><label>30:: Banner image for boards (300x100 / scales to 150x50 on mobile). (not as) useless, really.</label><input type="text" name="boardbanner" id="boardbanner" value="' . get_option ( 'regular_board_boardbanner' ) . '" /></section>
								<section><label>31:: Enable blog post viewing from Regular Board:</label><select name="enableblog" id="enableblog">'; regular_board_blog_option(); echo '</select></section>
								<section><label>32:: Allow new users to register?:</label><select name="registration" id="registration">'; regular_board_registration_option(); echo '</select></section>
								<section><label>33:: How many accounts per unique IP address can a person have?</label><input type="text" name="accountsper" id="accountsper" value="' . get_option ( 'regular_board_accountsper' ) . '" /></section>
								<section><label>34:: How many accounts (TOTAL) can be registered at one time?</label><input type="text" name="accountstotal" id="accountstotal" value="' . get_option ( 'regular_board_totaluserallowed' ) . '" /></section>
								<section><input type="submit" name="save" value="Save options" /></section>
							</form>
						</div>
						<div>
							<p>Information (for options)</p>
							<p><label for="enableurl">01:: Enable/disable URL embedding for new topics.</label></p>
							<p><label for="enablerep">02:: Enable/disable URL embedding for new replies.</label></p>
							<p><label for="maxbody">03:: Maximum amount of characters user can enter into comment field.</label></p>
							<p><label for="maxreplies">04:: Maximum replies any thread can receive.</label></p>
							<p><label for="maxtext">05:: Maximum amount of characters user can enter in non-comment text fields.</label></p>
							<p><label for="boards">06:: If blank, will list all boards on installation.  Comma-separated list of boards you want to show for installation (by shortname) (example: a,b,c,...).</label></p>
							<p><label for="floodgate">07:: Amount of time (in seconds) between each post a user can make. (default: 10 seconds)</label></p>
							<p><label for="archivegate">08:: Amount of time (in seconds) until comments are turned off for a thread. (default: 2 months (5356800))</label></p>
							<p><label for="userflood">09:: Comma-separated list of WordPress usernames who can bypass the flood-detection.</label></p>
							<p><label for="imgurid">10:: Imgur.com application ID to enable direct-to-imgur uploads.</label></p>
							<p><label for="dnsbl">11:: List of DNSBL servers to check the IP of the user against (must be in this format: \'server\',\'server\',\'server\',...).</label></p>
							<p><label for="modcode">12:: A special code to display next to the name on posts made by moderator (admin) account.</label></p>
							<p><label for="usermodcode">13:: A special code to display next to the name on posts made by user moderators accounts.</label></p>
							<p><label for="postsper">14:: How many threads per page to show.</label></p>
							<p><label for="roll">15:: Range for dice rolls (min,max)</label></p>
							<p><label for="wipedisplay">16:: Display a countdown for a board\'s wipe status (if it exists), alerting users to the time of the next board wipe.</label></p>
							<p><label for="iddisplay">17:: Display IDs next to the names of the people who post. IDs are a hashed combination of the board that the post belongs to and the ID of the user.  IDs are unique to the boards to which they belong.</label></p>
							<p><label for="autourl">18:: Automatically turn links and images in comment into links and an image gallery for the comment.</label></p>
							<p><label for="search">20:: Enable or disable the search function.</label></p>
							<p><label for="cssurl">21:: If you wish to use your own stylesheet (and not the default), enter the URL to it in this box.  (Blank for default)</label></p>
							<p><label for="postingoptions">22:: Enable extra posting options on the post form, like posting anonymously or rolling.</label></p>
							<p><label for="maxlinks">23:: When users post links, they are automatically converted.  This number will determine just how many of those links (and/or images) are returned for display.</label></p>
							<p><label for="robots">24:: Whether or not to add \'no index, no follow\' for robots on any page with the Regular Board shortcode.</label></p>
							<p><label for="lazyload">25:: Whether or not to enable Lazy Load for images on the boards, decreasing load time.</label></p>
							<p><label for="announcements">26:: The category id for which category you will use to post board announcements.</label></p>
							<p><label for="hideannouncements">27:: Whether or not you wish to hide the announcements category posts from showing up on the front page of the blog.</label></p>
							<p><label for="ascii">28:: A completely useless way to add ascii art to your HTML (header).  Removes backslashes and quotation-marks.  Use a service like <a href="http://picascii.com/">picascii.com</a> to make something useful(ish).</label></p>
							<p><label for="bannedimage">29:: An image to show to users when they are banned.  Completely optional, and completely useless.</label></p>
							<p><label for="boardbanner">30:: An image for your boards.  Just as optional as the banned image, but maybe not quite as useless.</label></p>
							<p><label for="enableblog">31:: Enable the user to browse blog posts from the Regular Board installation.</label></p>
						</div>
					
						<div>
							<p>Use this button to reset post counts to 1.  This will uninstall/reinstall your _posts table, but keep everything else intact.  (This <strong>will</strong> delete all threads and replies.)</p>';
						if ( isset ( $_POST['reinstall_step_one'] ) ) {
							echo '<form method="post"><input type="submit" name="reinstall" value="I am sure.  Proceed." /></form>';						
						} else {
							echo '<form method="post"><input type="submit" name="reinstall_step_one" value="I wish to reinstall Regular Board _posts table" /></form>';
						}
							
						echo '</div>
						<div>
							<p>Use this button to completey uninstall and remove all elements associated with Regular Board from your WordPress installation.  This can not be undone.</p>';
						if ( isset ( $_POST['uninstall_step_one'] ) ) {
							echo '<form method="post"><input type="submit" name="uninstall" value="I am sure.  Proceed." /></form>';						
						} else {
							echo '<form method="post"><input type="submit" name="uninstall_step_one" value="I wish to uninstall Regular Board" /></form>';
						}
						
						echo '</div>';
				}
			}
			echo '</div>';
		}
	}
}