<?php
/**
 * Header Template
 *
 * This file contains the markup for the header template.
 *
 * @package Crio
 */
?>
<header id="masthead" <?php BoldGrid::add_class( 'header', [ 'header' ] ); ?> role="banner" <?php BoldGrid_Framework_Schema::header( true ); ?>>
	<?php do_action( 'boldgrid_header_top' ); ?>
	<div class="custom-header-media">
		<?php the_custom_header_markup(); ?>
	</div>
	<?php echo BoldGrid::dynamic_header(); ?>
	<?php do_action( 'boldgrid_header_bottom' ); ?>
</header><!-- #masthead -->
