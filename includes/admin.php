<?php
namespace Jet_Engine_CCT_Single_Page;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

	private static $instance = null;

	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
	}

	/**
	 * Register admin menu.
	 */
	public function menu() {
		add_menu_page(
			'CCT Single Page',
			'CCT Single Page',
			'manage_options',
			'jet-cct-page-settings',
			[ $this, 'render' ],
			'dashicons-welcome-add-page',
			49
		);
	}

	/**
	 * Render admin page.
	 */
	public function render() {
		echo '<div class="wrap"><div id="jet-cct-admin-app"></div></div>';
	}

	/**
	 * Get available content types for options
	 *
	 * @return array
	 */
	public function get_content_types() {

		if ( ! class_exists( '\Jet_Engine\Modules\Custom_Content_Types\Module' ) ) {
			return [];
		}

		$content_types = [
			[
				'value' => '',
				'label' => __( 'Select Content Type...', 'csv' ),
			],
		];

		foreach ( \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types() as $slug => $type ) {

			$name = $type->get_arg( 'name' );
			$name = $name ? $name : $slug;

			$content_types[] = array(
				'value' => $type->type_id,
				'label' => $name,
			);
		}

		return $content_types;
	}

	/**
	 * Get available listing items for options
	 *
	 * @return array
	 */
	public function get_listing_items() {
		return jet_engine()->listings->get_listings_for_options( 'blocks' );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook
	 */
	public function assets( $hook ) {

		if ( $hook !== 'toplevel_page_jet-cct-page-settings' ) {
			return;
		}

		$asset = JET_CCT_SINGLE_PAGE_PATH . 'assets/build/index.asset.php';
		$deps  = [ 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ];
		$ver   = JET_CCT_SINGLE_PAGE_VERSION;

		if ( file_exists( $asset ) ) {
			$a = include $asset;
			$deps = $a['dependencies'];
			$ver  = $a['version'];
		}

		wp_enqueue_script(
			'jet-cct-admin',
			JET_CCT_SINGLE_PAGE_URL . 'assets/build/index.js',
			$deps,
			$ver,
			true
		);

		wp_enqueue_style(
			'jet-cct-admin',
			JET_CCT_SINGLE_PAGE_URL . 'assets/build/style-index.css',
			[ 'wp-components' ],
			$ver
		);

		wp_localize_script( 'jet-cct-admin', 'JET_CCT_ADMIN_DATA', [
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( Settings::instance()->get_action_name() ),
			'action'       => Settings::instance()->get_action_name(),
			'settings'     => Settings::instance()->get_settings(),
			'contentTypes' => $this->get_content_types(),
			'listingItems' => $this->get_listing_items(),
		] );
	}
}
