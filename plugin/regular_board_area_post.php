<?php 

/**
 * Area: post
 *
 * (1) Posting actions
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

echo '<div id="post">';
if ( isset ( $_POST['FORMSUBMIT'] ) ) {			
	$img = $_FILES['img'];
	if ( $_FILES['img']['size'] != 0 ) {
		if ( $img['name'] ) {
			$filename  = $img['tmp_name'];
			$client_id = "$imgurid";
			$handle    = fopen ( $filename, "r" );
			$data      = fread ( $handle, filesize ( $filename ) );
			$pvars     = array ( 'image' => base64_encode ( $data ) );
			$timeout   = 30;
			$curl      = curl_init();
			curl_setopt ( $curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json' );
			curl_setopt ( $curl, CURLOPT_TIMEOUT, $timeout);
			curl_setopt ( $curl, CURLOPT_HTTPHEADER, array ( 'Authorization: Client-ID ' . $client_id ) );
			curl_setopt ( $curl, CURLOPT_POST, 1 );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $pvars );
			$out       = curl_exec ( $curl );
			curl_close ( $curl );
			$pms       = json_decode ( $out,true );
			$URL       = $pms['data']['link'];
			$TYPE      = 'image';
		}
	} else {
		$URL = sanitize_text_field ( wp_strip_all_tags( $_REQUEST['URL'] ) );
	}
	include ( plugin_dir_path(__FILE__) . '/regular_board_post_action.php' );
}
echo '</div>';