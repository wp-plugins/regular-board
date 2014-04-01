<?php 

/**
 * Navigation elements
 *
 * (1) Active links and links with dependencies
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$all_link_class                                       = '';
$stuff_link_class                                     = '';
$home_link_class                                      = '';
$topics_link_class                                    = '';
$gallery_link_class                                   = '';
$history_link_class                                   = '';
$logout_link_class                                    = '';
$gallery_link                                         = '';
$all_link                                             = '';
$history_link                                         = '';
$logout_link                                          = '';
$options_link                                         = '';
$options_link_class                                   = '';
if ( $this_area == 'all' )                    { $all_link_class     = ' class="active" '; }
if ( $this_area == 'stuff' )                  { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'messages' )               { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'options' )                { $options_link_class = ' class="active" '; }
if ( $this_area == 'blog' )                   { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'news' )                   { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'stats' )                  { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'mod' )                    { $stuff_link_class   = ' class="active" '; }
if ( $nothing_is_here )         { 
	if ( !$this_area ) {
		$home_link_class    = ' class="active" '; 
	}
}
if ( $this_area == 'topics' || $the_board )                 { $topics_link_class  = ' class="active" '; }
if ( $the_board && $this_area == 'topics')    { $topics_link_class  = ' class="active" '; }
if ( $this_area == 'gallery' )                { $gallery_link_class = ' class="active" '; }
if ( $this_area == 'history' )                { $history_link_class = ' class="active" '; }
if ( $this_area == 'logout' )                 { $logout_link_class  = ' class="active"'; }
if ( $enable_rep || $enable_url || $imgurid ) {
	if ( $the_board ) {
		$gallery_link                                     = '<a title="all images" href="' . $current_page . '?b=' . $the_board . '&amp;a=gallery"' . $gallery_link_class . '>gallery</a>';
	}
	if ( !$the_board ) {
		$gallery_link                                     = '<a title="all images" href="' . $current_page . '?a=gallery"' . $gallery_link_class . '>gallery</a>';
	}
}
if ( $user_exists ) {
	$history_link                                     = '<a title="my profile" href="' . $current_page . '?a=history"' . $history_link_class . '>me</a>';
}

if ( $user_exists && $profile_name && $profilepassword) {
	$logout_link                                      =  '<a id="logout-link" title="logout" href="' . $current_page . '?a=logout"' . $logout_link_class . '>logout</a>';
}

$reports_link     = '';
$deleted_link     = '';
$queue_link       = '';
$video_link       = '';
$video_link_class = '';

if ( $this_area == 'videos' ) { $video_link_class = ' class="active" '; }
if ( $enable_rep || $enable_url ) {
	if ( $the_board ) {
		$video_link = '<a title="all videos" href="' . $current_page . '?b=' . $the_board . '&amp;a=videos"' . $video_link_class . '>videos</a>';
	} else {
		$video_link = '<a title="all videos" href="' . $current_page . '?a=videos"' . $video_link_class . '>videos</a>';
	}
}

if ( $is_moderator || $is_user_mod ) {
	$queue_link   = '<a title="awaiting approval" href="' . $current_page . '?a=queue">moderation</a>';
}

$blog_link    = '<a title="home" href="' . $current_page . '">front</a>';
$all_link     = '<a title="All (unfiltered)" href="' . $current_page. '?a=all"' . $all_link_class . '>all</a>';

if ( $the_board ) {
	$topics_link  = '<a title="all topics" href="' . $current_page . '?b=' . $the_board . '&amp;a=topics"' . $topics_link_class . '>topics</a>';
} else {
	$topics_link  = '<a title="all topics" href="' . $current_page . '?a=topics"' . $topics_link_class . '>topics</a>';
}

$stuff_link   = '<a title="options and other misc. stuff of importance" href="' . $current_page . '?a=stuff"' . $stuff_link_class . '>stuff</a>';
if ( $user_exists ) {
	$options_link = '<a id="settings-link" title="my personal account settings" href="' . $current_page . '?a=options"' . $options_link_class . '>settings</a>';
}

$board_current = '';
$board_present = '';
if ( $board_short ) {
	if ( $board_name ) {
		$board_name_current = ' / ' . $board_name;
	}
	$board_present = 1;
	if ( $board_present ) {
		$board_present_class = ' board_head';
	}
	$board_current = '<div class="board_header"><a href="' . $current_page . '?b=' . $board_short . '">' . $board_short . $board_name_current . '</a></div>';
}

$navigation   =  '<div class="navi' . $board_present_class. '">' . $board_current . $topics_link . $gallery_link . $video_link . $stuff_link . $history_link . $reports_link . $deleted_link . $queue_link . $logout_link . $options_link . '</div>';