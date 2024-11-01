<?php


// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Al borrar el plugin limpiamos todas las opciones grabadas
delete_option('wpipl_color_barra_progreso');
delete_option('wpipl_version');
