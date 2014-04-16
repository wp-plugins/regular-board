<?php 

/**
 * Open Graph and Meta Information
 *
 * (1) Display Open Graph and Meta information for threads and boards
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$the_board   = '';
$this_thread = '';
$getres      = '';

$query       = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
if ( $query ) {
	if ( isset ( $_GET['b'] ) ) {
		$the_board   = esc_sql ( strtolower ( $_GET['b'] ) );
	}
	if ( isset ( $_GET['t'] ) ) {
		$this_thread = intval ( $_GET['t'] );
	}
	if( $this_thread ) {
		$getres = $wpdb->get_results ( 
					$wpdb->prepare ( 
						"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE 
						post_id = %d LIMIT 1", 
						$this_thread 
					) 
				  );
		if ( count ( $getres ) == 1 ) {
			foreach ( $getres as $meta ) {
				if ( $meta->post_board ) {
					$the_board = $meta->post_board;
				}
				$canonical    = '';
				$author       = '';
				$title        = '';
				$site         = '';
				$locale       = '';
				$published    = '';
				$last         = '';
				$image        = '';
				$video        = '';
				$description  = '';
				$locale       = get_locale();
				$site         = get_bloginfo( 'name' );
				$current_page = home_url('/');
				$pretty       = esc_attr ( get_option ( 'mommaincontrol_prettycanon' ) );
				$the_board    = esc_sql ( strtolower ( $_GET['b'] ) );
				if ( $meta->post_parent == 0 ) {
					$this_thread  = intval ( $_GET['t'] );
				}
				if ( $meta->post_parent != 0 ) {
					$this_thread  = intval ( $meta->post_parent ) . '#' . intval ( $_GET['t'] );
				}
				$canonical   = $current_page . '?t=' . $this_thread;
				$author      = $meta->post_moderator;
				$title       = str_replace ( '\\', '', $meta->post_title );
				if ( !$title ) {
					$title   = 'No subject';
				}

				$published   = $meta->post_date;
				$last        = $meta->post_last;
				$type        = $meta->post_type;
				if ( $type == 'image' ) {
					$image   = $meta->post_url;
				}
				if ( $type == 'youtube' ) {
					$video   = '//youtube.com/watch?v=' . $meta->post_url;
				}
				$description = str_replace ( array ( '||||', '||', '*', '{{', '}}', '>>', ' >', '~~', ' - ', '----', '::', '`', '    '), '', ( str_replace ( '\\', '', $description ) ) );
				$description = substr ( $description,0,150 );
				echo "\n";
				if ( $canonical ) {
					echo '<meta property="og:url" content="' . $canonical . '" /> ' . "\n";
				}
				if ( $title ) {
					echo '<meta property="og:title" content="' . $title . '" /> ' . "\n";
				}
				if ( $site ) {
					echo '<meta property="og:site_name" content="' . $site . '" /> ' . "\n";
				}
				if ( $locale ) {
					echo '<meta property="og:locale" content="' . $locale . '" /> ' . "\n";
				}
				if ( $image ) {
					echo '<meta property="og:image" content="' . $image . '" /> ' . "\n";
				}
				if ( $video ) {
					echo '<meta property="og:video" content="//www.youtube.com/v/' . $meta->post_url . '?autohide=1&amp;version=3" /> ' . "\n";
					echo '<meta property="og:video:type" content="application/x-shockwave-flash" /> ' . "\n";
					echo '<meta property="og:video:height" content="720" /> ' . "\n";
					echo '<meta property="og:video:width" content="1280" /> ' . "\n";
					echo '<meta property="og:type" content="video" /> ' . "\n";
					echo '<meta property="og:image" content="//img.youtube.com/vi/' . $meta->post_url . '/0.jpg" /> ' . "\n";
				} else {
					if ( $published ) {
						echo '<meta property="og:published_time" content="' . $published . '" /> ' . "\n";
					}
					if ( $published ) {
						echo '<meta property="og:modified_time" content="' . $published . '" /> ' . "\n";
					}
					if ( $last ) {
						echo '<meta property="og:updated" content="' . $last . '" /> ' . "\n";
					}
					echo '<meta property="og:type" content="article" /> ' . "\n";
				}
				if ( $description ) {
					echo '<meta property="og:description" content="' . $description . '" /> ' . "\n\n";
				}
			}
		}
	}
}