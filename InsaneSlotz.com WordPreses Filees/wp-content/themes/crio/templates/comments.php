<?php
/**
 * The template for displaying comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package Crio
 */
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments">
	<?php do_action( 'boldgrid_comments_before' ) ?>
	<?php do_action( 'boldgrid_comments' ) ?>
	<?php do_action( 'boldgrid_comments_after' ) ?>
</section>
