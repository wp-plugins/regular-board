<?php 

	// This file is a default configuration for Regular Board, and will ALWAYS be overwritten 
	// when upgrading. To ensure that changes are saved, create a file in your BASE DIRECTORY 
	// and name it regular_board_config.php. Save all of your variables to that file to ensure 
	// that changes are kept between upgrades.

	defined( 'regularboardplugin_plugin' ) or exit;
	
	// Do NOT alter any of these values
	
	// You may start altering values in regular_board_config.php, starting here:
	
	
	// How many threads to view per page
	$postsperthread        = 50;
	$postsperpage          = 10;
	
	$admin_code            = ' <small class="user">admin</small>';
	$guest_code            = ' <small class="user">guest</small>';
	$user_code             = ' <small class="user">user</small>';

	// (1) Block FCC IP Addresses (1 for on, 0 for off)
	$fuck_the_fcc          = 1;