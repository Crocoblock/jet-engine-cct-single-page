<?php
namespace Jet_Engine_CCT_Single_Page;

if ( ! defined( 'ABSPATH' ) ) exit;

class Frontend {

	private static $instance = null;

	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'query_vars' ] );
		add_filter( 'template_include', [ $this, 'template_include' ] );
		add_filter( 'pre_get_document_title', [ $this, 'filter_title' ], 20 );
		add_action( 'wp_head', [ $this, 'output_meta_description' ], 1 );

		add_action(  'jet-engine/callbacks/register', [ $this, 'register_link_callback' ]);

		add_filter(
			'jet-engine/listings/dynamic-link/fields',
			[ $this, 'add_link_source_fields' ]
		);

		// Works the same as prev, but placed only where only plain URL allowed to return
		add_filter(
			'jet-engine/listings/dynamic-link/fields/common',
			[ $this, 'add_link_source_fields' ],
			10, 3
		);

		add_filter(
			'jet-engine/listings/dynamic-link/custom-url',
			[ $this, 'maybe_set_cct_link' ],
			20, 2
		);
	}

	/**
	 * Maybe set CCT item link.
	 *
	 * @param string $url
	 * @param array  $item
	 * @return string
	 */
	public function maybe_set_cct_link( $result = false, $settings = [] ) {

		$source = ! empty( $settings['dynamic_link_source'] ) ? $settings['dynamic_link_source'] : '_permalink';


		if ( 'jet_cct_single_page_link' !== $source	 ) {
			return $result;
		}

		return $this->get_item_link();
	}

	/**
	 * Get item link by item object or ID.
	 *
	 * @param string $title_fallback
	 * @return string
	 */
	public function get_item_link( $title_fallback = '' ) {

		$id = jet_engine()->listings->data->get_current_object_id();
		$object = jet_engine()->listings->data->get_current_object();

		if ( ! $id || ! is_object( $object ) ) {
			return '';
		}

		$cct_slug = $object->cct_slug ?? '';

		if ( ! $cct_slug ) {
			return '';
		}

		$cct = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types( $cct_slug );

		if ( ! $cct || ! $cct->type_id ) {
			return '';
		}

		$settings = Settings::instance()->get_settings();

		$base = '';

		foreach ( $settings as $s ) {
			if ( absint( $s['cct_id'] ?? 0 ) === absint( $cct->type_id ) ) {
				$base = trim( $s['rewrite_base'] ?: 'cct', '/' );
				$slug_field = $s['slug_field'] ?? '';
				break;
			}
		}

		if ( ! $base ) {
			return '';
		}

		$slug = $slug_field && isset( $object->{$slug_field} ) ? sanitize_title( $object->{$slug_field} ) : '';

		if ( ! $slug && $title_fallback ) {
			$slug = sanitize_title( $title_fallback );
		}

		if ( ! $slug ) {
			$slug = 'item';
		}

		return home_url( "{$base}/{$slug}-{$id}/" );
	}

	/**
	 * Register link callback.
	 *
	 * @param \Jet_Engine\Callbacks_Manager $manager
	 */
	public function register_link_callback( $manager ) {
		$manager->register_callback( 'jet_cct_single_page_link', 'CCT Single Page Link' );
	}

	public function add_link_source_fields( $groups, $for = 'plain', $is_common = false ) {

		$options = [
			'jet_cct_single_page_link' => 'CCT Single Page Link',
		];

		$groups[] = [
			'label'   => 'CCT Single Page',
			'options' => $options,
		];

		return $groups;
	}

	/**
	 * Register rewrite rules.
	 */
	public function register_rewrite_rules() {

		$settings = Settings::instance()->get_settings();

		if ( empty( $settings ) ) {
			return;
		}

		foreach ( $settings as $item ) {

			$base = trim( $item['rewrite_base'] ?: 'cct', '/' );

			add_rewrite_rule(
				'^' . preg_quote( $base, '/' ) . '/[^/]+-(\d+)/?$',
				'index.php?is_single_cct_page=' . $base . '&_ID=$matches[1]',
				'top'
			);
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function query_vars( $vars ) {

		$vars[] = 'is_single_cct_page';
		$vars[] = '_ID';

		return $vars;
	}

	/**
	 * Template include filter.
	 *
	 * @param string $template
	 * @return string
	 */
	public function template_include( $template ) {

		$single_base = get_query_var( 'is_single_cct_page' );
		$listing_id  = get_query_var( '_ID' );

		if ( $single_base && $listing_id ) {

			$custom = JET_CCT_SINGLE_PAGE_PATH . 'templates/single-cct.php';

			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}

		return $template;
	}

	/**
	 * Get CCT item by ID and base.
	 *
	 * @param int    $id
	 * @param string $base
	 * @return object|null
	 */
	public function get_item( int $id, string $base = '' ) {

		if ( ! class_exists( '\Jet_Engine\Modules\Custom_Content_Types\Module' ) ) {
			return null;
		}

		$settings = Settings::instance()->get_settings();

		$cct_id = 0;

		foreach ( $settings as $item ) {
			if ( $item['rewrite_base'] === $base ) {
				$cct_id = absint( $item['cct_id'] );
				break;
			}
		}

		if ( ! $cct_id ) {
			return null;
		}

		$content_type = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_type_by_id( $cct_id );

		if ( ! $content_type ) {
			return null;
		}

		$flag = \OBJECT;
		$content_type->db->set_format_flag( $flag );
		$item = $content_type->db->get_item( $id );

		return $item;
	}

	/**
	 * Filter the document title.
	 *
	 * @param string $title
	 * @return string
	 */
	public function filter_title( $title ) {

		$base = get_query_var( 'is_single_cct_page' );

		if ( ! $base ) {
			return $title;
		}

		$settings = Settings::instance()->get_settings();

		$id = absint( get_query_var( '_ID' ) );


		if ( ! $id ) {
			return $title;
		}

		$item = $this->get_item( $id, $base );

		if ( ! $item ) {
			return $title;
		}

		$pattern = '';

		foreach ( $settings as $s ) {
			if ( $s['rewrite_base'] === $base ) {
				$pattern = (string) ( $s['title'] ?? '' );
				break;
			}
		}

		if ( ! $pattern ) {
			return $title;
		} else {
			return $this->replace_tokens( $pattern, $item );
		}
	}

	/**
	 * Output meta description tag.
	 */
	public function output_meta_description() {

		$base = get_query_var( 'is_single_cct_page' );

		if ( ! $base ) {
			return;
		}
		$settings = Settings::instance()->get_settings();
		$id = absint( get_query_var( '_ID' ) );

		if ( ! $id ) {
			return;
		}

		$item = self::get_item( $id, $base );

		if ( ! $item ) {
			return;
		}

		$pattern = '';

		foreach ( $settings as $s ) {
			if ( $s['rewrite_base'] === $base ) {
				$pattern = (string) ( $s['description'] ?? '' );
				break;
			}
		}

		if ( ! $pattern ) {
			return;
		}

		$desc = trim( $this->replace_tokens( $pattern, $item ) );

		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
	}

	/**
	 * Replace tokens in pattern with item properties.
	 *
	 * @param string $pattern
	 * @param object $item
	 * @return string
	 */
	public function replace_tokens( $pattern, $item ) {

		if ( ! $pattern || ! is_object( $item ) ) {
			return '';
		}

		$replaced = preg_replace_callback(
			'/\%([a-zA-Z0-9_\-]+)\%/',
			function( $matches ) use ( $item ) {
				$token = $matches[1] ?? '';

				if ( ! $token ) {
					return '';
				}

				$value = $item->{$token} ?? '';

				if ( is_array( $value ) ) {
					$value = implode( ', ', $value );
				} elseif ( is_object( $value ) ) {
					$value = get_object_vars( $value );
					$value = implode( ', ', $value );
				} else {
					$value = (string) $value;
				}

				return $value;
			},
			$pattern
		);

		return $replaced;
	}
}
