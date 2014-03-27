<?php 

/**
 * Area: stuff
 *
 * (1) Extra links, like options and announcements.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

echo '<div class="thread_container">
<h1>stuff</h1>
<div class="container_half">
	<em>Tools/info</em>:
	<ul>';
	if ( $user_exists ) {
		echo '<li><a class="load_link" href="' . $current_page . '?a=messages">messages</a> &mdash; you have ' . $my_unread . ' unread messages.</li>';
		echo '<li><a class="load_link" href="' . $current_page . '?a=options">options</a> &mdash; your personal settings / you have ' . $my_waitings . ' connections pending.</li>';
	}
	if ( $enable_blog ) {
		echo '<li><a class="load_link" href="' . $current_page . '?a=blog">blog</a> &mdash; words and thoughts</li>';
	}
	if ( $announcements ) {
		echo '<li><a class="load_link" href="' . $current_page . '?a=news">news</a> &mdash; announcements and site news</li>';
	}
	echo '
	<li><a class="load_link" href="' . $current_page . '?a=stats">stats</a> &mdash; board statistics</li>
	<li><a class="load_link" href="' . $current_page . '?a=mod">moderation log</a></li>
	</ul>
</div>';
if ( $protocol == 'boards' ) {
	if ( count ( $getboards ) > 0 ) {
		echo '<div class="container_half">
		<em>Active boards</em>:
		<ul>';
			foreach ( $getboards as $gotboards ) {
				if ( $gotboards->board_postcount > 0 ) {
					echo '<li><a class="load_link" href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_shortname . '</a></li>';
				}
			}
		echo '</ul>
		</div>';
	}
}
echo '</div>';