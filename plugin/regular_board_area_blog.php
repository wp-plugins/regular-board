<?php 

/**
 * Area: blog
 *
 * (1) Display blog posts from the main blog if set in options
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

echo '<h3><center>Blog</center></h3>';
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
	} elseif ( $announcements ) {
		$args=array(
		'showposts' => -1,
		'category__not_in' => array ( $category->term_id ),
		);
	} else {
		$args=array(
		'showposts' => -1
		);
	}
	$posts = get_posts ( $args );
	if ( $posts ) {
		if ( $postno ) {
			echo '<div class="thread"><a class="load_link" href="' . $this_page . '?a=blog">More blog entries</a></div>';
		}
		foreach($posts as $post) {
			setup_postdata($post); 
				echo '<div class="thread"><strong class="left">';
				echo '<a class="load_link" href="' . $this_page . '?a=blog&amp;post=' . $post->ID . '">' . $post->post_title . '</a>';
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