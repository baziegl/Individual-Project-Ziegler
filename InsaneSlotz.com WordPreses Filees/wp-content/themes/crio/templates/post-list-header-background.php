<?php
/**
 * Post List Header Background Template
 *
 * This file contains the markup for the post list with feat img as a background image for header.
 *
 * @package Crio
 */
?>
<header <?php BoldGrid::add_class( 'entry_header', [ 'entry-header' ] ); ?> <?php is_single() ? : bgtfw_featured_img_bg( $post->ID, true ); ?>>
	<div <?php BoldGrid::add_class( 'featured_image', [ 'featured-imgage-header' ] ); ?>>
		<?php if ( is_single() || is_page() ) : ?>
			<?php the_title( '<p class="h1 entry-title page-title text-center">', '</p>' ); ?>
		<?php else : ?>
			<?php the_title( sprintf( '<p class="entry-title ' . get_theme_mod( 'bgtfw_blog_post_header_title_size' ) . '"><a ' . BoldGrid::add_class( 'blog_page_post_title', [ 'link' ], false ) . ' href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></p>' ); ?>
		<?php endif; ?>
		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php boldgrid_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</div>
</header><!-- .entry-header -->
