<?php

/**
 * File: class=crio-premium-page-headers-meta.php
 *
 * Adds extra post meta and meta boxes to page header templates.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers/Templates
 */

/**
 * Class: Crio_Premium_Page_Headers_Meta
 *
 * Adds extra post meta and meta boxes to page header templates.
 */
class Crio_Premium_Page_Headers_Templates_Meta {

	/**
	 * Page Headers Base
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_base
	 */
	public $page_haders_base;

	/**
	 * Allowed Html for wp_kses
	 *
	 * @since 1.1.0
	 * @var array
	 */
	public $wpkses_allowed_html = array(
		'div'      => array(
			'class' => array(),
			'id'    => array(),
		),
		'fieldset' => array(
			'class' => array(),
		),
		'span'     => array(
			'class' => array(),
			'id'    => array(),
		),
		'br'       => array(),
		'input'    => array(
			'class'   => array(),
			'type'    => array(),
			'id'      => array(),
			'name'    => array(),
			'value'   => array(),
			'checked' => array(),
		),
		'svg'      => array(
			'xmlns'       => array(),
			'viewBox'     => array(),
			'width'       => array(),
			'height'      => array(),
			'role'        => array(),
			'class'       => array(),
			'aria-hidden' => array(),
			'focusable'   => array(),
		),
		'path'     => array(
			'd' => array(),
		),
		'label'    => array(
			'for'   => array(),
			'class' => array(),
		),
		'p'        => array(
			'id'    => array(),
			'class' => array(),
		),
		'a'        => array(
			'href'   => array(),
			'target' => array(),
		),
	);

	/**
	 * Override Post Types
	 *
	 * The list of posts that will be given
	 * an option to override page header selections.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	public $override_post_types;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 *
	 * @param Crio_Premium_Page_Headers_Base $page_headers_base Page Headers Base object.
	 */
	public function __construct( $base ) {
		$this->base = $base;

		$this->override_post_types = array(
			'post',
			'page',
		);
	}

	/** Registers Custom Post Meta Field.
	 *
	 * @since 1.1.0
	 */
	public function register_post_meta() {

		// Determines whether or not to include the site header in the page header.
		register_post_meta(
			'crio_page_header',
			'crio-premium-include-site-header',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		// Determines whether or not to merge the site header with page header.
		register_post_meta(
			'crio_page_header',
			'crio-premium-merge-site-header',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		// Registers menu locations in this post's meta.
		register_post_meta(
			'crio_page_header',
			'crio-premium-menus',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		// Specifies whether or not this page header contains the 'Page Title' component.
		register_post_meta(
			'crio_page_header',
			'crio-premium-template-has-page-title',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		// Determines whether to use Global Page Header selection or override it.
		register_post_meta(
			'',
			'crio-premium-page-header-override',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			)
		);

		// Defines the post's overriding header.
		register_post_meta(
			'',
			'crio-premium-page-header-select',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		// Sets a background image to override page header background.
		register_post_meta(
			'',
			'crio-premium-page-header-background',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
	}

	/**
	 * Add Meta Boxes.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type Type of Post.
	 */
	public function add_template_metabox( $post_type ) {

		// Add the post meta box for site header inclusion / merging.
		if ( 'crio_page_header' === $post_type ) {
			add_meta_box(
				'crio-premium-include-site-header',
				__( 'Include Site Header', 'crio-premium' ),
				array( $this, 'include_header_callback' ),
				array( 'crio_page_header' ),
				'side',
				'low'
			);
		}
		if ( get_theme_mod( 'bgtfw_page_headers_global_enabled', true ) ) {
			// Add the post meta box for overriding header selection.
			foreach ( $this->override_post_types as $post_type ) {
				add_meta_box(
					'crio-premium-page-header-override',
					__( 'Page Header', 'crio-premium' ),
					array( $this, 'override_header_callback' ),
					$post_type,
					'side',
					'low'
				);
			}
		}
	}

	/**
	 * Override Header Background Callback
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post WordPress Post Object
	 */
	public function override_header_background( $post ) {
		$name             = 'crio-premium-page-header-background';
		$value            = get_post_meta( $post->ID, $name, true );
		$image            = ' button">' . __( 'Upload Image', 'crio-premium' );
		$image_size       = 'full'; // it would be better to use thumbnail size here (150x150 or so)
		$display          = 'none'; // display state ot the "Remove image" button
		$image_attributes = wp_get_attachment_image_src( $value, $image_size );
		?>
		<div class="crio_premium_override_background">
			<hr>
			<div class="bgtfw-custom-meta__description">
				<p class="description">
					<?php esc_html_e( 'Background Image', 'crio-premium' ); ?>
				</p>
			</div>
		<?php

		if ( $image_attributes ) {
			?>
			<div>
				<a href="#" class="crio_premium_image_button"><img src="<?php echo esc_attr( $image_attributes[0] ); ?>" style="max-width:95%;display:block;" /></a>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
				<p class="crio_premium_image_desc" style="display:inline-block"><?php esc_html_e( 'Click the image to edit or update', 'crio-premium' ); ?></p>
				<a href="#" class="crio_premium_remove_image_button" style="display:inline-block;display:inline-block"><?php esc_html_e( 'Remove Image', 'crio-premium' ); ?></a>
			</div>
			<?php

		} else {
			?>
		<div>
			<a href="#" class="crio_premium_image_button button"><?php esc_html_e( 'Upload Image', 'crio-premium' ); ?></a>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<p class="crio_premium_image_desc" style="display:none"><?php esc_html_e( 'Click the image to edit or update', 'crio-premium' ); ?></p>
			<a href="#" class="crio_premium_remove_image_button" style="display:inline-block;display:none"><?php esc_html_e( 'Remove Image', 'crio-premium' ); ?></a>
		</div>
		</div>
			<?php
		}
	}

	/**
	 * Override Page Header Meta Box Callback
	 *
	 * This metabox is called on post types in the $this->override_post_types
	 * array, but NOT on the header template type. This metabox provides options for
	 * overriding the chosen header template on that given post.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post WordPress Post Object.
	 */
	public function override_header_callback( $post ) {
		$global_post_template  = $this->base->templates->edit_post_template( $post );
		$global_post_template  = $global_post_template ? $global_post_template : 'none';
		$global_or_post        = get_post_meta( $post->ID, 'crio-premium-page-header-override', true );
		$global_or_post        = $global_or_post ? $global_or_post : 'global';
		$selected_template     = get_post_meta( $post->ID, 'crio-premium-page-header-select', true );
		$selected_template     = empty( $selected_template ) ? $global_post_template : $selected_template;
		$global_template_label = 'Customizer Header';
		$available_templates   = $this->base->templates->get_available();
		if ( 'none' !== $global_post_template ) {
			$global_template_label = get_post( $global_post_template )->post_title;
		}
		if ( 'none' !== $selected_template ) {
			$selected_template_label = get_post( $selected_template )->post_title;
		} else {
			$selected_template_label = 'Customizer Settings';
		}
		?>
			<div class="bgtfw-custom-meta__description">
				<p class="description">
					<?php esc_html_e( 'Choose a page header to use for this post / page.', 'crio-premium' ); ?>
				</p>
			</div>

			<div class="bgtfw-custom-meta__choice">
				<div class="crio-premium-page-header-override-global">
					<input type="radio" name="crio-premium-page-header-override" id="crio-premium-page-header-override-global" class="bgtfw-custom-meta__dialog-radio" value='global'
						<?php echo esc_attr( 'global' === $global_or_post ? 'checked' : '' ); ?>>
					<label for="crio-premium-page-header-override-global" class="bgtfw-custom-meta__dialog-label">
						<span class="choice-label"><?php esc_html_e( 'Use Global Setting:', 'crio-premium' ); ?></span>
						<span class="template-subtitle"><?php echo esc_html( $global_template_label ); ?></span>
					</label>
				</div>
				<div class="crio-premium-page-header-override-post">
					<input type="radio" name="crio-premium-page-header-override" id="crio-premium-page-header-override-post" class="bgtfw-custom-meta__dialog-radio" value='post'
						<?php echo esc_attr( 'post' === $global_or_post ? 'checked' : '' ); ?>>
					<label for="crio-premium-page-header-override-post" class="bgtfw-custom-meta__dialog-label">
						<span class="choice-label"><?php esc_html_e( 'Use Post Setting:', 'crio-premium' ); ?></span>
						<span class="post-setting-label template-subtitle"><?php echo esc_html( $selected_template_label ); ?></span>
					</label>
				</div>
				<div>
					<select id="crio-premium-page-header-select" class="bgtfw-custom-meta__dialog-select" name="crio-premium-page-header-select">
						<option value="none"><?php esc_html_e( 'None: ( Use Customizer Settings )', 'crio-premium' ); ?></option>
						<?php
						foreach ( $available_templates as $value => $label ) {
							$selected = $value === (int) $selected_template ? 'selected' : ''
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $label ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
		<?php
		$this->override_header_background( $post );
	}

	/**
	 * Include Header Meta Box Callback.
	 *
	 * This meta box is used only on the header templates post type.
	 * This box is used to determine whether or not to include the site header in this
	 * template, and whether or not to merge the page header with the site header.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post WordPress Post Object.
	 */
	public function include_header_callback( $post ) {
		$is_included = get_post_meta( $post->ID, 'crio-premium-include-site-header', true );
		$is_merged   = get_post_meta( $post->ID, 'crio-premium-merge-site-header', true );
		if ( '' === $is_included ) {
			$is_included = '1';
		}
		echo wp_kses(
			'<div class="bgtfw-custom-meta__description">
				<p class="description">

				</p>
			</div>',
			$this->wpkses_allowed_html
		);

		echo wp_kses(
			'<fieldset class="bgtfw-custom-meta__dialog-fieldset">
				<div class="bgtfw-custom-meta__choice include-site-header">
					<input type="checkbox" name="crio-premium-include-site-header" id="crio-premium-include-site-header" class="bgtfw-custom-meta__dialog-checkbox" value="1" ' .
					( '1' === $is_included ? 'checked' : '' ) . '>
					<label for="crio-premium-include-site-header" class="bgtfw-custom-meta__dialog-label">',
			$this->wpkses_allowed_html
		);

		echo esc_html__( 'Include Site Header', 'crio-premium' );

		echo wp_kses(
			'<span class="tooltiptext">Enable this If you wish to use the Customizer Site Header, and include it in your Page Header Template.</span>',
			$this->wpkses_allowed_html
		);
		echo wp_kses(
			'	</label>
			</div>
			<div class="bgtfw-custom-meta__choice merge-site-header">
					<input type="checkbox" name="crio-premium-merge-site-header" id="crio-premium-merge-site-header" class="bgtfw-custom-meta__dialog-checkbox" value="1" ' .
					( '1' === $is_merged ? 'checked' : '' ) . '>
					<label for="crio-premium-merge-site-header" class="bgtfw-custom-meta__dialog-label">',
			$this->wpkses_allowed_html
		);

		echo esc_html__( 'Merge Page Header', 'crio-premium' );

		echo wp_kses(
			'<span class="tooltiptext">Enabling this will cause the Customizer Header to become transparent, and overlay this page header</span>',
			$this->wpkses_allowed_html
		);

		echo wp_kses(
			'		</label>
				</div>
			</fieldset>',
			$this->wpkses_allowed_html
		);
	}

	/**
	 * Saves metadata.
	 *
	 * This is called when saving header templates only.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post WP_Post Object.
	 */
	public function save_metadata( $post ) {
		$post_id = $post->ID;
		if ( array_key_exists( 'crio-premium-include-site-header', $_POST ) ) { //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			update_post_meta(
				$post_id,
				'crio-premium-include-site-header',
				$_POST['crio-premium-include-site-header'] //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			);
		} else {
			update_post_meta(
				$post_id,
				'crio-premium-include-site-header',
				'0'
			);
		}

		if ( array_key_exists( 'crio-premium-merge-site-header', $_POST ) ) { //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			update_post_meta(
				$post_id,
				'crio-premium-merge-site-header',
				$_POST['crio-premium-merge-site-header'] //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			);
		} else {
			update_post_meta(
				$post_id,
				'crio-premium-merge-site-header',
				'0'
			);
		}

		// If this template contains a page title component, set this to true. Otherwise false.
		if ( false === strpos( $post->post_content, 'wp_boldgrid_component_page_title' ) ) {
			update_post_meta( $post_id, 'crio-premium-template-has-page-title', '0' );
		} else {
			update_post_meta( $post_id, 'crio-premium-template-has-page-title', '1' );
		}
	}

	/**
	 * Save Override Meta Data
	 *
	 * This is called by the 'save_post' action hook, so it will run when saving
	 * any post type. Make sure to use any necessary logic to prevent undesired
	 * effects on other posts.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_id Post id.
	 */
	public function save_override_meta( $post_id ) {
		if ( array_key_exists( 'crio-premium-page-header-override', $_POST ) ) { //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			update_post_meta(
				$post_id,
				'crio-premium-page-header-override',
				$_POST['crio-premium-page-header-override'] //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			);
		}

		if ( array_key_exists( 'crio-premium-page-header-select', $_POST ) ) { //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			update_post_meta(
				$post_id,
				'crio-premium-page-header-select',
				$_POST['crio-premium-page-header-select'] //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			);
		}

		if ( array_key_exists( 'crio-premium-page-header-background', $_POST ) ) { //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			update_post_meta(
				$post_id,
				'crio-premium-page-header-background',
				$_POST['crio-premium-page-header-background'] //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			);
		}
	}
}
