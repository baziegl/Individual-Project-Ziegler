<?php
/**
 * Post List Header Content Template
 *
 * This file contains the markup for the post list with feat img in post content.
 *
 * @package Crio
 */
?>
<header <?php BoldGrid::add_class( 'entry_header', [ 'entry-header' ] ); ?>>
	<div <?php BoldGrid::add_class( 'featured_image', [ 'featured-imgage-header' ] ); ?>>
		<?php the_title( sprintf( '<p class="entry-title ' . get_theme_mod( 'bgtfw_blog_post_header_title_size' ) . '"><a ' . BoldGrid::add_class( 'blog_page_post_title', [ 'link' ], false ) . ' href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></p>' ); ?>
		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php boldgrid_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</div>
</header><!-- .entry-header -->
