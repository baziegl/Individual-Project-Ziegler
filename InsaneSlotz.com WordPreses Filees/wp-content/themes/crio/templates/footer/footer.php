<?php
/**
 * Footer Template
 *
 * This file contains the markup for the footer template.
 *
 * @since 2.0
 * @package Crio
 */
?>
<footer id="colophon" <?php BoldGrid::add_class( 'footer', [ 'site-footer' ] ); ?> role="contentinfo" <?php BoldGrid_Framework_Schema::footer( true ); ?>>
	<?php do_action( 'boldgrid_footer_top' ); ?>
	<div <?php BoldGrid::add_class( 'footer_content', [ 'bgtfw-footer', 'footer-content' ] ); ?>>
		<?php echo BoldGrid::dynamic_footer(); ?>
	</div>
	<?php do_action( 'boldgrid_footer_bottom' ); ?>
</footer><!-- #colophon -->
