<?php 
/**
 * Plugin Name: Regular Board
 * Plugin URI: https://github.com/onebillion/regular_board
 * Description: Standalone (continuation) project for Regular Board, an anonymous text-based WordPress powered bbs.
 * Version: 1
 * Author: onebillion
 * License: GNU General Public License v2
 * License URI: //www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: regular_board
 * GitHub Plugin URI: https://github.com/onebillion/regular_board
 * 
 * Regular Board
 * 
 * @package  regular_board
 * @author   onebillion
 * @license  GPL-2.0+
 * @link     https://github.com/onebillion/regular_board
 *
 * LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with this program;if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

$regular_board_version = '1.2-stable';

register_activation_hook ( __FILE__, 'regular_board_installation_option' );
function regular_board_installation_option() {
	add_option ( 'regular_board_installation', 0 );
}

define         ( 'regular_board_plugin', true );
require_once   ( ABSPATH . 'wp-includes/pluggable.php' );
require_once   ( 'system/regular_board_installation.php' );
if             ( function_exists ( 'akismet_admin_init' ) ) { require_once ( 'akismet.class.php' ); }
require_once   ( 'system/regular_board_functions.php' );
require_once   ( 'system/regular_board_ip_functions.php' );
add_action     ( 'admin_enqueue_scripts', 'regular_board_admin_css' );
require_once   ( 'system/regular_board_options.php' );
require_once   ( 'plugin/regular_board.php' );
remove_action  ( 'wp_head', 'rel_canonical' );
add_action     ( 'wp_head', 'regular_board_canonical' );
add_action     ( 'wp_enqueue_scripts', 'regular_board_style' );
add_action     ( 'wp_head', 'regular_board_head' );
add_shortcode  ( 'regular_board', 'regular_board_shortcode' );
add_filter     ( 'the_content','do_shortcode', 'regular_board_shortcode' );
add_filter     ( 'jetpack_enable_opengraph', '__return_false', 99 );
	
?>