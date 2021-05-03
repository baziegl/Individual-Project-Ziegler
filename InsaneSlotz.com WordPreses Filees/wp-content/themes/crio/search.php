<?php
/**
 * The template for displaying search results pages.
 *
 * @package Crio
 */

// Get number of results.
$crio_results_count = $wp_query->found_posts;
?>
	<div class="text-center">
		<div class="container">
			<h1><?php esc_html_e( 'Search', 'crio' ); ?> <span class="keyword">&ldquo;<?php the_search_query(); ?>&rdquo;</span></h1>
			<?php if ( '' == $crio_results_count || 0 == $crio_results_count ) { // No Results ?>
				<p><span class="label label-danger"><?php esc_html_e( 'No Results', 'crio' ); ?></span>&nbsp; <?php esc_html_e( 'Try different search terms.', 'crio' ); ?></p>
			<?php } else { // Results Found. ?>
				<p><span class="label label-success"><?php echo absint( $crio_results_count ) . ' ' . esc_html__( 'Results', 'crio' ); ?></span></p>
			<?php } // End results check. ?>
			<div class="row">
				<div class="<?php echo BoldGrid::display_sidebar() ? 'col-md-9' : 'col-md-12'; ?>">
					<?php get_search_form(); ?>
				</div>
			</div>
		</div> <!-- .container -->
	</div> <!-- .jumbotron -->
	<div class="bgtfw search-results<?php echo ! have_posts() ? ' text-center' : ''; ?>">
		<div class="row">
			<div class="<?php echo BoldGrid::display_sidebar() ? 'col-md-9' : 'col-md-12'; ?>">
				<?php if ( have_posts() ) : // Results Found. ?>
					<h1><?php esc_html_e( 'Search Results', 'crio' ); ?></h1>
					<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class(); ?>>
						<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
						<div class="entry">
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30, '...' ) ); ?></p>
						</div>
					</article>
					<hr />
					<?php endwhile; ?>
					<?php boldgrid_paging_nav(); ?>
			</div> <!-- .col-md-12 -->
		</div> <!-- .row -->
		<?php else : // No Results. ?>
		<div class="row">
			<div class="<?php echo BoldGrid::display_sidebar( ) ? 'col-md-9' : 'col-md-12'; ?>">
				<p><?php esc_html_e( 'Sorry. We couldn&rsquo;t find anything for that search. View one of our site&rsquo;s pages or a recent article below.', 'crio' ); ?></p>
			</div><!-- .col-md-12 -->
		</div> <!-- .row -->
		<?php get_template_part( 'templates/recent-entries' ); ?>
		<?php endif; ?>
	</div><!-- .container -->
