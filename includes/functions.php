<?php

use Jet_Engine_CCT_Single_Page\Frontend;

/**
 * Generate single CCT item link.
 *
 * @param string $value
 * @return string
 */
function jet_cct_single_page_link( $value ) {
	return Frontend::instance()->get_item_link( $value );
}