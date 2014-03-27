<?php 

/**
 * Message center
 *
 * (1) Display messages for users
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

echo '<div class="thread_container">';
if ( count ( $my_messages ) > 0 ) {
	foreach ( $my_messages as $messages ) {
	
		$messages->messages_id      = intval ( $messages->messages_id );
		$messages->messages_to      = sanitize_text_field ( $messages->messages_to );
		$messages->messages_from    = sanitize_text_field ( $messages->messages_from );
		$message_to                 = $messages->messages_to;
		$message_from               = $messages->messages_from;
		$message_id                 = $messages->messages_id;
		$messages->messages_read    = intval ( $messages->messages_read );
		$messages->messages_date    = sanitize_text_field ( $messages->messages_date );
		$messages->messages_subject = sanitize_text_field ( $messages->messages_subject );
		if ( !$messages->messages_subject ) {
			$messages->messages_subject = 'No subject';
		}
		$messages->messages_subject = '<a class="load_link" href="' . $this_page . '?a=messages&message=' . $messages->messages_id . '">' . $messages->messages_subject . '</a>';
		
		$messages->messages_content = str_replace ( array ( '\\n', '\\r', '\\'), array( '<br />','<br />','' ), $messages->messages_content );
		if ( $formatting ) {
			$messages->messages_content = regular_board_format( $messages->messages_content );
		} else {
			$messages->messages_content = $messages->messages_content;
		}
		$messages_id = $messages->messages_id;
		if ( $messages->messages_from != $profile_name ) {
			if ( !$messages->messages_read ) {
				$wpdb->query ( "UPDATE $regular_board_messages SET messages_read = 1 WHERE messages_id = $messages_id" );
			}
		}
		
		echo '<div class="thread">
		' . $messages->messages_subject . ' &mdash; 
		To: ' . $messages->messages_to ;
			if ( $messages->messages_to == $profile_name ) {
				echo ' (you) ';
			}
		echo '&mdash; From: ' . $messages->messages_from;
			if ( $messages->messages_from == $profile_name ) {
				echo ' (you) ';
			}
		if ( $messages->messages_from != $profile_name && !$messages->messages_read || isset ( $_GET['message'] ) ) {
			echo '<div class="comment">' . wpautop ( $messages->messages_content ) . '</div>';
		}
		
		if ( isset ( $_POST['delete' . $message_id . ''] ) && $messages->messages_to == $profile_name ) {
			$wpdb->delete ( $regular_board_messages, array ( 'messages_id' => $message_id ), array ( '%d' ) );
			echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=messages"></p>';
		}
		
		if ( $messages->messages_from != $profile_name ) {
			echo '<form class="right" name="message' . $message_id . '" method="post" action="' . $current_page . '?a=messages">';
			wp_nonce_field('regularboard' . $message_id . '');
			echo '<input type="submit" value="Delete" name="delete' . $message_id . '" />
			</form>';
		}
		echo '</div>';
	}
}

if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
	include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
} else {
	include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
}				
echo '</div>';

?>