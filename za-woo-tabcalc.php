<?php
/**
 * Plugin Name: WooCommerce TabCalc
 * Plugin URI: http://2penguins.eu
 * Version: 0.6
 * Author: Victor Wondermega
 * Description: Add spec tab to woo product and calc by dimensions
 * Requires at least: 5.8
 * Tested up to: 5.8
 * WC requires at least: 6.1
 * WC tested up to: 6.1
 * Domain Path: /languages
 **/

if(!defined( 'ABSPATH' )||!function_exists('add_action')) {
	exit('ザガタ'); // don't access directly
} else {}

define('ZA_BLD_VERSION', '0.6');
define('ZA_BLD_MIN_WP_VERSION', '5.8');

define('ZA_BLD_FILE', __FILE__); // this file
define('ZA_BLD_BASENAME', plugin_basename( ZA_BLD_FILE )); // plugin name as known by WP
define('ZA_BLD_DIR', dirname( ZA_BLD_FILE )); // directory

require_once( ABSPATH.'wp-admin/includes/plugin.php' );
require_once(ZA_BLD_DIR.'/class.tabcalc.php');
$za = false; // zagata embryo (loader, manager, view)
$za_tc = new zTabcalc($za); // love this, it will be part of $za… ->m['tabcalc']

// ザガタ ////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

  register_activation_hook( __FILE__, array($za_tc, 'install') );
  register_deactivation_hook( __FILE__, array($za_tc, 'uninstall') );

  add_action('init', array($za_tc, 'ini'));

 
 ?>