<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Jet_Engine_CCT_Single_Page\Frontend;
use Jet_Engine_CCT_Single_Page\Settings;

$id = absint( get_query_var( '_ID' ) );
$base = get_query_var( 'is_single_cct_page' );

$item = $id ? Frontend::instance()->get_item( $id, $base ) : null;

if ( ! $item ) {
	status_header( 404 );
	nocache_headers();
	get_template_part( 404 );
	return;
}

$settings = Settings::instance()->get_settings();

$template_post_id = 0;

foreach ( $settings as $s ) {
	if ( $s['rewrite_base'] === $base ) {
		$template_post_id = absint( $s['listing_id'] ?? 0 );
		break;
	}
}

$content = '';

if ( $template_post_id ) {
	$content = jet_engine()->frontend->get_listing_item_content( $template_post_id );
}

if ( ! $content ) {
	$content = 'Template not found.';
}

get_header();
echo '<main id="primary" class="site-main">';
echo $content;
echo '</main>';
get_footer();
