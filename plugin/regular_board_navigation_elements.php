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

$stuff_link_class                                     = '';
$home_link_class                                      = '';
$topics_link_class                                    = '';
$gallery_link_class                                   = '';
$history_link_class                                   = '';
$logout_link_class                                    = '';
$gallery_link                                         = '';
$history_link                                         = '';
$logout_link                                          = '';
$options_link                                         = '';
$options_link_class                                   = '';
if ( $this_area == 'stuff' )    { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'messages' ) { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'options' )  { $options_link_class = ' class="active" '; }
if ( $this_area == 'blog' )     { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'news' )     { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'stats' )    { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'mod' )      { $stuff_link_class   = ' class="active" '; }
if ( $nothing_is_here )         { $home_link_class    = ' class="active" '; }
if ( $this_area == 'topics' )   { $topics_link_class  = ' class="active" '; }
if ( $the_board )               { $topics_link_class  = ' class="active" '; }
if ( $this_area == 'gallery' )  { $gallery_link_class = ' class="active" '; }
if ( $this_area == 'history' )  { $history_link_class = ' class="active" '; }
if ( $this_area == 'logout' )   { $logout_link_class  = ' class="active"'; }
if ( $enable_rep || $enable_url || $imgurid ) {
	if ( $the_board ) {
		$gallery_link                                     = '<a title="all images" href="' . $current_page . '?b=' . $the_board . '&amp;a=gallery"' . $gallery_link_class . '><span>gallery</span><i class="fa fa-camera"></i></a>';
	}
	if ( !$the_board ) {
		$gallery_link                                     = '<a title="all images" href="' . $current_page . '?a=gallery"' . $gallery_link_class . '><span>gallery</span><i class="fa fa-camera"></i></a>';
	}
}
if ( $user_exists ) {
	$history_link                                     = '<a title="my profile" href="' . $current_page . '?a=history"' . $history_link_class . '><span>me</span><i class="fa fa-user"></i></a>';
}

if ( $user_exists && $profile_name && $profilepassword) {
	$logout_link                                      =  '<a id="logout-link" title="logout" href="' . $current_page . '?a=logout"' . $logout_link_class . '><span>logout</span><i class="fa fa-times-circle"></i></a>';
}

$reports_link     = '';
$deleted_link     = '';
$queue_link       = '';
$video_link       = '';
$video_link_class = '';

if ( $this_area == 'videos' ) { $video_link_class = ' class="active" '; }
if ( $enable_rep || $enable_url ) {
	if ( $the_board ) {
		$video_link = '<a title="all videos" href="' . $current_page . '?b=' . $the_board . '&amp;a=videos"' . $video_link_class . '><span>videos</span><i class="fa fa-video-camera"></i></a>';
	} else {
		$video_link = '<a title="all videos" href="' . $current_page . '?a=videos"' . $video_link_class . '><span>videos</span><i class="fa fa-video-camera"></i></a>';
	}
}

if ( $is_moderator || $is_user_mod ) {
	$queue_link   = '<a title="awaiting approval" href="' . $current_page . '?a=queue"><span>moderation</span><i class="fa fa-warning"></i></a>';
}

$blog_link    = '<a title="home" href="' . $current_page . '"><span>' . $blog_title . '</span><i class="fa fa-rocket"></i></a>';
$home_link    = '<a title="latest activity" href="' . $current_page . '"' . $home_link_class . '><span>new</span><i class="fa fa-home"></i></a>';
$topics_link  = '<a title="all topics" href="' . $current_page . '?a=topics"' . $topics_link_class . '><span>topics</span><i class="fa fa-book"></i></a>';
$stuff_link   = '<a title="options and other misc. stuff of importance" href="' . $current_page . '?a=stuff"' . $stuff_link_class . '><span>stuff</span><i class="fa fa-cog"></i></a>';
if ( $user_exists ) {
	$options_link = '<a id="settings-link" title="my personal account settings" href="' . $current_page . '?a=options"' . $options_link_class . '><span>settings</span><i class="fa fa-tachometer"></i></a>';
}
$navigation   =  '<div class="navi">' . $blog_link . $home_link . $topics_link . $stuff_link . $gallery_link . $video_link . $history_link . $reports_link . $deleted_link . $queue_link . $logout_link . $options_link . '</div>';

