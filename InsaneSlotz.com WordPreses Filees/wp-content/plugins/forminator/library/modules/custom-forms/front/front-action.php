<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front ajax for custom forms
 *
 * @since 1.0
 */
class Forminator_CForm_Front_Action extends Forminator_Front_Action {

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = 'custom-forms';

	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Response message
	 *
	 * @var array
	 */
	private static $response = array();

	/**
	 * Render ID of Form
	 *
	 * @var array
	 */
	private static $render_id = '';

	/**
	 * Hold $_POST submitted data
	 *
	 * @since 1.5.1
	 * @var array
	 */
	private $_post_data = array();

	public function __construct() {
		parent::__construct();

		//Save entries
		if ( ! empty( $this->entry_type ) ) {
			add_action( 'wp_ajax_forminator_pp_create_order', array( $this, 'create_paypal_order' ) );
			add_action( 'wp_ajax_nopriv_forminator_pp_create_order', array( $this, 'create_paypal_order' ) );
		}
	}

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return Forminator_Front_Action
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Create PayPal order
	 *
	 * @since 1.14.3
	 *
	 */
	public function create_paypal_order() {
		$body = trim( file_get_contents( "php://input" ) );
		$data = json_decode( $body, true );

		// Check if form data is set
		if ( isset( $data['form_data'] ) && isset( $data['form_data']['purchase_units'] ) ) {

			// Check if payment amount is bigger than zero
			if ( floatval( $data['form_data']['purchase_units'][0]['amount']['value'] ) <= 0 ) {
				wp_send_json_error( esc_html__( 'The payment total must be greater than 0.', Forminator::DOMAIN ) );
			}

			$amount = $data['form_data']['purchase_units'][0]['amount']['value'];
			$data['form_data']['purchase_units'][0]['amount']['value'] = number_format((float)$amount, 2, '.', '');

			$paypal = new Forminator_PayPal_Express();

			$request = array_merge( array( 'intent' => 'CAPTURE' ), $data['form_data'] );
			$request = apply_filters( 'forminator_paypal_create_order_request', $request, $data );

			$order = $paypal->create_order( $request, $data['mode'] );

			if ( is_wp_error( $order ) ) {
				wp_send_json_error( esc_html__( 'Cannot create a new order on PayPal. If the error persists, please contact us for further assistance.', Forminator::DOMAIN ) );
			}

			$response = array(
				'order_id'	=> $order->id
			);

			wp_send_json_success( $response );
		}
	}

	/**
	 * Update payment amount
	 *
	 * @since 1.7.3
	 */
	public function update_payment_amount() {
		$post_data = $this->get_post_data();
		$form_id   = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false; // WPCS: CSRF OK

		if ( $form_id ) {
			$custom_form = Forminator_Custom_Form_Model::model()->load( $form_id );
			$setting     = $this->get_form_settings( $custom_form );

			if ( is_object( $custom_form ) ) {
				$submitted_data = $post_data;

				$submitted_data = $this->replace_hidden_field_values( $custom_form, $submitted_data );

				$pseudo_submitted_data = $this->build_pseudo_submitted_data( $custom_form, $submitted_data );

				if ( $custom_form->has_stripe_field() ) {
					$fields           = $custom_form->get_fields();
					$field_classes    = forminator_fields_to_array();

					foreach ( $fields as $field ) {
						$field_array = $field->to_formatted_array();
						$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

						if ( 'stripe' === $field_type ) {
							$field_id = Forminator_Field::get_property( 'element_id', $field_array );

							$forminator_stripe_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;

							if ( $forminator_stripe_field instanceof Forminator_Stripe ) {
								$currency = Forminator_Field::get_property( 'currency', $field_array, $this->get_default_currency() );
								$mode     = Forminator_Field::get_property( 'mode', $field_array, 'test' );

								$forminator_stripe_field->update_paymentIntent(
									$submitted_data['paymentid'],
									$pseudo_submitted_data[ $field_id ],
									$submitted_data,
									$field_array,
									$pseudo_submitted_data
								);
							}

							// process only first stripe field
							break;
						}
					}

				} else {
					$response = array(
						'message' => __( "Error: Stripe field doesn't exist in your form!", Forminator::DOMAIN ),
						'errors'  => array()
					);
				}
			} else {
				$response = array(
					'message' => __( "Error: Form object is corrupted!", Forminator::DOMAIN ),
					'errors'  => array()
				);
			}
		} else {
			$response = array(
				'message' => __( "Error: Your form ID doesn't exist!", Forminator::DOMAIN ),
				'errors'  => array()
			);
		}

		wp_send_json_error( $response );
	}

	/**
	 * Get default currency
	 *
	 * @return string
	 */
	private function get_default_currency() {
		try {
			$stripe = new Forminator_Gateway_Stripe();

			return $stripe->get_default_currency();

		} catch ( Forminator_Gateway_Exception $e ) {
			return 'USD';
		}
	}

	/**
	 * Handle submit
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function handle_submit() {
		$this->_post_data = $this->get_post_data();
		$post_data        = $this->_post_data;

		$form_id = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false; // WPCS: CSRF OK

		if ( $form_id ) {
			/**
			 * Action called before full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 */
			do_action( 'forminator_custom_form_before_handle_submit', $form_id );

			$response = $this->handle_form( $form_id );

			// sanitize front end message
			if ( is_array( $response ) && isset( $response['message'] ) && ! empty( $response['message'] ) ) {
				$response['message'] = wp_kses_post( $response['message'] );
			}

			/**
			 * Filter submit response
			 *
			 * @since 1.0.2
			 *
			 * @param array $response - the post response
			 * @param int $form_id - the form id
			 *
			 * @return array $response
			 */
			$response = apply_filters( 'forminator_custom_form_submit_response', $response, $form_id );

			/**
			 * Action called after full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 * @param array $response - the post response
			 */
			do_action( 'forminator_custom_form_after_handle_submit', $form_id, $response );
			if ( $response && is_array( $response ) ) {
				if ( ! empty( $response ) ) {
					self::$response = $response;
					add_action( 'forminator_cform_post_message', array( $this, 'form_response_message' ), 10, 2 );
					if ( ! $response['success'] && isset( $response['errors'] ) ) {
						add_action( 'wp_footer', array( $this, 'footer_message' ) );
					}
					if ( $response['success'] ) {
						if ( isset( $response['url'] ) && ( ! isset( $response['newtab'] ) || 'sametab' === $response['newtab'] ) ) {

							//wp redirect for sametab
							wp_redirect( $response['url'] );
							exit;

						} else {
							// cleanup submitted data
							$_POST = array();
						}
					}
				}
			}
		}
	}

	/**
	 * Save entry
	 *
	 * @since 1.0
	 * @since 1.1 Change $_POST to get_post_data
	 * @return void
	 */
	public function save_entry() {
		$this->_post_data = $this->get_post_data();
		$post_data        = $this->_post_data;

		if ( $this->validate_ajax( 'forminator_submit_form', 'POST', 'forminator_nonce' ) ) {
			$form_id    = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false; // WPCS: CSRF OK

			if ( $form_id ) {

				/**
				 * Action called before form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param string $type - the submit type. In this case submit
				 */
				do_action( 'forminator_custom_form_before_save_entry', $form_id, 'submit' );

				$response = $this->handle_form( $form_id );

				// sanitize front end message
				if ( is_array( $response ) && isset( $response['message'] ) && ! empty( $response['message'] ) ) {
					$response['message'] = wp_kses_post( $response['message'] );
				}

				/**
				 * Filter ajax response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $form_id - the form id
				 * @param string $type - the submit type. In this case submit
				 *
				 */
				$response = apply_filters( 'forminator_custom_form_ajax_submit_response', $response, $form_id, 'submit' );


				/**
				 * Action called after form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param array $response - the post response
				 * @param string $type - the submit type. In this case submit
				 */
				do_action( 'forminator_custom_form_after_save_entry', $form_id, $response, 'submit' );

				if ( ! $response['success'] && isset( $response['errors'] ) ) {
					wp_send_json_error( $response );
				} else {
					wp_send_json_success( $response );
				}
			}
		}
	}

	/**
	 * Save entry
	 *
	 * @since 1.6
	 * @return void
	 */
	public function save_entry_preview() {
		$this->_post_data = $this->get_post_data();
		$post_data        = $this->_post_data;

		if ( $this->validate_ajax( 'forminator_submit_form', 'POST', 'forminator_nonce' ) ) {
			$form_id    = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false; // WPCS: CSRF OK
			$payment_id = isset( $post_data['payment_id'] ) ? sanitize_text_field( $post_data['payment_id'] ) : false; // WPCS: CSRF OK

			if ( $form_id ) {

				/**
				 * Action called before form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param string $type - the submit type. In this case submit
				 */
				do_action( 'forminator_custom_form_before_save_entry', $form_id, 'submit' );

				$response = $this->handle_form( $form_id, true );

				// sanitize front end message
				if ( is_array( $response ) && isset( $response['message'] ) && ! empty( $response['message'] ) ) {
					$response['message'] = wp_kses_post( $response['message'] );
				}

				/**
				 * Filter ajax response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $form_id - the form id
				 * @param string $type - the submit type. In this case submit
				 *
				 */
				$response = apply_filters( 'forminator_custom_form_ajax_submit_response', $response, $form_id, 'submit' );


				/**
				 * Action called after form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param array $response - the post response
				 * @param string $type - the submit type. In this case submit
				 */
				do_action( 'forminator_custom_form_after_save_entry', $form_id, $response, 'submit' );

				if ( ! $response['success'] && isset( $response['errors'] ) ) {
					wp_send_json_error( $response );
				} else {
					wp_send_json_success( $response );
				}
			}
		}
	}

	/**
	 * Handle form
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 * @since 1.5.1 utilize `_post_data` which already defined on submit
	 *
	 * @param $form_id
	 * @param $preview
	 *
	 * @return array|bool
	 */
	public function handle_form( $form_id, $preview = false ) {
		$submitted_data        = $this->_post_data;
		$pseudo_submitted_data = array();

		/** @var Forminator_Custom_Form_Model $custom_form */
		$custom_form = Forminator_Custom_Form_Model::model()->load( $form_id );
		if ( is_object( $custom_form ) ) {
			$setting          = $this->get_form_settings( $custom_form );
			$form_submit      = $custom_form->form_can_submit();
			$can_submit       = $form_submit['can_submit'];
			$submit_error     = $form_submit['error'];
			$prevent_store    = $custom_form->is_prevent_store();
			$submission_behav = $custom_form->get_submission_behaviour();
			$form_type        = isset( $setting['form-type'] ) ? $setting['form-type'] : '';

			// If preview, prevent storing
			if ( $preview ) {
				$prevent_store = true;
			}

			if ( isset( $setting['logged-users'] ) && $setting['logged-users'] ) {
				$submit_error = __( "Only logged in users can submit this form.", Forminator::DOMAIN );
				$can_submit   = is_user_logged_in();
			}

			// disable submit if status is draft.
			if ( Forminator_Custom_Form_Model::STATUS_DRAFT === $custom_form->status ) {
				$submit_error = __( "This form is not published.", Forminator::DOMAIN );
				$can_submit   = false;
			}

			/**
			 * Filter to check if current user can submit the form
			 *
			 * @since 1.0.2
			 *
			 * @param bool $can_submit - if can submit depending on above conditions
			 * @param int $form_id - the form id
			 *
			 * @return bool $can_submit - true|false
			 */
			$can_submit = apply_filters( 'forminator_custom_form_handle_form_user_can_submit', $can_submit, $form_id );

			if ( $can_submit ) {
				$submit_errors         = array();
				$entry                 = new Forminator_Form_Entry_Model();
				$entry->entry_type     = $this->entry_type;
				$entry->form_id        = $form_id;
				$field_data_array      = array();
				$fields                = $custom_form->get_fields();
				$field_suffix          = Forminator_Form_Entry_Model::field_suffix();
				$field_forms           = forminator_fields_to_array();
				$product_fields        = array();
				$calculation_exists    = false;
				$stripe_exists         = false;
				$paypal_exists         = false;
				$registration_exists   = false;
				$select_field_value    = array();
				$login_user            = array();
				$form_upload_data      = array();
				$postdata_fields       = array();
				$upload_in_customfield = array();

				// set default response to error message
				$response = array(
					'message' => __( "At least one field must be filled out to submit the form.", Forminator::DOMAIN ),
					'errors'  => array(),
					'success' => false,
					'behav'   => $submission_behav,
				);

				if ( ! is_null( $fields ) ) {

					// Ignore captcha re-check if we have Stripe field
					if ( ! $custom_form->has_stripe_field() ) {

						// verify captcha before any else
						$captcha_field = $custom_form->get_captcha_field();
						if ( $captcha_field && isset( $field_forms['captcha'] ) && $field_forms['captcha'] instanceof Forminator_Captcha ) {
							$captcha_field_array   = $captcha_field->to_formatted_array();
							$field_id              = Forminator_Field::get_property( 'element_id', $captcha_field_array );
							$captcha_user_response = '';
							if ( isset( $submitted_data['g-recaptcha-response'] ) ) {
								$captcha_user_response = $submitted_data['g-recaptcha-response'];
							}

							/**
							 * Filter captcha user response, default is from `g-recaptcha-response`
							 *
							 * @since 1.5.3
							 *
							 * @param string $captcha_user_response
							 * @param int $form_id
							 * @param array $submitted_data
							 *
							 * @return string captcha user response
							 */
							$captcha_user_response = apply_filters( 'forminator_captcha_user_response', $captcha_user_response, $form_id, $submitted_data );

							/** @var Forminator_Field $field_captcha_obj */
							$field_captcha_obj = $field_forms['captcha'];
							if ( $field_captcha_obj->is_available( $captcha_field_array ) ) {
								$field_captcha_obj->validate( $captcha_field_array, $captcha_user_response );

								$valid_response = $field_captcha_obj->is_valid_entry();
								if ( is_array( $valid_response ) && isset( $valid_response[ $field_id ] ) ) {

									return array(
										'message' => $valid_response[ $field_id ],
										'errors'  => array(),
										'success' => false,
										'behav'   => $submission_behav,
									);
								}
							}
						}
					}

					$ignored_field_types = Forminator_Form_Entry_Model::ignored_fields();

					$submitted_data = $this->replace_hidden_field_values( $custom_form, $submitted_data );

					if ( isset( $submitted_data['forminator-multifile-hidden'] ) ) {
						$form_upload_data      = json_decode( stripslashes( $submitted_data['forminator-multifile-hidden'] ), true );
					}
					// build pseudo submit data first to later usage
					$pseudo_submitted_data = $this->build_pseudo_submitted_data( $custom_form, $submitted_data );

					$hidden_fields  = array();
					foreach ( $fields as $field_index => $field ) {
						$field_array = $field->to_formatted_array();
						$field_type  = $field_array["type"];
						if ( in_array( $field_type, $ignored_field_types, true ) ) {
							continue;
						}

						$is_hidden      = false;
						$form_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;
						if ( $form_field_obj ) {
							if ( 'stripe' === $field_type ) {
								$is_hidden = $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form, $hidden_fields );
							} else {
								$is_hidden = $form_field_obj->is_hidden( $field_array, $submitted_data, [], $custom_form, $hidden_fields );
							}
						}

						// Store the hidden fields
						if ( $is_hidden ) {
							$hidden_fields[] = $field->slug;
						}

						// Exclude calculation field, we will process later
						if ( 'calculation' === $field_type ) {
							$calculation_exists = true;
							continue;
						}

						// Exclude stripe field, we will process later
						if ( 'paypal' === $field_type ) {
							$paypal_exists = true;
							continue;
						}
						// Exclude paypal field, we will process later
						if ( 'stripe' === $field_type ) {
							$form_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;
							if ( $form_field_obj ) {
								$is_hidden = $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form );
								if ( ! $is_hidden ) {
									$stripe_exists = true;
									continue;
								}
							}
						}
						if ( 'hidden' === $field_type && ! empty( $field_array['element_id'] )
								&& ! empty( $field_array['default_value'] ) && 'user_ip' === $field_array['default_value'] ) {
							$submitted_data[ $field_array['element_id'] ] = Forminator_Geo::get_user_ip();
						}

						if ( isset( $field->slug ) ) {
							$field_id     = Forminator_Field::get_property( 'element_id', $field_array );
							$mod_field_id = $field_id;
							$field_data   = array();
							$field_type   = $field_array["type"];
							$post_file    = false;

							if ( ! isset( $submitted_data[ $field_id ] ) ) {
								foreach ( $field_suffix as $suffix ) {
									$mod_field_id = $field_id . '-' . $suffix;
									if ( isset( $submitted_data[ $mod_field_id ] ) ) {
										$field_data[ $suffix ] = $submitted_data[ $mod_field_id ];
									} elseif ( isset( $_FILES[ $mod_field_id ] ) ) {
										if ( "postdata" === $field_type && 'post-image' === $suffix ) {
											$post_file = $mod_field_id;
										}
									}
								}

								if ( "postdata" === $field_type ) {
									$post_type     = Forminator_Field::get_property( 'post_type', $field_array, 'post' );
									$category_list = forminator_post_categories( $post_type );
									if ( ! empty( $category_list ) ) {
										foreach ( $category_list as $category ) {
											$mod_field_id = $field_id . '-' . $category['value'];
											if ( isset( $submitted_data[ $mod_field_id ] ) ) {
												$field_data[ $category['value'] ] = $submitted_data[ $mod_field_id ];
											}
										}
									}
									$custom_vars = Forminator_Field::get_property( 'post_custom_fields', $field_array );
									if ( ! empty( $custom_vars ) ) {
										$custom_meta = Forminator_Field::get_property( 'options', $field_array );
										if ( ! empty( $custom_meta ) ) {
											$i = 1;
											foreach ( $custom_meta as $meta ) {
												$value = ! empty( $meta['value'] ) ? trim( $meta['value'] ) : '';
												$label = $meta['label'];

												if ( strpos( $value, '{' ) !== false && strpos( $value, '{upload' ) === false ) {
                                                    $value = forminator_replace_form_data( $value, $submitted_data );
                                                    $value = forminator_replace_variables( $value, $form_id );
												} elseif ( isset( $submitted_data[ $value ] ) ) {
													$value = $submitted_data[ $value ];
												}

                                                // Store data that will be used later by upload fields
                                                if ( strpos( $value, '{upload' ) !== false ) {
                                                    $upload_in_customfield[] = array(
                                                        'postdata_id' => $field_id,
                                                        'upload_id'   => trim( $value, '{}' ),
                                                        'uploads'     => '',
                                                    );
                                                }

												$field_data['post-custom'][] = array(
													'key'   => $label,
													'value' => $value,
												);
												$i ++;
											}
										}
									}
								}
							} else {
								$field_data = $submitted_data[ $field_id ];
							}

							if ( isset( $field_forms[ $field_type ] ) && ! empty( $field_forms[ $field_type ] ) ) {
								/** @var Forminator_Field $form_field_obj */
								$form_field_obj = $field_forms[ $field_type ];

								// is conditionally hidden go to next field
								if ( $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form, $hidden_fields ) ) {
									continue;
								}

								if ( "upload" === $field_type ) {
									$file_type = Forminator_Field::get_property( 'file-type', $field_array, 'single' );
									$upload_method = Forminator_Field::get_property( 'upload-method', $field_array, 'ajax' );
									/** @var  Forminator_Upload $form_field_obj */
									if ( 'multiple' === $file_type && 'ajax' === $upload_method ) {
										$upload_multiple_data = isset( $form_upload_data[ $field->slug ] ) ? $form_upload_data[ $field->slug ] : array();
										$upload_data          = $form_field_obj->handle_ajax_multifile_upload( $upload_multiple_data, $field_array );
									} elseif ( 'multiple' === $file_type && 'submission' === $upload_method ) {
										$upload_multiple_data = isset( $_FILES[ $field->slug ] ) ? $_FILES[ $field->slug ] : array();
										$upload_data = $form_field_obj->handle_submission_multifile_upload( $field_array, $upload_multiple_data );
                                    } else {
										$upload_data = $form_field_obj->handle_file_upload( $field_array );
									}
									if ( isset( $upload_data['success'] ) && $upload_data['success'] ) {
										$field_data['file'] = $upload_data;

                                        // If upload is successful, add the upload data to custom field if tag is present
                                        if ( ! empty( $upload_in_customfield ) ) {
                                            $file_url = $upload_data['file_url'];
                                            if ( 'multiple' === $file_type ) {
                                                $file_url = implode( ', ', $upload_data['file_url'] );
                                            }

                                            foreach ( $upload_in_customfield as $cf_key => $cf ) {
                                                if ( $field_id === $cf['upload_id'] ) {
                                                    $upload_in_customfield[$cf_key]['uploads'] = $file_url;
                                                }
                                            }
                                        }

									} elseif ( isset( $upload_data['success'] ) && false === $upload_data['success'] ) {
										$response = array(
											'message' => isset( $upload_data['message'] ) ? $upload_data['message'] : $this->get_invalid_form_message( $setting, $form_id ),
											'errors'  => array(),
											'success' => false,
											'behav'   => $submission_behav,
										);

										return $response;
									} else {
										// no file uploaded for this field_id
										$field_data = '';
									}
								}
								if ( "postdata" === $field_type ) {
									if ( $post_file ) {
										$post_image = $form_field_obj->upload_post_image( $field_array, $post_file );
										if ( is_array( $post_image ) && $post_image['attachment_id'] > 0 ) {
											$field_data['post-image'] = $post_image;
										} else {
											$field_data['post-image'] = '';
										}
									}

								}
								if ( 'url' === $field_type ) {
									$field_data = $form_field_obj->add_scheme_url( $field_data );
								}

								if ( 'select' === $field_type ) {
									$is_limit = Forminator_Field::get_property( 'limit_status', $field_array );
									if ( isset( $is_limit ) && 'enable' === $is_limit ) {
										$options      = Forminator_Field::get_property( 'options', $field_array );
										$value_type   = Forminator_Field::get_property( 'value_type', $field_array );
										$select_array = is_array( $submitted_data[ $field_id ] ) ? $submitted_data[ $field_id ] : array( $submitted_data[ $field_id ] );
										foreach ( $options as $o => $option ) {
											if ( in_array( $option['value'], $select_array ) ) {
												$select_field_value[ $field_id ][ $o ]['limit'] = $option['limit'];
												$select_field_value[ $field_id ][ $o ]['value'] = $option['value'];
												$select_field_value[ $field_id ][ $o ]['type']  = $value_type;
											}
										}
									}
								}

								/**
								 * Filter handle specific field types
								 *
								 * @since 1.13
								 *
								 * @param array $field_data Field data
								 * @param object $form_field_obj Form field object
								 * @param array $field_array field settings
								 * @param string $submission_behav submission behaviour
								 *
								 * @return array $field_data Set `return` element of the array as true for returning
								 */
								$field_data = apply_filters( 'forminator_handle_specific_field_types', $field_data, $form_field_obj, $field_array, $submission_behav );

								if ( ! empty( $field_data['return'] ) ) {
									unset( $field_data['return'] );

									return $field_data;
								}

								/**
								 * @since 1.0.5
								 * Load Autofill
								 */
								$form_field_obj->init_autofill( $setting );

								/**
								 * Sanitize data
								 *
								 * @since 1.0.2
								 *
								 * @param array $field
								 * @param array|string $data - the data to be sanitized
								 */
								$field_data = $form_field_obj->sanitize( $field_array, $field_data );

								// Validate data when its available and not hidden on front end
								if ( $form_field_obj->is_available( $field_array ) && ! $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form ) ) {

									/**
									 * @since 1.0.5
									 * Mayble re autofill, when autofill not editable, it should return autofill value
									 */
									$field_data = $form_field_obj->maybe_re_autofill( $field_array, $field_data, $setting );

									$form_field_obj->validate( $field_array, $field_data, $submitted_data );
								}
								$valid_response = $form_field_obj->is_valid_entry();

								if ( ! empty( $field_data ) || '0' === $field_data ) {
									if ( ! is_array( $valid_response ) ) {
										if ( "postdata" === $field_type && ! $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form ) ) {
                                            // Store postdata' data here to be used later. This allows support for upload tags inside custom fields.
                                            $postdata_fields[] = array(
                                                'key'            => $field_index,
                                                'field_id'       => $field_id,
                                                'field_data'     => $field_data,
                                                'field_array'    => $field_array,
                                                'form_field_obj' => $form_field_obj,
                                            );
										}

										if ( 'date' === $field_type && 'picker' !== $field->field_type ) {
											$date_format          = Forminator_Field::get_property( 'date_format', $field_array );
											$field_data['format'] = datepicker_default_format( $date_format );
										}

										if ( "product" === $field_type ) {
											$product_fields[] = array(
												'name'  => $field_id,
												'value' => $field_data,
											);
										}
										$field_data_array[] = array(
											'name'  => $field_id,
											'value' => $field_data,
										);
									} else {
										foreach ( $valid_response as $error_field => $error_response ) {
											$submit_errors[][ $error_field ] = $error_response;
										}
									}
								} else {
									if ( is_array( $valid_response ) && isset( $valid_response[ $field_id ] ) ) {
										$submit_errors[][ $field->slug ] = $valid_response[ $field_id ];
									}
								}
							}
						}
					}

					/**
					* Get saved postdata fields data and replace upload tags with uploaded data
					* move to function ++++
					*/
					if ( ! empty( $postdata_fields ) ) {
					   $postdata_return = $this->create_post_from_postdata( $postdata_fields, $upload_in_customfield );

					   foreach ( $postdata_return as $postdata ) {
					       if ( 'success' === $postdata['type'] ) {

					           foreach ( $field_data_array as $field_key => $field_datum ) {
					               if ( $field_datum['name'] === $postdata['field_id'] ) {
					                   $field_data_array[$field_key] = array(
					                       'name'  => $postdata['field_id'],
					                       'value' => $postdata['field_data'],
					                   );
					               }
					           }
					       } else {
					           $submit_errors[][ $field->slug ] = $postdata['value'];
					       }
					   }
					}

					$submitted_data = $this->replace_hidden_field_values( $custom_form, $submitted_data, $hidden_fields );

					/**
					 * @since 1.11
					 * For login or registration forms
					 */
					if ( isset( $setting['form-type'] ) && in_array( $setting['form-type'], array(
							'login',
							'registration'
						) ) ) {
						//Check who can register new users.
						if ( ! is_user_logged_in() ) {
							$can_creat_user = true;
						} elseif ( 'registration' === $setting['form-type']
						           && isset( $setting['hide-registration-form'] )
						           && '' === $setting['hide-registration-form']
						) {
							$can_creat_user = true;
						} else {
							$can_creat_user = false;
						}

						$user_response = array(
							'message' => '',
							'errors'  => array(),
							'success' => false,
							'behav'   => $submission_behav,
						);
						if ( $can_creat_user && 'login' === $setting['form-type'] ) {
							$forminator_user_login = new Forminator_CForm_Front_User_Login();
							$login_user            = $forminator_user_login->process_login( $custom_form, $submitted_data, $entry, $field_data_array );
							if ( is_wp_error( $login_user['user'] ) ) {
								$message = '';

								if ( forminator_get_property( $login_user['user']->errors, 'invalid_email' ) ) {
									$message = $login_user['user']->errors['invalid_email'][0];
								}

								if ( forminator_get_property( $login_user['user']->errors, 'invalid_username' ) ) {
									$message = $login_user['user']->errors['invalid_username'][0];
								}

								if ( forminator_get_property( $login_user['user']->errors, 'incorrect_password' ) ) {
									$message = $login_user['user']->errors['incorrect_password'][0];
								}

								$user_response['message'] = $message;

								return $user_response;
							}

							if ( ! empty( $login_user['authentication'] ) && 'invalid' === $login_user['authentication'] ) {
								$user_response['authentication'] = 'invalid';
								$user_response['message']        = __( 'Whoops, the passcode you entered was incorrect or expired.', Forminator::DOMAIN );

								return $user_response;
							}

							$field_data_array = $forminator_user_login->remove_password( $field_data_array );
						} elseif ( $can_creat_user ) {
							$forminator_user_registration = new Forminator_CForm_Front_User_Registration();
							$registration_error           = $forminator_user_registration->process_validation( $custom_form, $submitted_data, $field_data_array, $pseudo_submitted_data );
							if ( true !== $registration_error ) {
								$user_response['message'] = $registration_error;

								return $user_response;
							}

							$custom_error = apply_filters( 'forminator_custom_registration_form_errors', $registration_error, $form_id, $field_data_array );
							if ( true !== $custom_error ) {
								$user_response['message'] = $custom_error;

								return $user_response;
							}

							$registration_exists = true;
						}
					}
				}

				/**
				 * Filter submission errors
				 *
				 * @since 1.0.2
				 *
				 * @param array $submit_errors - the submission errors
				 * @param int $form_id - the form id
				 *
				 * @return array $submit_errors
				 */
				$submit_errors = apply_filters( 'forminator_custom_form_submit_errors', $submit_errors, $form_id, $field_data_array );

				if ( empty( $submit_errors ) ) {
					if ( isset( $setting['honeypot'] ) && filter_var( $setting['honeypot'], FILTER_VALIDATE_BOOLEAN ) ) {
						$total_fields = count( $fields ) + 1;
						if ( isset( $submitted_data["input_$total_fields"] ) && empty( $submitted_data["input_$total_fields"] ) ) {
							$can_submit = true;
						} else {
							$can_submit = false;
							//show success but dont save form
							$response = array(
								'message' => __( "Form entry saved", Forminator::DOMAIN ),
								'success' => true,
								'behav'   => $submission_behav,
							);
						}
					}
				}

				/**
				 * Handle spam protection
				 * Add-ons use this filter to check if content has spam data
				 *
				 * @since 1.0.2
				 *
				 * @param bool false - defauls to false
				 * @param array $field_data_array - the entry data
				 * @param int $form_id - the form id
				 * @param string $form_type - the form type. In this case defaults to 'custom_form'
				 *
				 * @return bool true|false
				 */
				$is_spam = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'custom_form' );

				$entry->is_spam = $is_spam;
				if ( $is_spam ) {
					$can_submit          = false;
					$response['message'] = __( 'Something went wrong.', Forminator::DOMAIN );
				}

				if ( $can_submit && ! $is_spam ) {
					if ( ! empty( $field_data_array ) && empty( $submit_errors ) ) {

						// If preview, skip integrations
						if ( ! $preview ) {
							//ADDON on_form_submit
							$addon_error = $this->attach_addons_on_form_submit( $form_id, $custom_form );

							if ( true !== $addon_error ) {
								$response = array(
									'message' => $addon_error,
									'success' => false,
									'errors'  => array(),
									'behav'   => $submission_behav,
								);

								return $response;
							}
						}

						if ( 'leads' === $form_type && isset( $submitted_data['lead_quiz'] ) ) {
							$quiz_model        = Forminator_Quiz_Form_Model::model()->load( $submitted_data['lead_quiz'] );
							$entry->entry_type = 'quizzes';
							$entry->form_id    = $submitted_data['lead_quiz'];
							if ( isset( $quiz_model->settings ) ) {
								$prevent_store = $custom_form->is_prevent_store( $submitted_data['lead_quiz'], $quiz_model->settings );
							}
						}

						if ( $prevent_store || $entry->save() ) {
							$response = array(
								'message' => __( "Form entry saved", Forminator::DOMAIN ),
								'success' => true,
								'behav'   => $submission_behav,
							);
							if ( isset( $submitted_data['product-shipping'] ) && intval( $submitted_data['product-shipping'] > 0 ) ) {
								$field_data_array[] = array(
									'name'  => 'product_shipping',
									'value' => $submitted_data['product-shipping'],
								);
							}
							$field_data_array[] = array(
								'name'  => '_forminator_user_ip',
								'value' => Forminator_Geo::get_user_ip(),
							);
							$field_data_array[] = array(
								'name'  => '_forminator_user_ip',
								'value' => Forminator_Geo::get_user_ip(),
							);
							// Calculation
							if ( $calculation_exists ) {
								$calculation_entry_data_array = $this->calculate_fields_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $hidden_fields );

								if ( ! empty( $calculation_entry_data_array ) ) {
									$field_data_array = array_merge( $field_data_array, $calculation_entry_data_array );
								}
							}

							// Stripe
							if ( $stripe_exists ) {
								$stripe_entry_data_array = $this->stripe_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );
								if ( ! empty( $stripe_entry_data_array ) ) {
									// only take first
									$stripe_entry_meta = isset( $stripe_entry_data_array[0] ) ? $stripe_entry_data_array[0] : array();
									if ( ! empty( $stripe_entry_meta ) ) {
										$stripe_meta_value = $stripe_entry_meta['value'];
										forminator_maybe_log( __METHOD__, $stripe_meta_value );

										$field_data_array = array_merge( $field_data_array, $stripe_entry_data_array );
									}
								}

								$fields_collection = forminator_fields_to_array();

								foreach ( $fields as $field ) {
									$field = $field->to_formatted_array();

									if ( isset( $field['type'] ) && 'stripe' === $field['type'] ) {

										if ( isset( $fields_collection['stripe'] ) ) {
											$element_id = isset( $field['element_id'] ) ? $field['element_id'] : false;
											$mode 		= isset( $field['mode'] ) ? $field['mode'] : 'sandbox';

											$i = 0;
											foreach( $field_data_array as $data ) {
												if ( $data['name'] === $element_id ) {

													$field_object = $fields_collection['stripe'];

													// Try to get Payment Intent from submitted date
													try {
														$intent = $field_object->get_paymentIntent( $field, $submitted_data );

														// Make sure PaymentIntent is valid
														if ( is_wp_error( $intent ) ) {
															throw new Exception( __('Invalid Stripe Payment Intent.', Forminator::DOMAIN ) );
														}

														// Confirm payment intent
														$result = $intent->confirm();
													} catch ( Exception $e ) {
														// Delete entry if paymentIntent confirmation is not successful
														$entry->delete();

														$response = array(
															'message' => $e->getMessage(),
															'success' => false,
															'errors'  => array(),
															'behav'   => $submission_behav,
														);

														wp_send_json_error( $response );
													}

													// If we have 3D security on the card return for verification
													if ( 'requires_action' === $result->status ) {
														// Delete entry if 3d security is needed, we will store it on next attempt
														$entry->delete();

														$response = array(
															'message'  => __( 'This payment require 3D Secure authentication! Please follow the instructions.', Forminator::DOMAIN ),
															'success'  => false,
															'errors'   => array(),
															'behav'    => $submission_behav,
															'stripe3d' => true,
															'secret'	  => $result->client_secret
														);

														wp_send_json_error( $response );
													}

													// Try to capture payment
													try {
														$capture = $intent->capture();
													} catch ( Exception $e ) {
														// Delete entry if capture is not successful
														$entry->delete();

														$response = array(
															'message' => $e->getMessage(),
															'success' => false,
															'errors'  => array(),
															'behav'   => $submission_behav,
														);

														wp_send_json_error( $response );
													}

													if ( isset( $capture->charges->data[0]->captured ) && $capture->charges->data[0]->captured === true ) {
														$field_data_array[ $i ]['value']['status'] = "COMPLETED";

														$field_data_array[ $i ]['value']['transaction_id'] = $intent->id;

														$transaction_link = 'https://dashboard.stripe.com/payments/' . rawurlencode( $intent->id );
														if ( 'test' === $mode ) {
															$transaction_link = 'https://dashboard.stripe.com/test/payments/' . rawurlencode( $intent->id );
														}

														$field_data_array[ $i ]['value']['transaction_link'] = $transaction_link;

													} else {
														// Delete entry if capture is not successful
														$entry->delete();

														// Return error
														$response = array(
															'message' => __( 'Payment failed, please try again!', Forminator::DOMAIN ),
															'success' => false,
															'errors'  => array(),
															'behav'   => $submission_behav,
														);

														wp_send_json_error( $response );
													}
												}

												$i++;
											}
										}
									}
								}
							}

							// PayPal
							if ( $paypal_exists ) {
								// Update entry with new
								$entry->set_fields( $field_data_array );

								if ( $custom_form->is_payment_require_ssl() && ! is_ssl() ) {
									$response = array(
										'message' => apply_filters(
											'forminator_payment_require_ssl_error_message',
											__( 'SSL required to submit this form, please check your URL.', Forminator::DOMAIN )
										),
										'errors'  => array(),
										'success' => false,
									);

									return $response;
								}

								$paypal_entry_data_array = $this->paypal_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );
								if ( ! empty( $paypal_entry_data_array ) ) {
									// only take first
									$paypal_entry_meta = isset( $paypal_entry_data_array[0] ) ? $paypal_entry_data_array[0] : array();
									if ( ! empty( $paypal_entry_meta ) ) {
										$paypal_meta_value = $paypal_entry_meta['value'];
										forminator_maybe_log( __METHOD__, $paypal_meta_value );
										// Error
										if ( ! isset( $paypal_meta_value['status'] ) || 'APPROVED' !== $paypal_meta_value['status'] ) {
											$response = array(
												'message' => $paypal_meta_value['error'],
												'errors'  => array(),
												'success' => false,
											);

											return $response;
										}
										$field_data_array = array_merge( $field_data_array, $paypal_entry_data_array );
									}

								}

								$fields_collection = forminator_fields_to_array();

								foreach ( $fields as $field ) {
									$field = $field->to_formatted_array();

									if ( isset( $field['type'] ) && 'paypal' === $field['type'] ) {
										if ( isset( $fields_collection['paypal'] ) ) {
											$element_id = isset( $field['element_id'] ) ? $field['element_id'] : false;
											$mode 		= isset( $field['mode'] ) ? $field['mode'] : 'sandbox';

											$i = 0;
											foreach( $field_data_array as $data ) {
												if ( $data['name'] === $element_id ) {
													$paypal = new Forminator_PayPal_Express();
													$capture = $paypal->capture_order( $submitted_data[ $element_id ], $mode );

													if ( isset( $capture->status ) && "COMPLETED" === $capture->status ) {
														$field_data_array[ $i ]['value']['status'] = "COMPLETED";

														if ( isset( $capture->purchase_units[0]->payments->captures[0]->id ) ) {
															$transaction_id = $capture->purchase_units[0]->payments->captures[0]->id;

															$field_data_array[ $i ]['value']['transaction_id'] = $transaction_id;
															$transaction_link = 'https://www.paypal.com/activity/payment/' . rawurlencode( $transaction_id );

															if ( 'sandbox' === $mode ) {
																$transaction_link = 'https://www.sandbox.paypal.com/activity/payment/' . rawurlencode( $transaction_id );
															}

															$field_data_array[ $i ]['value']['transaction_link'] = $transaction_link;
														}
													} else {
														// Delete entry if capture is not successful
														$entry->delete();

														// Return error
														$response = array(
															'message' => __( 'Payment failed, please try again!', Forminator::DOMAIN ),
															'success' => false,
															'errors'  => array(),
															'behav'   => $submission_behav,
														);

														return $response;
													}
												}

												$i++;
											}
										}
									}
								}

								// Update entry with new
								$entry->set_fields( $field_data_array );
							}

							/**
							 * Filter saved data before persisted into the database
							 *
							 * @since 1.0.2
							 *
							 * @param array $field_data_array - the entry data
							 * @param int $form_id - the form id
							 *
							 * @return array $field_data_array
							 */
							$field_data_array = apply_filters( 'forminator_custom_form_submit_field_data', $field_data_array, $form_id );

							/**
							 * Action called before setting fields to database
							 *
							 * @since 1.0.2
							 *
							 * @param Forminator_Form_Entry_Model $entry - the entry model
							 * @param int $form_id - the form id
							 * @param array $field_data_array - the entry data
							 *
							 */
							do_action( 'forminator_custom_form_submit_before_set_fields', $entry, $form_id, $field_data_array );

							// ADDON add_entry_fields
							// @since 1.2 Add field_data_array to param
							$added_data_array = $this->attach_addons_add_entry_fields( $form_id, $custom_form, $field_data_array );
							$added_data_array = array_merge( $field_data_array, $added_data_array );
							$added_data_array = self::replace_values_to_labels( $added_data_array, $submitted_data, $custom_form, $entry );

							if ( 'leads' === $form_type ) {
								$response['entry_id'] = $entry->entry_id;

								$added_data_array[] = array(
									'name' => 'skip_form',
									'value' => '0'
								);
							}

							$entry->set_fields( $added_data_array );

							//ADDON after_entry_saved
							$this->attach_addons_after_entry_saved( $form_id, $entry );

							//After $entry->set_fields() to get all data for {all_fields}

							if ( 'leads' !== $form_type ) {
								if ( $registration_exists ) {
									$new_user_data = $forminator_user_registration->process_registration( $custom_form, $submitted_data, $entry );

									if ( ! is_array( $new_user_data ) ) {
										$user_response['message'] = $new_user_data;

										return $user_response;
									}

									$field_data_array = $forminator_user_registration->remove_password( $field_data_array );
									//Do not send emails later
									$custom_form = $forminator_user_registration->change_custom_form( $custom_form );
								}

								if ( ! $entry->is_spam ) {
									$forminator_mail_sender = new Forminator_CForm_Front_Mail();
									$forminator_mail_sender->process_mail( $custom_form, $submitted_data, $entry, $pseudo_submitted_data );
								}
							} else {
								$response['entry_id'] = $entry->entry_id;
							}

							$all_behaviours = array( 'behaviour-thankyou', 'behaviour-hide', 'behaviour-redirect' );
							if ( isset( $setting['submission-behaviour'] ) && in_array( $setting['submission-behaviour'], $all_behaviours, true ) ) {
								$exist_thankyou_message = false;
								if ( isset( $setting['thankyou-message'] ) && ! empty( $setting['thankyou-message'] ) ) {
									/**
									 * Filter thankyou message
									 *
									 * @since 1.11
									 *
									 * @param string $setting ['thankyou-message']
									 * @param array $submitted_data
									 * @param Forminator_Custom_Form_Model $custom_form
									 *
									 * @return string
									 */
									$setting['thankyou-message'] = apply_filters( 'forminator_custom_form_thankyou_message', $setting['thankyou-message'], $submitted_data, $custom_form );
									//replace form data vars with value
									$thankyou_message = forminator_replace_form_data( $setting['thankyou-message'], $submitted_data, $custom_form, $entry, true );
									//replace misc data vars with value
									$thankyou_message       = forminator_replace_variables( $thankyou_message, $form_id );
									$response['message']    = $thankyou_message;
									$exist_thankyou_message = true;
								}

								if ( 'behaviour-redirect' === $setting['submission-behaviour'] && isset( $setting['redirect-url'] ) && ! empty( $setting['redirect-url'] ) ) {
									$response['redirect'] = true;
									//replace form data vars with value
									$redirect_url = forminator_replace_form_data( $setting['redirect-url'], $submitted_data, $custom_form, $entry );
									$tab_value    = isset( $setting['newtab'] ) ? $setting['newtab'] : 'sametab';
									$newtab       = forminator_replace_form_data( $tab_value, $submitted_data, $custom_form, $entry );
									//replace misc data vars with value
									$redirect_url       = forminator_replace_variables( $redirect_url, $form_id );
									$newtab             = forminator_replace_variables( $newtab, $form_id );
									$response['url']    = esc_url_raw( $redirect_url );
									$response['newtab'] = esc_html( $newtab );
									//Empty message if behaviour is redirect
									if ( ! $exist_thankyou_message ) {
										$response['message'] = '';
									}
								}
							}

							if ( isset( $login_user['user']->ID ) ) {
								$response['user_id'] = $login_user['user']->ID;
							}
							if ( isset( $login_user['authentication'] ) ) {
								$response['authentication'] = $login_user['authentication'];
							}
							if ( isset( $login_user['lost_url'] ) ) {
								$response['lost_url'] = $login_user['lost_url'];
							}

							if ( ! isset( $setting['enable-ajax'] ) || empty( $setting['enable-ajax'] ) ) {
								$is_ajax_enabled = false;
							} else {
								$is_ajax_enabled = filter_var( $setting['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
							}

							// Force AJAX submit if form contains Stripe payment field
							if ( $custom_form->has_stripe_field() ) {
								$is_ajax_enabled = true;
							}

							if ( $is_ajax_enabled && ! empty( $select_field_value ) ) {
								$result = array();
								foreach ( $select_field_value as $select_name => $select_field ) {
									$select_value = array();
									foreach ( $select_field as $s => $select ) {
										$entries = Forminator_Form_Entry_Model::select_count_entries_by_meta_field( $form_id, $select_name, $select['value'], $select['type'] );
										if ( ! empty( $select['limit'] ) && $select['limit'] <= $entries ) {
											$select_value[ $s ]['value'] = $select['value'];
											$select_value[ $s ]['type']  = $select['type'];
										}
									}
									if ( ! empty( $select_value ) ) {
										$result[ $select_name ] = array_values( $select_value );
									}
								}
								$response['select_field'] = $result;
							}

							if ( ! empty( $product_fields ) ) {
								//Process purchase

								$page_id  = $submitted_data['page_id']; //use page id to get permalink for redirect
								$entry_id = $entry->entry_id;
								$shipping = 0;

								if ( isset( $submitted_data['product-shipping'] ) ) {
									$shipping = $submitted_data['product-shipping'];
								}

								/**
								 * Process purchase
								 *
								 * @since 1.0.0
								 *
								 * @param array $response - the response array
								 * @param array $product_fields - the product fields
								 * @param int $entry_id - the entry id ( reference for callback)
								 * @param int $page_id - the page id. Used to generate a return url
								 * @param int $shipping - the shipping cost
								 */
								$response = apply_filters( 'forminator_cform_process_purchase', $response, $product_fields, $field_data_array, $entry_id, $page_id, $shipping );
							}
						}
					}
					if ( ! empty( $submit_errors ) ) {
						$response = array(
							'message' => $this->get_invalid_form_message( $setting, $form_id ),
							'success' => false,
							'errors'  => $submit_errors,
							'behav'   => $submission_behav,
						);
					}
				}
			} else {
				$response = array(
					'message' => $submit_error,
					'success' => false,
					'errors'  => array(),
					'behav'   => $submission_behav,
				);
			}

			return $response;
		}

		return false;
	}

	/**
	 * Replace values to labels for radios, selectboxes and checkboxes
	 *
	 * @param type $data
	 * @param type $submitted_data
	 * @param type $custom_form
	 * @param type $entry
	 * @return type
	 */
	private static function replace_values_to_labels( $data, $submitted_data, $custom_form, $entry ) {
		foreach ( $data as $key => $value ) {
			if ( empty( $value['name'] ) ) {
				continue;
			}
			$slug = $value['name'];
			if ( strpos( $slug, 'radio' ) !== false
					|| strpos( $slug, 'select' ) !== false
					|| strpos( $slug, 'checkbox' ) !== false
					) {
				$data[ $key ]['value'] = forminator_replace_form_data( '{' . $slug . '}', $submitted_data, $custom_form, $entry, true );
			}
		}

		return $data;
	}

	/**
	 * Multiple File upload
	 */
	public function multiple_file_upload() {

		$response  = array();
		$post_data = $this->get_post_data();

		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'forminator_submit_form' ) ) {
			wp_send_json_error( new WP_Error( 'invalid_code' ) );
		}
		$form_id = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false; // WPCS: CSRF OK

		if ( $form_id ) {
			$custom_form = Forminator_Custom_Form_Model::model()->load( $form_id );
			if ( is_object( $custom_form ) ) {
				$fields      = $custom_form->get_fields();
				$field_forms = forminator_fields_to_array();
				foreach ( $fields as $field ) {
					$field_array = $field->to_formatted_array();
					$element_id  = isset( $field_array['element_id'] ) ? $field_array['element_id'] : '';
					$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';
					if ( isset( $post_data['element_id'] ) && 'upload' === $field_type && $post_data['element_id'] === $element_id ) {
					    $upload_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;
						$response         = $upload_field_obj->handle_file_upload( $field_array, $post_data, 'upload' );

						if ( ! $response['success'] || isset( $response['errors'] ) ) {
							wp_send_json_error( $response );
						} else {
							wp_send_json_success( $response );
						}
					}
				}
			}
		} else {
			$response = array(
				'success' => false,
				'message' => __( 'form not found', Forminator::DOMAIN ),
			);
		}

		wp_send_json_error( $response );
	}

	/**
	 * Response message
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 * @since 1.5.1 utilize `_post_data` which already defined on submit
	 *
	 * @param $form_id
	 * @param $render_id
	 */
	public function form_response_message( $form_id, $render_id ) {
		$submitted_data = $this->_post_data;

		$post_form_id   = isset( $submitted_data['form_id'] ) ? sanitize_text_field( $submitted_data['form_id'] ) : 0;
		$post_render_id = isset( $submitted_data['render_id'] ) ? sanitize_text_field( $submitted_data['render_id'] ) : 0;
		$response       = self::$response;

		//only show to related form
		if ( ! empty( $response ) && is_array( $response ) && (int) $form_id === (int) $post_form_id && (int) $render_id === (int) $post_render_id ) {
			$label_class = $response['success'] ? 'forminator-success' : 'forminator-error';
			?>
            <div class="forminator-response-message forminator-show <?php echo esc_attr( $label_class ); ?>"
                 tabindex="-1">
                <label class="forminator-label--<?php echo esc_attr( $label_class ); ?>"><?php echo $response['message']; // WPCS: XSS ok. ?></label>
				<?php
				if ( isset( $response['errors'] ) && ! empty( $response['errors'] ) ) {
					?>
                    <ul class="forminator-screen-reader-only">
						<?php
						foreach ( $response['errors'] as $key => $error ) {
							foreach ( $error as $id => $value ) {
								?>
                                <li><?php echo esc_html( $value ); ?></li>
								<?php
							}
						}
						?>
                    </ul>
					<?php
				}
				?>
            </div>
			<?php

			if ( isset( $response['success'] ) && $response['success'] && isset( $response['behav'] ) && ( 'behaviour-hide' === $response['behav'] || ( isset( $response['newtab'] ) && 'newtab_hide' === $response['newtab'] ) ) ) {
				$selector = '#forminator-module-' . $form_id . '[data-forminator-render="' . $render_id . '"]';
				?>
                <script type="text/javascript">var ForminatorFormHider =
					<?php
					echo wp_json_encode(
						array(
							'selector' => $selector,
						)
					);
					?>
                </script>
				<?php
			}
			if ( isset( $response['success'] ) && $response['success'] && isset( $response['behav'] ) && 'behaviour-redirect' === $response['behav'] && isset( $response['newtab'] ) && ( 'newtab_hide' === $response['newtab'] || 'newtab_thankyou' === $response['newtab'] ) ) {
				$url = $response['url'];
				?>
                <script type="text/javascript">var ForminatorFormNewTabRedirect =
					<?php
					echo wp_json_encode(
						array(
							'url' => $url,
						)
					);
					?>
                </script>
				<?php
			}

		}
	}

	/**
	 * @since 1.0
	 *
	 * @param array $setting - the form settings
	 * @param int $form_id - the form id
	 *
	 * @return mixed
	 */
	public function get_invalid_form_message( $setting, $form_id ) {
		$invalid_form_message = __( "Error: Your form is not valid, please fix the errors!", Forminator::DOMAIN );
		if ( isset( $setting['submitData']['custom-invalid-form-message'] ) && ! empty( $setting['submitData']['custom-invalid-form-message'] ) ) {
			$invalid_form_message = $setting['submitData']['custom-invalid-form-message'];
		}

		return apply_filters( 'forminator_custom_form_invalid_form_message', $invalid_form_message, $form_id );
	}


	/**
	 * Add Error message on footer script if available
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 * @since 1.5.1 utilize `_post_data` which already defined on submit
	 */
	public function footer_message() {
		$submitted_data = $this->_post_data;

		$response  = self::$response;
		$form_id   = isset( $submitted_data['form_id'] ) ? sanitize_text_field( $submitted_data['form_id'] ) : false;
		$render_id = isset( $submitted_data['render_id'] ) ? sanitize_text_field( $submitted_data['render_id'] ) : '';
		$selector  = '#forminator-module-' . $form_id . '[data-forminator-render="' . $render_id . '"]';
		if ( ! empty( $response ) && is_array( $response ) ) {
			?>
            <script type="text/javascript">var ForminatorValidationErrors =
				<?php
				echo wp_json_encode(
					array(
						'selector' => $selector,
						'errors'   => $response['errors'],
					)
				);
				?>
            </script>
			<?php
		}

	}

	/**
	 * Executor On form submit for attached addons
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::on_form_submit()
	 * @since 1.1
	 *
	 * @param                              $form_id
	 *
	 * @param Forminator_Custom_Form_Model $custom_form_model
	 *
	 * @return bool true on success|string error message from addon otherwise
	 */
	private function attach_addons_on_form_submit( $form_id, Forminator_Custom_Form_Model $custom_form_model ) {
		$allowed_form_fields = forminator_addon_format_form_fields( $custom_form_model );
		$submitted_data      = forminator_format_submitted_data_for_addon( $_POST, $_FILES, $allowed_form_fields );// WPCS: CSRF ok. its already validated before.
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_form( $form_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $form_id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$addon_return = $form_hooks->on_form_submit( $submitted_data );
					if ( true !== $addon_return ) {
						return $form_hooks->get_submit_form_error_message();
					}
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to attach_addons_on_form_submit', $e->getMessage() );
			}

		}

		return true;
	}

	/**
	 * Executor to add more entry fields for attached addons
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::add_entry_fields()
	 *
	 * @since 1.1
	 * @since 1.2 Add $current_entry_fields for Addon
	 *
	 * @param                              $form_id
	 * @param Forminator_Custom_Form_Model $custom_form_model
	 * @param array $current_entry_fields
	 *
	 * @return array added fields to entry
	 */
	private function attach_addons_add_entry_fields( $form_id, Forminator_Custom_Form_Model $custom_form_model, $current_entry_fields ) {
		$additional_fields_data = array();
		$allowed_form_fields    = forminator_addon_format_form_fields( $custom_form_model );
		$submitted_data         = forminator_format_submitted_data_for_addon( $_POST, $_FILES, $allowed_form_fields, $current_entry_fields );// WPCS: CSRF ok. its already validated before.
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_form( $form_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $form_id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$addon_fields = $form_hooks->add_entry_fields( $submitted_data, $current_entry_fields );
					//reformat additional fields
					$addon_fields           = self::format_addon_additional_fields( $connected_addon, $addon_fields );
					$additional_fields_data = array_merge( $additional_fields_data, $addon_fields );
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to add_entry_fields', $e->getMessage() );
			}

		}

		return $additional_fields_data;
	}

	/**
	 * Executor action for attached addons after entry saved on storage
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::after_entry_saved()
	 *
	 * @since 1.1
	 *
	 * @param                             $form_id
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	private function attach_addons_after_entry_saved( $form_id, Forminator_Form_Entry_Model $entry_model ) {
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_form( $form_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $form_id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$form_hooks->after_entry_saved( $entry_model );// run and forget
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to attach_addons_on_form_submit', $e->getMessage() );
			}

		}
	}

	/**
	 * Return Form Settings
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Custom_Form_Model $form
	 *
	 * @return mixed
	 */
	private function get_form_settings( $form ) {
		// If not using the new "submission-behaviour" setting, set it according to the previous settings
		if ( ! isset( $form->settings['submission-behaviour'] ) ) {
			$redirect = ( isset( $form->settings['redirect'] ) && filter_var( $form->settings['redirect'], FILTER_VALIDATE_BOOLEAN ) );
			$thankyou = ( isset( $form->settings['thankyou'] ) && filter_var( $form->settings['thankyou'], FILTER_VALIDATE_BOOLEAN ) );

			if ( ! $redirect && ! $thankyou ) {
				$form->settings['submission-behaviour'] = 'behaviour-thankyou';
			} elseif ( $thankyou ) {
				$form->settings['submission-behaviour'] = 'behaviour-thankyou';
			} elseif ( $redirect ) {
				$form->settings['submission-behaviour'] = 'behaviour-redirect';
			}
		}

		return $form->settings;
	}

	/**
	 * Calculate fields and convert it to entry data array to be saved or processed later
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param array $pseudo_submitted_data
	 *
	 * @return array
	 */
	public function calculate_fields_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $hidden_fields = array() ) {
		$entry_data_array  = array();
		$fields_collection = forminator_fields_to_array();
		$fields            = $custom_form->get_fields();

		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();

			if ( isset( $field['type'] ) && 'calculation' === $field['type'] ) {
				if ( ! isset( $fields_collection['calculation'] ) ) {
					continue;
				}

				/**
				 * Fires before calculate each `calculation` field
				 *
				 * Note : one form can have multiple calculation fields,
				 * in this case this action fired multiple times too
				 *
				 * @since 1.7
				 *
				 * @param Forminator_Custom_Form_Model $custom_form
				 * @param array $field field properties
				 */
				do_action( 'forminator_custom_form_before_calculate_field', $custom_form, $field );

				/** @var Forminator_Calculation $field_object */
				$field_object = $fields_collection['calculation'];
				if ( ! $field_object instanceof Forminator_Calculation ) {
					continue;
				}

				// skip on hidden
				if ( $field_object->is_hidden( $field, $submitted_data, $pseudo_submitted_data, $custom_form, $hidden_fields ) ) {
					continue;
				}

				// RECALCULATE, to retrieve error message if available
				$formula           = $field_object->get_calculable_value( $submitted_data, $field );
				$converted_formula = $field_object->get_converted_formula( $submitted_data, $field, $custom_form, $hidden_fields );
				$calculation_error = '';
				$result            = 0.0;

				try {
					$result = $field_object->get_calculated_value( $converted_formula, $submitted_data, $field );
				} catch ( Forminator_Calculator_Exception $e ) {
					$calculation_error = $e->getMessage();
				}

				$calculation_entry_data = array(
					'name'  => $field['element_id'],
					'value' => array(
						'formula'           => $formula,
						'converted_formula' => $converted_formula,
						'error'             => $calculation_error,
						'result'            => $result,
					),
				);

				/**
				 * Filter calculation entry data that might be stored/used later
				 *
				 * @since 1.7
				 *
				 * @param array $calculation_entry_data
				 * @param Forminator_Custom_Form_Model $custom_form
				 * @param array $field field_properties
				 * @param string $formula original formula
				 * @param string $converted_formula real formula that already replaced with field values
				 *
				 * @return array
				 */
				$calculation_entry_data = apply_filters( 'forminator_custom_form_calculation_entry_data', $calculation_entry_data, $custom_form, $field, $formula, $converted_formula );

				$entry_data_array [] = $calculation_entry_data;

				/**
				 * Fires after calculate each `calculation` field
				 *
				 * Note : one form can have multiple calculation fields,
				 * in this case this action fired multiple times too
				 *
				 * @since 1.7
				 *
				 * @param Forminator_Custom_Form_Model $custom_form
				 * @param array $field field properties
				 */
				do_action( 'forminator_custom_form_after_calculate_field', $custom_form, $field );

			}
		}

		return $entry_data_array;
	}


	/**
	 * Process stripe charge
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param array $pseudo_submitted_data
	 * @param array $field_data_array
	 *
	 * @return array
	 */
	public function stripe_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array ) {
		$entry_data_array  = array();
		$fields_collection = forminator_fields_to_array();
		$fields            = $custom_form->get_fields();

		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();

			if ( isset( $field['type'] ) && 'stripe' === $field['type'] ) {
				if ( isset( $fields_collection['stripe'] ) ) {

					/**
					 * Fires before process stripe
					 *
					 * @since 1.7
					 *
					 * @param Forminator_Custom_Form_Model $custom_form
					 * @param array $field field properties
					 * @param array $submitted_data
					 * @param array $field_data_array
					 */
					do_action( 'forminator_custom_form_before_stripe_charge', $custom_form, $field, $submitted_data, $field_data_array );

					/** @var Forminator_Stripe $field_object */
					$field_object = $fields_collection['stripe'];

					$entry_data = $field_object->process_to_entry_data( $field, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );

					$stripe_entry_data = array(
						'name'  => $field['element_id'],
						'value' => $entry_data,
					);

					/**
					 * Filter stripe entry data that might be stored/used later
					 *
					 * @since 1.7
					 *
					 * @param array $calculation_entry_data
					 * @param Forminator_Custom_Form_Model $custom_form
					 * @param array $field field_properties
					 * @param array $field_data_array
					 *
					 * @return array
					 */
					$stripe_entry_data = apply_filters( 'forminator_custom_form_stripe_entry_data', $stripe_entry_data, $custom_form, $field, $field_data_array );

					$entry_data_array [] = $stripe_entry_data;

					/**
					 * Fires after charge stripe
					 *
					 * @since 1.7
					 *
					 * @param Forminator_Custom_Form_Model $custom_form
					 * @param array $field field properties
					 * @param array $stripe_entry_data
					 * @param array $submitted_data
					 * @param array $field_data_array
					 */
					do_action( 'forminator_custom_form_after_stripe_charge', $custom_form, $field, $stripe_entry_data, $submitted_data, $field_data_array );

					// only process first
					break;
				}
			}
		}

		return $entry_data_array;
	}

	/**
	 * Process PayPal charge
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param array $pseudo_submitted_data
	 * @param array $field_data_array
	 *
	 * @return array
	 */
	public function paypal_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array ) {
		$entry_data_array  = array();
		$fields_collection = forminator_fields_to_array();
		$fields            = $custom_form->get_fields();

		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();

			if ( isset( $field['type'] ) && 'paypal' === $field['type'] ) {
				if ( isset( $fields_collection['paypal'] ) ) {

					/**
					 * Fires before process paypal
					 *
					 * @since 1.7
					 *
					 * @param Forminator_Custom_Form_Model $custom_form
					 * @param array $field field properties
					 * @param array $submitted_data
					 * @param array $field_data_array
					 */
					do_action( 'forminator_custom_form_before_paypal_charge', $custom_form, $field, $submitted_data, $field_data_array );

					/** @var Forminator_PayPal $field_object */
					$field_object = $fields_collection['paypal'];


					$entry_data        = $field_object->process_to_entry_data( $field, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );
					$paypal_entry_data = array(
						'name'  => $field['element_id'],
						'value' => $entry_data,
					);

					/**
					 * Filter paypal entry data that might be stored/used later
					 *
					 * @since 1.7
					 *
					 * @param array $calculation_entry_data
					 * @param Forminator_Custom_Form_Model $custom_form
					 * @param array $field field_properties
					 * @param array $field_data_array
					 *
					 * @return array
					 */
					$paypal_entry_data = apply_filters( 'forminator_custom_form_paypal_entry_data', $paypal_entry_data, $custom_form, $field, $field_data_array );

					$entry_data_array [] = $paypal_entry_data;

					/**
					 * Fires after charge paypal
					 *
					 * @since 1.7
					 *
					 * @param Forminator_Custom_Form_Model $custom_form
					 * @param array $field field properties
					 * @param array $paypal_entry_data
					 * @param array $submitted_data
					 * @param array $field_data_array
					 */
					do_action( 'forminator_custom_form_after_paypal_charge', $custom_form, $field, $paypal_entry_data, $submitted_data, $field_data_array );

					// only process first
					break;
				}
			}
		}

		return $entry_data_array;
	}

	/**
	 * Process PayPal charge
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param array $pseudo_submitted_data
	 * @param array $field_data_array
	 *
	 * @return array
	 */
	public function paypal_capture_payment( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry ) {

		return $field_data_array;
	}

	/**
	 * Build Pseudo Submit Data
	 * Pseudo Submit Data is used to later process on submit
	 * Its needed for fields that virtually not available on the user submitted data
	 * - Calculation : its not available on $_POST even its displayed on the form, because the value is re-calculated on backend
	 * - Stripe : Stripe field is not visually available on the form, the `amount` or value will be gathered on backend
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data = $_POST
	 *
	 * @return array
	 */
	public function build_pseudo_submitted_data( $custom_form, $submitted_data, $hidden_fields = array() ) {
		$pseudo_submitted_data = array();
		/** @var Forminator_Field[] $field_classes */
		$field_classes = forminator_fields_to_array();
		$fields        = $custom_form->get_fields();

		if ( $custom_form->has_calculation_field() ) {
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_id    = Forminator_Field::get_property( 'element_id', $field_array );
				$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

				if ( 'calculation' === $field_type ) {

					$forminator_calculation_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;
					if ( $forminator_calculation_field instanceof Forminator_Calculation ) {
						try {
							$converted_formula = $forminator_calculation_field->get_converted_formula( $submitted_data, $field_array, $custom_form, $hidden_fields );
							$result            = $forminator_calculation_field->get_calculated_value( $converted_formula, $submitted_data, $field_array );
						} catch ( Forminator_Calculator_Exception $e ) {
							$result = 0.0;
						}
						$pseudo_submitted_data[ $field_id ] = $result;
					}

				}
			}
		}

		// Stripe / payments go last, because it's amount can be dependant on other pseudo submitted data (calc
		if ( class_exists( 'Forminator_Stripe' ) && $custom_form->has_stripe_field() ) {
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_id    = Forminator_Field::get_property( 'element_id', $field_array );
				$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

				if ( 'stripe' === $field_type ) {

					$forminator_stripe_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;

					if ( $forminator_stripe_field instanceof Forminator_Stripe ) {
						$pseudo_submitted_data[ $field_id ] = $forminator_stripe_field->get_payment_amount( $field_array, $custom_form, $submitted_data, $pseudo_submitted_data );
					}

					// only process first single stripe
					break;

				}
			}
		}
		// PayPal / payments go last, because it's amount can be dependant on other pseudo submitted data (calc
		if ( class_exists( 'Forminator_PayPal' ) && $custom_form->has_paypal_field() ) {
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_id    = Forminator_Field::get_property( 'element_id', $field_array );
				$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

				if ( 'paypal' === $field_type ) {

					$forminator_paypal_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;

					if ( $forminator_paypal_field instanceof Forminator_PayPal ) {
						$pseudo_submitted_data[ $field_id ] = $forminator_paypal_field->get_payment_amount( $field_array, $custom_form, $submitted_data, $pseudo_submitted_data );
					}

					// only process first single paypal
					break;

				}
			}
		}

		/**
		 * Filter Pseudo submitted data on Custom Form
		 *
		 * @since 1.7
		 *
		 * @param array $pseudo_submitted_data
		 * @param Forminator_Custom_Form_Model $custom_form
		 * @param array $submitted_data
		 *
		 * @return array
		 */
		$pseudo_submitted_data = apply_filters( 'forminator_custom_form_pseudo_submitted_data', $pseudo_submitted_data, $custom_form, $submitted_data );

		return $pseudo_submitted_data;
	}

	/**
	 * Replace field values hidden by conditions
	 *
	 * @param $custom_form
	 * @param $submitted_data
	 *
	 * @return mixed
	 */
	private function replace_hidden_field_values( $custom_form, $submitted_data, $hidden_fields = array() ) {
		$field_forms = forminator_fields_to_array();

		// build pseudo submit data first to later usage
		$pseudo_submitted_data = $this->build_pseudo_submitted_data( $custom_form, $submitted_data, $hidden_fields );

		foreach ( $custom_form->get_fields() as $field ) {
			$field_array    = $field->to_formatted_array();
			$field_id       = Forminator_Field::get_property( 'element_id', $field_array );
			$field_type     = isset( $field_array['type'] ) ? $field_array['type'] : '';
			$form_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;

			if ( $form_field_obj && $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form, $hidden_fields ) ) {
				$replace        = 0;
				$quoted_operand = preg_quote( '{' . $field_id . '}', '/' );
				$pattern        = '/([\\+\\-\\*\\/]?)[^\\+\\-\\*\\/\\(]*' . $quoted_operand
				                  . '[^\\)\\+\\-\\*\\/]*([\\+\\-\\*\\/]?)/';

				foreach ( $custom_form->get_fields() as $calc_field ) {
					$calc_field_array = $calc_field->to_array();
					if ( 'calculation' !== $calc_field_array['type'] ) {
						continue;
					}
					$formula = $calc_field_array['formula'];

					$matches = [];
					if ( preg_match( $pattern, $formula, $matches ) ) {
						// if operand in multiplication or division set value = 1
						if ( '*' === $matches[1] || '/' === $matches[1] || '*' === $matches[2] || '/' === $matches[2] ) {
							$replace = 1;
						}
					}
				}

				$submitted_data[ $field_id ] = $replace;
			}
		}

		return $submitted_data;
	}

	/**
	 * Get Default date format
	 *
	 * @param $format
	 *
	 * @return string
	 */
	public function datepicker_default_format( $format ) {
		switch ( $format ) {
			case 'mm/dd/yy':
				$format = 'm/d/Y';
				break;
			case 'yy-mm-dd':
				$format = 'Y-m-d';
				break;
			case 'dd/mm/yy':
				$format = 'd/m/Y';
				break;
			default:
				$format = get_option( 'date_format' );
				break;
		}

		return $format;
	}

	/**
	 * Create new post from postdata field
     * Add upload file urls to postdata custom fields if necessary
	 *
	 * @param $postdata_fields       array
	 * @param $upload_in_customfield array
	 *
	 * @return array
	 */
	public function create_post_from_postdata( $postdata_fields, $upload_in_customfield ) {
        if ( empty( $postdata_fields ) ) {
            return array(
                            'type'  => 'error',
                            'value' => __( 'Failed to supply necessary data.', Forminator::DOMAIN )
                        );
        }

        $postdata_result = array();
        foreach ( $postdata_fields as $postdata_field ) {
            $field_id       = $postdata_field['field_id'];
            $field_data     = $postdata_field['field_data'];
            $field_array    = $postdata_field['field_array'];
            $form_field_obj = $postdata_field['form_field_obj'];

            // check if field_data of post values not empty (happen when postdata is not required)
            $filtered   = array_filter( $field_data );
            $post_value = $field_data;
            if ( ! empty( $filtered ) ) {
                if ( isset( $filtered['post-custom'] ) ) {
                    foreach( $filtered['post-custom'] as $custom_field_index => $custom_field ) {
                        if ( preg_match( '/\{upload-(\d+)\}/', $custom_field['value'] ) ) {
                            $upload_id = trim( $custom_field['value'], '{}' );

                            if ( ! empty( $upload_in_customfield ) ) {
                                foreach ( $upload_in_customfield as $cf_data ) {
                                    if ( $upload_id === $cf_data['upload_id'] && $field_id === $cf_data['postdata_id'] ) {
                                        $field_data['post-custom'][$custom_field_index]['value'] = $cf_data['uploads'];
                                    }
                                }
                            }
                        }
                    }
                }

                $post_id = $form_field_obj->save_post( $field_array, $field_data );
                if ( $post_id ) {
                    $field_data = [
                        'postdata' => $post_id,
                        'value'    => $field_data,
                    ];
                } else {
                    return array(
                        'type'     => 'error',
                        'field_id' => $field_id,
                        'value'    => __( 'There was an error saving the post data. Please try again', Forminator::DOMAIN )
                    );
                }
            } else {
                $field_data = [
                    'postdata' => null,
                    'value'    => $post_value,
                ];
            }

            $postdata_result[] = array(
                'type'       => 'success',
                'field_id'   => $field_id,
                'field_data' => $field_data,
            );
        }

		return $postdata_result;
	}

}
