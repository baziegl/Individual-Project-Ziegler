<?php
/**
 * 404 Template
 *
 * This file contains the 404 template used in this theme.
 *
 * @since 2.0
 * @package Crio
 */
?>

<div class="text-center">
	<h1><?php esc_html_e( '404: Page Not Found.', 'crio' ); ?></h1>
		<p><?php esc_html_e( 'The page you requested could not be found.', 'crio' ); ?></p>
	<div class="row">
		<div class="col-md-12">
			<?php get_search_form(); ?>
		</div>
	</div>
	<?php get_template_part( 'templates/recent-entries' ); ?>
</div>
