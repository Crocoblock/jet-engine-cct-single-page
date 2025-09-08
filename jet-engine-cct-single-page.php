<?php
/**
 * Plugin Name: JetEngine - Single page for Custom Content Type
 * Plugin URI:
 * Description: Allow to create a single page template for Custom Content Type using Listing Items templates as a base.
 * Version:     1.0.0
 * Author:      Crocoblock
 * Author URI:
 * Text Domain: jet-engine-cct-single-page
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_CCT_SINGLE_PAGE_VERSION', '1.0.0' );

define( 'JET_CCT_SINGLE_PAGE__FILE__', __FILE__ );
define( 'JET_CCT_SINGLE_PAGE_PLUGIN_BASE', plugin_basename( JET_CCT_SINGLE_PAGE__FILE__ ) );
define( 'JET_CCT_SINGLE_PAGE_PATH', plugin_dir_path( JET_CCT_SINGLE_PAGE__FILE__ ) );
define( 'JET_CCT_SINGLE_PAGE_URL', plugins_url( '/', JET_CCT_SINGLE_PAGE__FILE__ ) );

add_action( 'after_setup_theme', 'jet_engine_cct_single_page_init' );

function jet_engine_cct_single_page_init() {
	require JET_CCT_SINGLE_PAGE_PATH . 'includes/settings.php';
	require JET_CCT_SINGLE_PAGE_PATH . 'includes/admin.php';
	require JET_CCT_SINGLE_PAGE_PATH . 'includes/frontend.php';

	\Jet_Engine_CCT_Single_Page\Settings::instance();
	\Jet_Engine_CCT_Single_Page\Admin::instance();
	\Jet_Engine_CCT_Single_Page\Frontend::instance();

	require JET_CCT_SINGLE_PAGE_PATH . 'includes/functions.php';
}
