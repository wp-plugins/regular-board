<?php 

/**
 * Area: news
 *
 * (1) Board announcements, as set in options
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( $announcements ) {
	if ( isset ( $_GET['post'] ) ) {
		$postno = intval ( $_GET['post'] );
	}
	$cat_args=array(
	'include' => intval ( $announcements )
	);
	$categories=get_categories($cat_args);
	foreach($categories as $category) {
		if ( $postno ) {
			$args=array(
			'p'=> $postno,
			);					
		} else {
			$args=array(
			'showposts' => -1,
			'category__in' => array ( $category->term_id ),
			);
		}
		$posts = get_posts ( $args );
		if ( $posts ) {
			if ( $postno ) {
				echo '<div class="thread clear"><p><a class="load_link" href="' . $this_page . '?a=news">More site announcements</a></p></div>';
			}
			foreach($posts as $post) {
				setup_postdata($post); 
					echo '<div class="thread clear">
					<strong class="left"><a class="load_link" href="' . $this_page . '?a=news&amp;post=' . $post->ID . '">' . $post->post_title . '</a></strong>
					<span class="right">' . regular_board_timesince( $post->post_date ) . '</span>
					</div>
					<div class="thread clear">';
					if ( $postno ) { 
						echo '<hr />' . wpautop ( $post->post_content ) . '<hr /><em>posted by</em> ';
						the_author();
					}
					echo '</div>';
					if ( $postno ) {
						echo '<hr />';
					}
			}
		} else {
			echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
		}
	}
}