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
$paging  = $totalpages / $posts_per_page;

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
	$latest = $current_page . $location;
}
if ( $nothing_is_here ) {
	$locale = $current_page . '?n=';
	$latest = $current_page;
}

if($paging > 0){
	$pageresults = round($paging / 10);
	echo '<p class="nav clear">';
	if($results > 1){
		if ( $nothing_is_here ) {
			echo ' [ <a class="load_link" href="' . $latest . '">Latest</a> ] ';
		} else { 
			echo ' [ <a class="load_link" href="' . $latest . '">Latest</a> ] ';
		}
	}
	if($results > 2){
		echo ' [ <a class="load_link" href="' . $locale . ($results - 1) . '">Newer</a> ] ';
	}
	if($paging > 1 && $results < $paging && !$results ){
		echo ' [ <a class="load_link" href="' . $locale . '2">Older</a> ] ';
	}
	if($results < $paging && $results ){
		echo ' [ <a class="load_link" href="' . $locale . ( $results + 1 ) . '">Older</a> ]  ';
	}
	
	if ( $results ) {
		echo ' [ Page ' . $results . ' ]';
	}
	
	echo '</p>';
}