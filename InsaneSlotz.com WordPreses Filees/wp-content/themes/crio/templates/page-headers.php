<?php
/**
 * Page Headers Template
 *
 * This file gets the template part for the page header/title to display.
 *
 * @package Crio
 */
if ( is_archive() ) {
	get_template_part( 'templates/page-header', 'archive' );
}

if ( ! is_front_page() && is_home() ) {
	get_template_part( 'templates/page-header', 'blog' );
}

while ( have_posts() ) : the_post();
	if ( is_page() ) {
		get_template_part( 'templates/entry-header-page' );
	}

	if ( is_single() ) {
		get_template_part( 'templates/entry-header-single', get_post_format() );
	}
endwhile;
