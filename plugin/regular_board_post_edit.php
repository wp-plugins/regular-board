<?php 

/**
 * Post Edit Form
 *
 * (1) Display the edit form if we're editing a post, with all information
 * (1) prefetched from the database.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( $is_moderator ) {
	$checkPass = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", $this_thread ) );
} elseif ( $is_user_mod ) {
	$checkPass = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d AND MODERATOR != %d", $this_thread, 1 ) );
} elseif ( $is_user ) {
	$checkPass = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_password = %s AND post_id = %d", $profilepassword, $this_thread ) );
}
if ( count ( $checkPass ) > 0 ) {
	foreach($checkPass as $edit){
		$edit->post_comment = str_replace(array("\\r\\n", "\\r", "\\n", "\\"), array ( " || ", " || ", " || ", "" ), $edit->post_comment );
		
		if ( preg_match ( '/\[\[title\:(.*)\]\]/', $edit->post_comment, $match ) ) {
			$edit->post_title   = '[[title:' . $match[1] . ']]';
			$edit->post_comment = str_replace ( '[[title:' . $match[1] . ']]', '', $edit->post_comment );
		} elseif ( $edit->post_title ) {
			$edit->post_title = '[[title: ' . $edit->post_title . ']]';
		}
		if ( preg_match ( '/\[\[(.*?)\]\]/', $edit->post_comment, $match ) ) {
			$edit->post_board   = '[[' . $match[1] . ']]';
			$edit->post_comment = str_replace ( '[[' . $match[1] . ']]', '', $edit->post_comment );
		} elseif ( $edit->post_board ) {
			$edit->post_board = '[[' . $edit->post_board . ']]';
		}
		if ( preg_match ( '/\+\+(.*)\+\+/', $edit->post_comment, $match ) ) {
			$edit->post_url    = '++' . $match[1] . '++';
			$edit->post_comment = str_replace ( '++' . $match[1] . '++', '', $edit->post_comment );
		} elseif ( $edit->post_url ) {
			if ( $edit->post_type == 'youtube' ) {
				$edit->post_url  = '++//youtube.com/watch?v=' . $edit->post_url . '++';
			} else {
				$edit->post_url  = '++' . $edit->post_url . '++';
			}
		}
		
		$editSubject = str_replace ( '\\', '', $edit->post_title );
		echo '<div id="reply" class="reply">
			<form enctype="multipart/form-data" name="editform" method="post" action="' . $current_page . '?a=post">';
			wp_nonce_field ( 'editform' );
			echo '<input type="hidden" name="password" value="' . $edit->post_password . '" />
			<input type="hidden" value="' . $the_board . '" NAME="board" />
			<input type="hidden" value="" name="LINK" />
			<input type="hidden" value="" name="PAGE" />
			<input type="hidden" value="" name="LOGIN" />
			<input type="hidden" value="" name="USERNAME" />
			<input type="hidden" value="' . $this_thread . '" id="editthisthread" name="editthisthread" />
			<textarea id="COMMENT" name="COMMENT">' . $edit->post_board . $edit->post_title . $edit->post_url . $edit->post_comment . '</textarea>
			<input type="submit" value="Edit" name="FORMSUBMIT" id="FORMSUBMIT" />
			</form>
		</div>';
		$correct = 3;
	}
} else {

	$false_access = $wpdb->get_row( $wpdb->prepare( "SELECT $regular_board_bans_select FROM $regular_board_bans WHERE banned_ip = %d AND banned_message = %s", $user_ip, 'false access' ) );
	if ( count ( $false_access ) == 0 ) {
		$access_count = 5;
		$wpdb->query (
			$wpdb->prepare (
				"INSERT INTO $regular_board_bans 
				( 
					banned_id, banned_date, banned_ip, banned_banned, banned_message, banned_length 
				) 
				VALUES ( 
					%d, %s, %s, %d, %s, %s 
				)",
			'', $current_timestamp, $user_ip, 5, 'false access', '10 minutes' 
			)
		);
	}
	if ( count ( $false_access ) == 1 ) {
			foreach ( $false_access as $mute ) {
				if ( $mute[banned_banned] == 5 ) { $banned_count = 4; }
				if ( $mute[banned_banned] == 4 ) { $banned_count = 3; }
				if ( $mute[banned_banned] == 3 ) { $banned_count = 2; }
				if ( $mute[banned_banned] == 2 ) { $banned_count = 1; }
				$access_count = $banned_count - 1;
				$wpdb->update (
					$regular_board_bans,
					array( 
						'banned_banned' => $banned_count
					),
					array( 
						'banned_ip' => $user_ip
					),
					array( 
						'%s'
					)
				);
			}
	}
	
	echo '<div class="reply">
		<h1>You can\'t do that.</h1>
		<p>You do not have permission to access that resource.  This attempt has been noted, and you have ' . $access_count . ' more warnings before 
		action is taken against you.</p>  
	</div>';
}