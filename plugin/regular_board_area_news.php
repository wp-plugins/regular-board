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
	echo '<h3><center>Announcements</center></h3>';
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
				echo '<div class="thread"><a class="load_link" href="' . $this_page . '?a=news">More site announcements</a></div>';
			}
			foreach($posts as $post) {
				setup_postdata($post); 
					echo '<div class="thread"><strong class="left">';
					echo '<a class="load_link" href="' . $this_page . '?a=news&amp;post=' . $post->ID . '">' . $post->post_title . '</a>';
					echo '</strong>';
					echo '<span class="right">' . regular_board_timesince( $post->post_date ) . '</span>';
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