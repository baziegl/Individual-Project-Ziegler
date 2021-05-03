<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Module_Edit_Page
 *
 * @since 1.14.10
 */
abstract class Forminator_Admin_Module_Edit_Page extends Forminator_Admin_Page {

	/**
	 * Reset views data
	 *
	 * @since 1.6
	 *
	 * @param int $id Module ID.
	 */
	public static function reset_module_views( $id ) {
		$form_types = forminator_form_types();
		$module     = get_post( $id );
		if ( ! empty( $module->post_type ) && in_array( $module->post_type, $form_types, true ) ) {
			$form_view = Forminator_Form_Views_Model::get_instance();
			$form_view->delete_by_form( $id );
		}
	}
}
