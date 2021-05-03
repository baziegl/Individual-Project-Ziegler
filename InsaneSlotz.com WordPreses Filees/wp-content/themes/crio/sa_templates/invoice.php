
<?php
// phpcs:ignoreFile
/**
 * DO NOT EDIT THIS FILE! Instead customize it via a theme override.
 *
 * Any edit will not be saved when this plugin is upgraded. Not upgrading will prevent you from receiving new features,
 * limit our ability to support your site and potentially expose your site to security risk that an upgrade has fixed.
 *
 * Theme overrides are easy too, so there's no excuse...
 *
 * https://sproutinvoices.com/support/knowledgebase/sprout-invoices/customizing-templates/
 *
 * You find something that you're not able to customize? We want your experience to be awesome so let support know and we'll be able to help you.
 * @package sa_templates
 */

do_action( 'pre_si_invoice_view' ); ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<html>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title><?php wp_title() ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />

		<script type="text/javascript" src="<?php echo site_url() ?>/wp-includes/js/jquery/jquery.js"></script>
		<script type="text/javascript" src="<?php echo site_url() ?>/wp-includes/js/jquery/jquery-migrate.min.js"></script>

		<?php si_head( true ); ?>

		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,700,900">
		<meta name="robots" content="noindex, nofollow" />
	</head>

	<body id="invoice" <?php body_class( 'si_default_theme' ); ?>>
		<header class="row" id="header">
			<div class="inner">

				<div class="row messages">
					<?php si_display_messages(); ?>
				</div>

				<?php if ( get_theme_mod( 'si_logo' ) ) : ?>
					<img src="<?php echo esc_url( get_theme_mod( 'si_logo', si_doc_header_logo_url() ) ); ?>" alt="document logo" >
				<?php else : ?>
					<img src="<?php echo esc_url( si_doc_header_logo_url() ) ?>" alt="document logo" >
				<?php endif; ?>

				<div class="row intro">
					<h1><?php the_title() ?></h1>
					<span><?php printf( __( 'Invoice %1$s', 'crio' ), si_get_invoice_id() ) ?></span>
				</div>

				<div class="row history_message">
					<?php if ( $last_updated = si_doc_last_updated() ) :  ?>
						<?php $days_since = si_get_days_ago( $last_updated ); ?>
						<?php if ( 2 > $days_since ) :  ?>
								<a class="open" href="#history"><?php printf( __( 'Recently Updated', 'crio' ), $days_since ) ?></a>
							<?php else : ?>
								<a class="open" href="#history"><?php printf( __( 'Updated %1$s Days Ago', 'crio' ), $days_since ) ?></a>
							<?php endif ?>
					<?php endif ?>
				</div>
			</div>
		</header>

		<section class="row" id="intro">
			<div class="inner">
				<div class="column">
					<span><?php printf( __( 'Issued: %1$s by:', 'crio' ), date_i18n( get_option( 'date_format' ), si_get_invoice_issue_date() ) ) ?></span>
					<h2><?php si_company_name() ?></h2>
					<?php si_doc_address() ?>
				</div>

				<div class="column">
					<?php if ( si_get_invoice_client_id() ) :  ?>
						<span><?php printf( __( 'Due: %1$s to:', 'crio' ), date_i18n( get_option( 'date_format' ), si_get_invoice_due_date() ) ) ?></span>
						<h2><?php echo get_the_title( si_get_invoice_client_id() ) ?></h2>

						<?php do_action( 'si_document_client_addy' ) ?>

						<?php si_client_address( si_get_invoice_client_id() ) ?>
					<?php else : ?>
						<span><?php _e( 'Due:', 'crio' ) ?></span>
						<h2><?php si_invoice_due_date() ?></h2>
					<?php endif ?>

				</div>

				<?php do_action( 'si_document_vcards' ) ?>

			</div>
		</section>

		<section class="row" id="details">
			<div class="inner">
				<div class="row item">
					<?php do_action( 'si_document_more_details' ) ?>
				</div>
			</div>
		</section>

		<?php do_action( 'si_doc_line_items', get_the_id() ) ?>

		<section class="row" id="signature">
			<div class="inner">
				<div class="row item">
					<?php do_action( 'si_signature_section' ) ?>
				</div>
			</div>
		</section>

		<section class="row" id="notes">
			<div class="inner">
				<?php if ( strlen( si_get_invoice_notes() ) > 1 ) : ?>
					<div class="row item">
						<div class="row header">
							<h3><?php esc_html_e( 'Info &amp; Notes', 'crio' ) ?></h3>
						</div>
						<?php si_invoice_notes() ?>
					</div>
				<?php endif ?>

				<?php if ( strlen( si_get_invoice_terms() ) > 1 ) : ?>

					<div class="row item">
						<div class="row header">
							<h3><?php esc_html_e( 'Terms &amp; Conditions', 'crio' ) ?></h3>
						</div>
						<?php si_invoice_terms() ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( si_get_invoice_balance() ) : ?>
			<?php si_payment_options_view(); ?>
		<?php else : ?>
			<section class="row" id="paybar">
				<div class="inner">
					<?php do_action( 'si_default_theme_inner_paybar' ) ?>

					<?php if ( 'complete' === si_get_invoice_status() ) :  ?>
						<?php printf( 'Total of <strong>%1$s</strong> has been <strong>Paid</strong>', sa_get_formatted_money( si_get_invoice_total() ) ); ?>
					<?php else : ?>
						<?php printf( 'Total of <strong>%1$s</strong> has been <strong>Reconciled</strong>', sa_get_formatted_money( si_get_invoice_total() ) ); ?>
					<?php endif ?>

					<?php do_action( 'si_default_theme_pre_no_payment_button' ) ?>

					<?php do_action( 'si_pdf_button' ) ?>

					<?php do_action( 'si_signature_button' ) ?>

					<?php do_action( 'si_default_theme_no_payment_button' ) ?>
				</div>
			</section>

		<?php endif ?>

		<?php if ( apply_filters( 'si_show_invoice_history', true ) ) : ?>
			 <section class="panel closed" id="history">
				<a class="close" href="#history">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
					<path d="M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z"/>
				</svg>
				</a>

				<div class="inner">
					<h2><?php _e( 'Invoice History', 'crio' ) ?></h2>
					<div class="history">
						<?php foreach ( si_doc_history_records() as $item_id => $data ) : ?>
							<?php $days_since = (int) si_get_days_ago( strtotime( $data['post_date'] ) ); ?>
							<article class=" <?php echo esc_attr( $data['status_type'] ); ?>">
								<span class="posted">
									<?php
										$type = ( 'comment' === $data['status_type'] ) ? sprintf( __( 'Comment by %s ', 'crio' ), $data['type'] ) : $data['type'] ;
											?>
									<?php if ( 0 === $days_since ) :  ?>
										<?php printf( '%1$s today', $type ) ?>
									<?php elseif ( 2 > $days_since ) :  ?>
										<?php printf( '%1$s %2$s day ago', $type, $days_since ) ?>
									<?php else : ?>
										<?php printf( '%1$s %2$s days ago', $type, $days_since ) ?>
									<?php endif ?>
								</span>

								<?php if ( SI_Notifications::RECORD === $data['status_type'] ) : ?>
									<p>
										<?php echo esc_html( $update_title ) ?>
										<br/><a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $item_id ?>" id="show_notification_tb_link_<?php echo (int) $item_id ?>" class="thickbox si_tooltip notification_message" title="<?php esc_attr_e( 'View Message', 'crio' ) ?>"><?php esc_html_e( 'View Message', 'crio' ) ?></a>
									</p>
									<div id="notification_message_<?php echo (int) $item_id ?>" class="cloak">
										<?php echo wpautop( $data['content'] ) ?>
									</div>
								<?php elseif ( SI_Invoices::VIEWED_STATUS_UPDATE === $data['status_type'] ) : ?>
									<p>
										<?php echo $data['update_title']; ?>
									</p>
								<?php else : ?>
									<?php echo wpautop( $data['content'] ) ?>
								<?php endif ?>
							</article>
						<?php endforeach ?>
					</div>
				</div>
			</section>
		<?php endif ?>

		<div id="footer_credit">
			<?php do_action( 'si_document_footer_credit' ) ?>
			<!--<p><?php esc_html_e( 'Powered by Sprout Invoices', 'crio' ) ?></p>-->
		</div><!-- #footer_messaging -->
		<?php wp_footer(); ?>
	</body>
	<?php do_action( 'si_document_footer' ) ?>
	<?php si_footer() ?>

	<?php printf( '<!-- Template Version v%s -->', Sprout_Invoices::SI_VERSION ); ?>
</html>
<?php do_action( 'invoice_viewed' ) ?>
<?php do_action( 'invoice_viewed' ) ?>
