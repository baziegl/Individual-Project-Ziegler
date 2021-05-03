<?php
/**
 * Content Template
 *
 * This file contains the markup for the default content template.
 *
 * @package Crio
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( is_home() || is_archive() ) : ?>
		<?php get_template_part( 'templates/post-list-header', get_theme_mod( 'bgtfw_blog_post_header_feat_image_position' ) ); ?>
	<?php else : ?>
		<?php get_template_part( 'templates/entry-header', get_post_type() ); ?>
	<?php endif; ?>
	<div class="entry-content">

	<?php if ( is_home() || is_archive() ) : ?>
		<?php if ( 'content' === get_theme_mod( 'bgtfw_blog_post_header_feat_image_position' ) &&
				'show' === get_theme_mod( 'bgtfw_blog_post_header_feat_image_display' ) &&
				has_post_thumbnail() ) : ?>
			<div class="featured-image container">
				<?php the_post_thumbnail(); ?>
			</div>
		<?php endif; ?>
	<?php endif;?>
	<?php

		/* translators: %s: Name of current post */
		$bgtfw_content = get_theme_mod( 'bgtfw_pages_blog_blog_page_layout_content', 'excerpt' );
		if ( 'excerpt' === $bgtfw_content ) {
			Boldgrid_Framework_Content::the_excerpt();
		} else {
			the_content( sprintf( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'crio' ), the_title( '<span class="screen-reader-text">"', '"</span>', false ) ) );
		}
	?>
	<?php
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'crio' ),
			'after' => '</div>',
		) );
	?>
	</div><!-- .entry-content -->
	<footer class="entry-footer">
		<?php boldgrid_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
