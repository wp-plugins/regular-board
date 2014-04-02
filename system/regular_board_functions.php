<?php 

/**
 * Regular Board Functions
 *
 * (1) Various functions used throughout Regular Board
 *
 * @package regular_board
 */	
 
if ( !defined('regular_board_plugin' ) ) {
	die();
}



if ( get_option ( 'regular_board_announcements' ) && get_option ( 'regular_board_hideannouncements' ) ) {
	add_action ( 'pre_get_posts', 'regular_board_exclude_announcements' );
	function regular_board_exclude_announcements ( $query ) {
		if ( $query->is_home ) {
			$tax_query = array (
				'ignore_sticky_posts' => true,
				'post_type' => 'any',
				array (
					'taxonomy' => 'category',
					'terms' => intval ( get_option ( 'regular_board_announcements' ) ),
					'field' => 'id',
					'operator' => 'NOT IN'
				)
			);
			$query->set ( 'tax_query', $tax_query );
		}
	}
}


function regular_board_percent($num_amount, $num_total) {
	$count1 = $num_amount / $num_total;
	$count2 = $count1 * 100;
	$count = number_format($count2, 0);
	return $count;
}

/**
 * Enqueue scripts and styles if post content contains the shortcode
 */
function regular_board_style(){
	global $wpdb, $wp, $post, $regular_board_version, $ipaddress;
	$content = $post->post_content;
	if( has_shortcode ( $content, 'regular_board' ) ) {
		$form_submit    = plugins_url() . '/regular-board/system/js/jquery.form.min.js?' . $regular_board_version;
		wp_deregister_script ( 'regular_board-form');
		wp_register_script   ( 'regular_board-form', protocol_relative_url_dangit ( $form_submit ), array( 'jquery' ), '', null, false);
		wp_enqueue_script    ( 'regular_board-form');

		$regularboard   = plugins_url() . '/regular-board/system/js/regular_board00000000192.js?' . $regular_board_version;
		if ( get_option ( 'regular_board_css_url' ) ) {
			$css_file   = get_option ( 'regular_board_css_url' );
		} else { 
			$regular_board_users = $wpdb->prefix . 'regular_board_users';
			$css_choice = '';
			$user_ip = sanitize_text_field ( wp_hash ( $ipaddress ) );
			$css_choice = $wpdb->get_var( "SELECT user_colormode FROM $regular_board_users WHERE user_ip = '$user_ip'" );
			if ( $css_choice ) {
				if ( $css_choice == 1 ) {
					$css_file   = plugins_url() . '/regular-board/system/css/regular_board_dm_00000000192.css';
				}
				if ( $css_choice == 2 ) {
					$css_file   = plugins_url() . '/regular-board/system/css/regular_board_nm_00000000192.css';
				}
			} else {
				if ( date ( 'H' ) >= 7 && date ( 'H' ) <= 19 ) {
					$css_file   = plugins_url() . '/regular-board/system/css/regular_board_nm_00000000192.css';
				} else {
					$css_file   = plugins_url() . '/regular-board/system/css/regular_board_dm_00000000192.css';
				}
			}
		}
		$regbostyle     = $css_file . '?' . $regular_board_version;
		// Selectively load lazyload!
		if ( get_option ( 'regular_board_lazyload' ) ) {
			$lazy_load           = '//cdn.jsdelivr.net/jquery.lazyload/1.9.0/jquery.lazyload.min.js';
			$lazy_load_functions = plugins_url() . '/regular-board/system/js/lazyload.js';
			wp_deregister_script ( 'regular_board-lazyload');
			wp_register_script   ( 'regular_board-lazyload', protocol_relative_url_dangit ( $lazy_load ), array( 'jquery' ), '', null, false);
			wp_enqueue_script    ( 'regular_board-lazyload');
			wp_deregister_script ( 'regular_board-lazy_load_functions' );
			wp_register_script   ( 'regular_board-lazy_load_functions', protocol_relative_url_dangit ( $lazy_load_functions ), array( 'jquery' ), '', null, false );
			wp_enqueue_script    ( 'regular_board-lazy_load_functions' );
			
		}
		$fontawesome         = plugins_url() . '/regular-board/system/css/fontawesome/css/font-awesome.min.css?' . $regular_board_version;
		wp_register_style    ( 'font-awesome', protocol_relative_url_dangit ( $fontawesome ) );
		wp_enqueue_style     ( 'font-awesome' );
		wp_register_style    ( 'regular_board', protocol_relative_url_dangit ( $regbostyle ) );
		wp_enqueue_style     ( 'regular_board' );
		wp_deregister_script ( 'regularboard' );
		wp_register_script   ( 'regularboard', protocol_relative_url_dangit ( $regularboard ) , array( 'jquery' ), '', null, false );
		wp_enqueue_script    ( 'regularboard' );
	}
}

/**
 * Automatically remove http from links 
 */
function protocol_relative_url_dangit ( $u ) {
	return str_replace ( 'http:', '', esc_url ( $u ) );
}


/**
 * Automatically apply quotes to a group of items for SQL use
 */
function regular_board_apply_quotes($i){
	return "'" . mysql_real_escape_string($i) . "'";
}

/**
 * Generate a random password
 */
$seedlet = str_split ( 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . '0123456789!@#$%^&*()' );
shuffle ( $seedlet );
$random_password = '';
foreach (array_rand($seedlet, 10) as $p) $random_password .= $seedlet[ $p ];

/**
 * Calcuate time between (date) and (date)
 */
function regular_board_timesince ( $date, $granularity=2 ) {
	$retval = '';
	$date = strtotime ( $date );
	$difference = time() - $date;
	$periods = array( ' decades' => 315360000, 
		' years' => 31536000, 
		' months' => 2628000, 
		' weeks' => 604800,  
		' days' => 86400, 
		' hours' => 3600, 
		' minutes' => 60, 
		' seconds' => 1 );

	foreach ( $periods as $key => $value ) {
		if ( $difference >= $value ) {
			$time = floor ( $difference/$value );
			$difference %= $value;
			$retval .= ( $retval ? ' ' : '' ) . $time . '';
			$retval .= ( ( $time > 1 ) ? $key : $key );
			$granularity--;
		}
		if ( $granularity == '0' ) { break; }
	}
	return $retval . ' ago';
}	

/**
 * Format text
 * You type                                You get
 * **bold**                                bold
 * *italics*                               italics
 * ***bold and italic***                   bold and italic
 * ::#:: (where # is the post number)      >> 1            
 * ~~strikethrough~~                       Strikethrough
 * (4 blank spaces)quote(4 blank spaces)   > quote
 * ----                                    Horizontal divider
 * |,||                                    New line, new paragraph
 * `code`                                  code
 * This is a [spoiler]spoiler[/spoiler].   This is a spoiler.
 */
function regular_board_format ( $data ) {
	$input = array (
	'/\{(.*?)}/is',
	'/\:\:(.*?) /is',
	'/\*\*\*(.*?)\*\*\*/is',
	'/\*\*(.*?)\*\*/is',
	'/\*(.*?)\*/is',
	'/\~\~(.*?)\~\~/is',
	'/\{\{(.*?)\}\}/is',
	'/-\-\-\-/is',
	'/—\-/is',
	'/\|/is',
	'/\`(.*?)\`/is',
	'/\[spoiler](.*?)\[\/spoiler]/is',
	'/\\\/is',
	);
	$output = array (
	'<span class="quotes"> &#62; $1 </span><br />',
	'<a href="?t=$1"> &#62;&#62; $1 </a><br />',
	'<strong><em>$1</em></strong>',
	'<strong>$1</strong>',
	'<em>$1</em>',
	'<u class="strike">$1</u>',
	'<blockquote>$1</blockquote>',
	'<hr />',
	'<hr />',
	'<br />',
	'<code>$1</code>',
	'<span class="spoiler">$1</span>',
	);
	$rtrn = preg_replace ( $input, $output, $data );
	return wpautop( $rtrn );
}	

function regular_board_auto_tags($text){
	if ( get_option ( 'regular_board_useboards' ) == 'tags' ) {
		return preg_replace('!((#)[-a-zA-Zа-яА-Я()0-9@:%_+~?&;//=]+)!i', '<a href="?b=$1">$1</a>', $text);
	}
	if ( get_option ( 'regular_board_useboards' ) == 'boards' ) {
		return preg_replace('!((#)[-a-zA-Zа-яА-Я()0-9@:%_+~?&;//=]+)!i', '<a href="?ht=$1">$1</a>', $text);
	}
}

/** 
 * Get the domain of a URL
 */
function regular_board_get_domain ( $url ) {
	$pieces = parse_url ( $url );
	$domain = isset ( $pieces['host'] ) ? $pieces['host'] : '';
	if ( preg_match ( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) ) {
		return $regs['domain'];
	}
	return false;
}

function regular_board_canonical(){
	global $wp,$post;
	$BOARD       = '';
	$THREAD      = '';
	$the_board   = '';
	$this_thread = '';
	$query  = sanitize_text_field ( $_SERVER['QUERY_STRING'] );
	if ( $query ) {
		if ( isset ( $_GET['b'] ) ) {
			$the_board             = sanitize_text_field ( strtolower( $_GET['b'] ) );
		}
		if ( isset ( $_GET['t'] ) ) {
			$this_thread           = intval ( $_GET['t'] );
		}
	}	
	if ( is_page() && $the_board || 
	     is_single() && $the_board || 
		 is_page() && $this_thread || 
		 is_single() && $this_thread 
		) {
		$THISPAGE = home_url('/');
		if ( $_GET['b'] ) { 
			$BOARD  = esc_sql ( strtolower ( $_GET['b'] ) );
		}
		if ( !$_GET['b'] ) { 
			$BOARD  = esc_sql ( strtolower ( $post->post_name ) );
		}
		if ( $BOARD && $THREAD ) { 
			$THREAD = esc_sql ( intval ( $_GET['t'] ) );
		}
		if ( $BOARD && $THREAD != 0 ) { 
			$canonical = $THISPAGE.'?t='.$THREAD;
		} elseif($BOARD && $THREAD == 0 ) { 
			$canonical = $THISPAGE . '?b=' . $BOARD; 
		}
	}		
	elseif ( is_home() ) {
		$canonical         = $THISPAGE;
	} else {
		$THISPAGE          = '//'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$canonical         = $THISPAGE;
	}
	echo "\n";
	echo '<link rel=\'canonical\' href=\'' . htmlentities ( $canonical ) . '\' />';
	echo "\n";
}