<?php 

/**
 * IP Checks
 *
 * (1) Determine if the connecting IP is valid (v4/v6)
 * (1) to be used on the main script to determine whether or not 
 * (1) to show content to the viewer, based on this validity.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( inet_pton ( $_SERVER['REMOTE_ADDR'] ) === false ) {
	$ipaddress = false;
}
if ( inet_pton ( $_SERVER['REMOTE_ADDR'] ) !== false ) {
	$ipaddress = esc_attr ( $_SERVER['REMOTE_ADDR'] );
}

if ( !function_exists ( 'regular_board_check_dnsbl' ) ) {
	function regular_board_check_dnsbl($ipaddress){
		$dnsbl_lookup=array(
				get_option('regular_board_dnsbl')
			);
		if ( $ipaddress ) {
			$reverse_ip = implode ( ".", array_reverse ( explode ( ".", $ipaddress ) ) );
			foreach ( $dnsbl_lookup as $host ) {
				if ( checkdnsrr ( $reverse_ip . "." . $host . ".", "A" ) ) {
					$listed.= $reverse_ip . '.' . $host;
				}
			}
		}
		if ( $listed ) {
			$ipaddress === false;
		}
		regular_board_check_dnsbl ( $ipaddress );
	}
}