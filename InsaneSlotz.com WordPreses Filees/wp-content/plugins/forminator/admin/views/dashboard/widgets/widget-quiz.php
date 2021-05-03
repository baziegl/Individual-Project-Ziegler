<?php
$dashboard_settings = forminator_get_dashboard_settings( 'quizzes', array() );
$num_recent         = isset( $dashboard_settings['num_recent'] ) ? $dashboard_settings['num_recent'] : 5;
$published          = isset( $dashboard_settings['published'] ) ? filter_var( $dashboard_settings['published'], FILTER_VALIDATE_BOOLEAN ) : true;
$draft              = isset( $dashboard_settings['draft'] ) ? filter_var( $dashboard_settings['draft'], FILTER_VALIDATE_BOOLEAN ) : true;
$statuses           = array();

if ( $published ) {
	$statuses[] = Forminator_Base_Form_Model::STATUS_PUBLISH;
}

if ( $draft ) {
	$statuses[] = Forminator_Base_Form_Model::STATUS_DRAFT;
}

if ( 0 === $num_recent ) {
	return;
}
?>

	<div class="sui-box">

		<div class="sui-box-header">

			<h3 class="sui-box-title"><i class="sui-icon-academy" aria-hidden="true"></i><?php esc_html_e( 'Quizzes', Forminator::DOMAIN ); ?></h3>

		</div>

		<div class="sui-box-body">

			<p><?php esc_html_e( 'Create fun or challenging quizzes for your visitors to take and share on social media.', Forminator::DOMAIN ); ?></p>

			<?php if ( 0 === forminator_quizzes_total() ) { ?>

				<p><button class="sui-button sui-button-blue wpmudev-open-modal"
					data-modal="quizzes">
					<i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', Forminator::DOMAIN ); ?>
				</button></p>

			<?php } ?>

		</div>

		<?php if ( forminator_quizzes_total() > 0 ) { ?>

			<table class="sui-table sui-table-flushed">

				<thead>

					<tr>

						<th><?php esc_html_e( 'Name', Forminator::DOMAIN ); ?></th>
						<th class="fui-col-status"></th>

					</tr>

				</thead>

				<tbody>

					<?php
						$per_page = forminator_form_view_per_page();
						foreach ( forminator_quizzes_modules( $num_recent, $statuses ) as $index => $module ) {
							$page = ceil( ( $index + 1 ) / $per_page );
					?>

						<tr>

							<td class="sui-table-item-title"><?php echo esc_html( htmlspecialchars( $module['name'] ) ); ?></td>

							<td class="fui-col-status">

								<?php
								if ( 'publish' === $module['status'] ) {
									$status_class = 'published';
									$status_text  = esc_html__( 'Published', Forminator::DOMAIN );
								} else {
									$status_class = 'draft';
									$status_text  = esc_html__( 'Draft', Forminator::DOMAIN );
								}
								$has_leads = isset( $module['has_leads'] ) ? $module['has_leads'] : false;
								$leads_id  = isset( $module['leads_id'] ) ? $module['leads_id'] : 0;
								?>

								<span
										class="sui-status-dot sui-<?php echo esc_html( $status_class ); ?> sui-tooltip"
										data-tooltip="<?php echo esc_html( $status_text ); ?>"
								>
									<span aria-hidden="true"></span>
								</span>

								<a href="<?php echo admin_url( 'admin.php?page=forminator-quiz&view-stats=' . esc_attr( $module['id'] ) . '&paged=' . $page ); // phpcs:ignore ?>"
									class="sui-button-icon sui-tooltip"
									data-tooltip="<?php esc_html_e( 'View Status', Forminator::DOMAIN ); ?>">
									<i class="sui-icon-graph-line" aria-hidden="true"></i>
								</a>

								<div class="sui-dropdown">

									<button class="sui-button-icon sui-dropdown-anchor"
										aria-expanded="false"
										aria-label="<?php esc_html_e( 'More options', Forminator::DOMAIN ); ?>">
										<i class="sui-icon-widget-settings-config" aria-hidden="true"></i>
									</button>

									<ul>
										<li>
											<a href="<?php echo $this->getAdminEditUrl( $module['type'], $module['id'] ); // phpcs:ignore ?>">
												<i class="sui-icon-pencil" aria-hidden="true"></i> <?php esc_html_e( 'Edit', Forminator::DOMAIN ); ?>
											</a>
										</li>

										<li><button class="wpmudev-open-modal"
											data-modal="preview_quizzes"
											data-modal-title="<?php echo sprintf( '%s - %s', __( 'Preview Quiz', Forminator::DOMAIN ), htmlspecialchars( htmlspecialchars( forminator_get_form_name( $module['id'], 'quiz' ) ) ) ); // phpcs:ignore ?>"
											data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
                                 data-has-leads="<?php echo esc_attr( $has_leads ); ?>"
                                 data-leads-id="<?php echo esc_attr( $leads_id ); ?>"
											data-nonce-preview="<?php echo esc_attr( wp_create_nonce( 'forminator_load_module' ) ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_preview_quizzes' ) ); ?>">
											<i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( 'Preview', Forminator::DOMAIN ); ?>
										</button></li>

										<li>
											<button class="copy-clipboard" data-shortcode='[forminator_quiz id="<?php echo esc_attr( $module['id'] ); ?>"]'><i class="sui-icon-code" aria-hidden="true"></i> <?php esc_html_e( 'Copy Shortcode', Forminator::DOMAIN ); ?></button>
										</li>

										<li><a href="<?php echo admin_url( 'admin.php?page=forminator-entries&form_type=forminator_quizzes&form_id=' . $module['id'] ); // phpcs:ignore ?>"><i class="sui-icon-community-people" aria-hidden="true"></i> <?php esc_html_e( 'View Submissions', Forminator::DOMAIN ); ?></a></li>

										<li <?php echo ( $module['has_leads'] ) ? 'aria-hidden="true"' : ''; ?>><form method="post">
											<input type="hidden" name="forminator_action" value="clone">
											<input type="hidden" name="form_type" value="quiz">
											<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
											<?php
											$clone_nonce = esc_attr( 'forminator-nonce-clone-' . $module['id'] );
											wp_nonce_field( $clone_nonce, $clone_nonce );
											?>
											<?php if ( $module['has_leads'] ): ?>
												<button type="submit" disabled="disabled" class="fui-button-with-tag sui-tooltip sui-tooltip-left sui-constrained" data-tooltip="<?php esc_html_e( 'Duplicate isn\'t supported at the moment for the quizzes with lead capturing enabled.', Forminator::DOMAIN ); ?>">
													<span class="sui-icon-page-multiple" aria-hidden="true"></span>
													<span class="fui-button-label"><?php esc_html_e( 'Duplicate', Forminator::DOMAIN ); ?></span>
													<span class="sui-tag sui-tag-blue sui-tag-sm"><?php echo esc_html__( 'Coming soon', Forminator::DOMAIN ); ?></span>
												</button>
											<?php else: ?>
												<button type="submit">
													<i class="sui-icon-page-multiple" aria-hidden="true"></i> <?php esc_html_e( 'Duplicate', Forminator::DOMAIN ); ?>
												</button>
											<?php endif; ?>
										</form></li>

										<?php if ( Forminator::is_import_export_feature_enabled() ) : ?>
											<?php if ( $module['has_leads'] ): ?>
												<li aria-hidden="true"><a href="#" class="fui-button-with-tag sui-tooltip sui-tooltip-left"
													data-tooltip="<?php esc_html_e( 'Export isn\'t supported at the moment for the quizzes with lead capturing enabled.', Forminator::DOMAIN ); ?>">
													<span class="sui-icon-cloud-migration" aria-hidden="true"></span>
													<span class="fui-button-label"><?php esc_html_e( 'Export', Forminator::DOMAIN ); ?></span>
													<span class="sui-tag sui-tag-blue sui-tag-sm"><?php echo esc_html__( 'Coming soon', Forminator::DOMAIN ); ?></span>
												</a></li>
											<?php else: ?>
												<li><a href="#"
													class="wpmudev-open-modal"
													data-modal="export_quiz"
													data-modal-title=""
													data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
													data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_export_quiz' ) ); ?>">
													<i class="sui-icon-cloud-migration" aria-hidden="true"></i> <?php esc_html_e( 'Export', Forminator::DOMAIN ); ?>
												</a></li>
											<?php endif; ?>

										<?php endif; ?>

										<li><a href="#"
											class="wpmudev-open-modal"
											data-modal="delete-module"
											data-modal-title="<?php esc_attr_e( 'Delete Quiz', Forminator::DOMAIN ); ?>"
											data-modal-content="<?php esc_attr_e( 'Are you sure you wish to permanently delete this quiz?', Forminator::DOMAIN ); ?>"
											data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminatorQuizFormRequest' ) ); ?>">
												<i class="sui-icon-trash" aria-hidden="true"></i> <?php esc_html_e( 'Delete', Forminator::DOMAIN ); ?>
											</a></li>

									</ul>

								</div>

							</td>

						</tr>

					<?php } ?>

				</tbody>

			</table>

			<div class="sui-box-footer">

				<button class="sui-button sui-button-blue wpmudev-open-modal forminator-create-quiz"
					data-modal="quizzes">
					<i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', Forminator::DOMAIN ); ?>
				</button>

				<div class="sui-actions-right">
					<p class="sui-description"><a href="<?php echo admin_url( 'admin.php?page=forminator-quiz' ); // phpcs:ignore ?>" class="sui-link-gray"><?php esc_html_e( 'View all quizzes', Forminator::DOMAIN ); ?></a></p>
				</div>

			</div>

		<?php } ?>

	</div>
