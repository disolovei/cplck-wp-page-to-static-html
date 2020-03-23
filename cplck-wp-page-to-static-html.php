<?php
/*
 * Plugin Name: CPLCK Page To Static HTML
 * Author: Dima Solovey
 * Version: 0.8
 */

( defined( 'ABSPATH' ) && ! defined( 'PTSH_ABSPATH' ) && ! defined( 'PTSH_MAINFILE' ) ) || exit;

define( 'PTSH_ABSPATH', __DIR__ . DIRECTORY_SEPARATOR );
define( 'PTSH_MAINFILE', __FILE__ );

include_once 'inc/classes/class-page-to-static-html.php';
$GLOBALS['PTSH'] = PTSH();