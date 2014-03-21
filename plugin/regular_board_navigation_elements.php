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

$my_alerts                                            = $my_waitings + $my_unread;
$banner                                               = '';
$stuff_link_class                                     = '';
$home_link_class                                      = '';
$topics_link_class                                    = '';
$gallery_link_class                                   = '';
$history_link_class                                   = '';
$logout_link_class                                    = '';
$gallery_link                                         = '';
$history_link                                         = '';
$logout_link                                          = '';
if ( $this_area == 'stuff' )    { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'messages' ) { $stuff_link_class   = ' class="active" '; }
if ( $this_area == 'options' )  { $stuff_link_class   = ' class="active" '; }
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
	$gallery_link                                     = '<a title="all images" href="' . $current_page . '?a=gallery"' . $gallery_link_class . '><i class="fa fa-camera"></i><span>gallery</span></a>';
}
if ( $user_exists ) {
	$history_link                                     = '<a title="my profile" href="' . $current_page . '?a=history"' . $history_link_class . '><i class="fa fa-user"></i><span>me</span></a>';
}
if ( $my_alerts == 0 )          { $my_alerts          = ''; }
if ( $my_alerts > 0 )           { $my_alerts          = ' <em>' . $my_alerts . ' alert(s)</em> '; }

if ( $user_exists) {
	$logout_link                                      =  '<a title="logout" href="' . $current_page . '?a=logout"' . $logout_link_class . '><i class="fa fa-times-circle"></i><span>logout</span></a>';
}

$home_link   = '<a title="latest activity" href="' . $current_page . '"' . $home_link_class . '><i class="fa fa-home"></i><span>home</span></a>';
$topics_link = '<a title="all topics" href="' . $current_page . '?a=topics"' . $topics_link_class . '><i class="fa fa-book"></i><span>topics</span></a>';
$stuff_link  = '<a title="options and other misc. stuff of importance" href="' . $current_page . '?a=stuff"' . $stuff_link_class . '><i class="fa fa-cog"></i><span>stuff ' . $my_alerts . '</span></a>';
$navigation  =  '<div class="navi">' . $home_link . $topics_link . $stuff_link . $gallery_link . $history_link . $logout_link . '</div>';

if ( $board_banner != '' ) {
	$banner  = '<div class="banner"><img src="' . $board_banner . '" alt="Banner" /></div>';
}