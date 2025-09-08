<?php
namespace Jet_Engine_CCT_Single_Page;

if ( ! defined( 'ABSPATH' ) ) exit;

class Settings {

	protected $settings = null;

	private static $instance = null;

	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_' . $this->get_action_name(), [ $this, 'ajax_save' ] );
	}

	/**
	 * Get the AJAX action name.
	 *
	 * @return string
	 */
	public function get_action_name() {
		return 'jet_cct_single_settings';
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings() {

		if ( null === $this->settings ) {
			$this->settings = get_option( $this->get_action_name(), [] );
		}

		return $this->settings;
	}

	/**
	 * Save settings
	 *
	 * @param array $data Settings data.
	 */
	public function save( $data = [] ) {
		$this->settings = $this->sanitize_settings( $data );
		update_option( $this->get_action_name(), $this->settings );
	}

	/**
	 * Sanitize single settings item.
	 *
	 * @param array $item Settings item.
	 *
	 * @return array
	 */
	public function sanitize_settings_item( $item = [] ) {

		if ( ! is_array( $item ) ) {
			return [];
		}

		$fields_map = [
			'rewrite_base' => 'sanitize_text_field',
			'cct_id'       => 'absint',
			'listing_id'   => 'absint',
			'slug_field'   => 'sanitize_text_field',
			'title'        => 'sanitize_text_field',
			'description'  => 'sanitize_textarea_field',
		];

		$sanitized = [];

		foreach ( $fields_map as $field => $sanitize_function ) {
			if ( isset( $item[ $field ] ) && is_callable( $sanitize_function ) ) {
				$sanitized[ $field ] = $sanitize_function( $item[ $field ] );
			} else {
				$sanitized[ $field ] = '';
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize settings data.
	 *
	 * @param array $data Settings data.
	 *
	 * @return array
	 */
	public function sanitize_settings( $data = [] ) {

		$sanitized = [];

		if ( ! is_array( $data ) ) {
			return $sanitized;
		}

		foreach ( $data as $i => $item ) {
			$sanitized[ $i ] = $this->sanitize_settings_item( $item );
		}

		return $sanitized;
	}

	/**
	 * Save settings via AJAX.
	 */
	public function ajax_save() {

		$nonce = $_POST['nonce'] ?? '';

		if ( ! wp_verify_nonce( $nonce, $this->get_action_name() ) ) {
			wp_send_json_error( [ 'message' => 'Page is expired. Please refresh the page and try again.' ], 400 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		$data = wp_unslash( $_POST['data'] ?? [] );

		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		$data = is_array( $data ) ? $data : [];

		$this->save( $data );
		Frontend::instance()->register_rewrite_rules();
		flush_rewrite_rules();

		wp_send_json_success( [ 'settings' => $this->get_settings() ] );
	}
}
