<?php
/**
 * Entry Header
 *
 * This file contains the markup for entry headers.
 *
 * @package Crio
 */
do_action( 'boldgrid_before_entry_title' ); ?>
<header <?php BoldGrid::add_class( 'entry_header', [ 'entry-header' ] ); ?> <?php bgtfw_featured_img_bg( $post->ID ); ?>>
	<div <?php BoldGrid::add_class( 'featured_image', [ 'featured-imgage-header' ] ); ?>>
		<?php if ( is_single() ) : ?>
			<?php the_title( sprintf( '<p class="entry-title page-title ' . get_theme_mod( 'bgtfw_global_title_size' ) . '"><a ' . BoldGrid::add_class( 'posts_title', [ 'link' ], false ) . ' href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></p>' ); ?>
		<?php elseif ( is_page() ) : ?>
			<?php the_title( sprintf( '<p class="entry-title page-title ' . get_theme_mod( 'bgtfw_global_title_size' ) . '"><a ' . BoldGrid::add_class( 'pages_title', [ 'link' ], false ) . ' href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></p>' ); ?>
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
<?php do_action( 'boldgrid_after_entry_title' ); ?>
