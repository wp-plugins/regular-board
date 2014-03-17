<?php 

/**
 * Paging
 *
 * (1) Handle paging for various loops
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
 
$i       = 0;
if ( isset ( $_GET['n'] ) ) {
	$results = intval ( $_GET['n'] );
} else {
	$results = 1;
}
$paging  = round ( $totalpages / $posts_per_page );
 
$location = '';

if ( $this_area ) {
	$location = '?a=' . $this_area;
}
if ( $the_board ) {
	$location = '?b=' . $the_board;
} 
if ( $this_user ) {
	$location = '?u=' . $this_user;
}

if ( !$nothing_is_here ) {
	$locale = $current_page . $location . '&amp;n=';
}
if ( $nothing_is_here ) {
	$locale = $current_page . '?n=';
}

if($paging > 0){
	$pageresults = round($paging / 10);
	echo '<p class="nav">';
	if($results > 1){
		echo ' [ <a href="' . $locale . '">Latest</a> ] ';
	}
	if($results > 2){
		echo ' [ <a href="' . $locale . ($results - 1) . '">Newer</a> ] ';
	}
	if($paging > 1 && $results < $paging && !$results ){
		echo ' [ <a href="' . $locale . '2">Older</a> ] ';
	}
	if($results < $paging && $results ){
		echo ' [ <a href="' . $locale . ( $results + 1 ) . '">Older</a> ]  ';
	}
	echo '</p>';
}