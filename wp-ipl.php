<?php
/*
 * Plugin Name: WP Indicador de Progreso de Lectura
 * Version: 1.6.1
 * Plugin URI: http://mispinitoswp.wordpress.com/
 * Description: Displays a progress indicator for reading the entry in progress. The indicator is a red bar that increases in width depending on the reading of the entry.
 * Author: Juan Carlos Gomez-Lobo
 * Author URI: https://profiles.wordpress.org/jcglp
 * Text Domain: wp-indicador-de-progreso-de-lectura
 * Domain Path: /languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('WPIPL_VERSION', '1.6.1');
define('WPIPL_SCRIPT_SUFFIX', '.min');
define('WPIPL_TEXTDOMAIN', 'wp-indicador-de-progreso-de-lectura');
define('WPIPL_URL', plugins_url( '', __FILE__ ) );
define('WPIPL_DIR', dirname(__FILE__) );


// Load plugin class files
require_once( WPIPL_DIR . '/includes/class-wp-ipl.php');
require_once( WPIPL_DIR . '/includes/class-wp-ipl-settings.php');

// Load plugin libraries
require_once( WPIPL_DIR . '/includes/lib/class-wp-ipl-admin-api.php');


/**
 * Returns the main instance of WP_IPL to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_IPL
 */
function WP_IPL() {

	$instance = WP_IPL::instance( __FILE__, WPIPL_VERSION );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WP_IPL_Settings::instance( $instance );

	}

	return $instance;
}

WP_IPL();
