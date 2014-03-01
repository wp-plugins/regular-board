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
	$periods = array( 'decade' => 315360000, 
		'year' => 31536000, 
		'month' => 2628000, 
		'week' => 604800,  
		'day' => 86400, 
		'hour' => 3600, 
		'minute' => 60, 
		'second' => 1 );

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
	return str_replace( array ( 'decade', 'year', 'month', 'week', 'day', 'hour', 'minute', 'second' ), array ( 'D', 'Y', 'm', 'w', 'd', 'h', 'm', 's' ), $retval . ' ago' );
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
 * [//i.imgur.com/*]                  Embed an image from imgur.
 * [//imgur.com/a/*]                  Embed an album from imgur.	 
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
	'/\[/is',
	'/\]/is',
	'/\\\/is',
	);
	$output = array (
	'<span class="quotes"> &#62; $1 </span><br />',
	'<a href="?t=$1"> &#62;&#62; $1 </a><br />',
	'<strong><em>$1</em></strong>',
	'<strong>$1</strong>',
	'<em>$1</em>',
	'<span class="strike">$1</span>',
	'<blockquote>$1</blockquote>',
	'<hr />',
	'<hr />',
	'<br />',
	'<code>$1</code>',
	'<span class="spoiler">$1</span>',
	'&#91;',
	'&#93;',
	'',
	);
	$rtrn = preg_replace ( $input, $output, $data );
	return wpautop( $rtrn );
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
	$BOARD  = '';
	$THREAD = '';
	if ( $prettycanon != 1 && is_page() && $_GET['board'] || $prettycanon != 1 && is_single() && $_GET['board'] ) {
		$THISPAGE = home_url('/');
		if ( $_GET['board'] ) { 
			$BOARD  = esc_sql ( strtolower ( $_GET['board'] ) );
		}
		if ( !$_GET['board'] ) { 
			$BOARD  = esc_sql ( strtolower ( $post->post_name ) );
		}
		if ( $BOARD ) { 
			$THREAD = esc_sql ( intval ( $_GET['thread'] ) );
		}
		if ( $BOARD && $THREAD != 0 ) { 
			$canonical = $THISPAGE.'?b='.$BOARD.'&amp;t='.$THREAD;
		} elseif($BOARD && $THREAD == 0 ) { 
			$canonical = $THISPAGE . '?b=' . $BOARD; 
		}
	} elseif ( $prettycanon == 1 && is_page() && $_GET['board'] || $prettycanon == 1 && is_single() && $_GET['board'] ) {
		$THISPAGE = home_url('/');
		if ( $_GET['board'] ) { 
			$BOARD  = esc_sql ( strtolower($_GET['board'] ) );
		}
		if ( !$_GET['board'] ) { 
			$BOARD  = esc_sql ( strtolower($post->post_name) );
		}
		if ( $BOARD ) { 
			$THREAD = esc_sql(intval($_GET['thread']));
		}
		if ( $BOARD && $THREAD != 0 ) { 
			$canonical = $THISPAGE . '?t=' . $THREAD; 
		} elseif ( $BOARD && $THREAD == 0 ) { 
			$canonical = $THISPAGE;
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