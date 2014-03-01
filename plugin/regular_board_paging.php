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
$results = intval ( $_GET['n'] );
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
if($paging > 0){
	$pageresults = round($paging / 10);
	echo '<p class="nav">';
	if($results > 1){
		echo ' [ <a href="' . $current_page . $location . '">Latest</a> ] ';
	}
	if($results > 2){
		echo ' [ <a href="' . $current_page .  $location . '&amp;n=' . ($results - 1) . '">Newer</a> ] ';
	}
	if($paging > 1 && $results < $paging && !$results ){
		echo ' [ <a href="' . $current_page .  $location . '&amp;n=2">Older</a> ] ';
	}
	if($results < $paging && $results ){
		echo ' [ <a href="' . $current_page .  $location . '&amp;n=' . ( $results + 1 ) . '">Older</a> ]  ';
	}
	echo '</p>';
}