<?php
/**
 * Archive Page Entry Header Template
 *
 * This file contains the markup for the archive page's entry header.
 *
 * @package Crio
 */
?>
<div <?php BoldGrid::add_class( 'page_header_wrapper', [ 'page-header-wrapper' ] ); ?>>
	<header <?php BoldGrid::add_class( 'archive_page_title', [ 'page-header' ] ); ?>>
		<div <?php BoldGrid::add_class( 'featured_image', [ 'featured-imgage-header' ] ); ?>>
			<?php
				$crio_queried_obj = get_queried_object_id();
				$crio_archive_url = is_author() ? get_author_posts_url( $crio_queried_obj ) : get_term_link( $crio_queried_obj );
				if ( ! is_wp_error( $crio_archive_url ) ) {
					printf(
						'<h1 class="page-title %1$s"><a %2$s href="%3$s" rel="bookmark">%4$s</a></h1>',
						esc_attr( get_theme_mod( 'bgtfw_global_title_size' ) ),
						BoldGrid::add_class( 'pages_title', [ 'link' ], false ),
						esc_url( $crio_archive_url ),
						wp_kses_post( get_the_archive_title() )
					);
				} else {
					printf(
						'<h1 class="page-title %1$s"><span %2$s>%3$s</span></h1>',
						esc_attr( get_theme_mod( 'bgtfw_global_title_size' ) ),
						BoldGrid::add_class( 'pages_title', [ 'link' ], false ),
						wp_kses_post( get_the_archive_title() )
					);
				}

				wp_kses_post( the_archive_description( '<div class="taxonomy-description">', '</div>' ) );
			?>
		</div>
	</header>
</div>
