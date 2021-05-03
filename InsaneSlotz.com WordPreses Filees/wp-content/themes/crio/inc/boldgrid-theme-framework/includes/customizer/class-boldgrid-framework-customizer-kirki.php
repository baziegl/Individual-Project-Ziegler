<?php
/**
 * Footer Customizer Functionality
 *
 * @link http://www.boldgrid.com
 * @since 1.0.0
 *
 * @package Boldgrid_Theme_Framework
 */

/**
 * Class: Boldgrid_Framework_Customizer_Kirki
 *
 * General Kirki settings to help generate WordPress customizer controls.
 *
 * @since      1.0.0
 * @category   Customizer
 * @package    Boldgrid_Framework
 * @subpackage Boldgrid_Framework_Customizer_Kirki
 * @author     BoldGrid <support@boldgrid.com>
 * @link       https://boldgrid.com
 */
class Boldgrid_Framework_Customizer_Kirki {

	/**
	 * The BoldGrid Theme Framework configurations.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       string     $configs       The BoldGrid Theme Framework configurations.
	 */
	protected $configs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     1.0.0
	 * @param     string $configs       The BoldGrid Theme Framework configurations.
	 */
	public function __construct( $configs ) {
		$this->configs = $configs;
	}


	/**
	 * Add Kirki Configs
	 *
	 * @param  array $controls Array of controls to pass to kirki.
	 * @return array $args     Logo path and textdomain information.
	 */
	public function general_kirki_configs( $controls ) {
		$url = $this->configs['framework']['root_uri'] . 'includes/kirki/';

		Kirki::$url = $url;

		$args = array(
			'url_path'     => $url,
			'textdomain'   => 'boldgrid',
		);

		return $args;
	}

	/**
	 * This method returns the language translation strings used in the
	 * BoldGrid Theme Framework Kirki implmentation.
	 *
	 * @since 2.0.0
	 */
	public function l10n( $l10n ) {
		$l10n['background-color']      = esc_attr__( 'Background Color', 'crio' );
		$l10n['background-image']      = esc_attr__( 'Background Image', 'crio' );
		$l10n['no-repeat']             = esc_attr__( 'No Repeat', 'crio' );
		$l10n['repeat-all']            = esc_attr__( 'Repeat All', 'crio' );
		$l10n['repeat-x']              = esc_attr__( 'Repeat Horizontally', 'crio' );
		$l10n['repeat-y']              = esc_attr__( 'Repeat Vertically', 'crio' );
		$l10n['inherit']               = esc_attr__( 'Inherit', 'crio' );
		$l10n['background-repeat']     = esc_attr__( 'Background Repeat', 'crio' );
		$l10n['cover']                 = esc_attr__( 'Cover', 'crio' );
		$l10n['contain']               = esc_attr__( 'Contain', 'crio' );
		$l10n['background-size']       = esc_attr__( 'Background Size', 'crio' );
		$l10n['fixed']                 = esc_attr__( 'Fixed', 'crio' );
		$l10n['scroll']                = esc_attr__( 'Scroll', 'crio' );
		$l10n['background-attachment'] = esc_attr__( 'Background Attachment', 'crio' );
		$l10n['left-top']              = esc_attr__( 'Left Top', 'crio' );
		$l10n['left-center']           = esc_attr__( 'Left Center', 'crio' );
		$l10n['left-bottom']           = esc_attr__( 'Left Bottom', 'crio' );
		$l10n['right-top']             = esc_attr__( 'Right Top', 'crio' );
		$l10n['right-center']          = esc_attr__( 'Right Center', 'crio' );
		$l10n['right-bottom']          = esc_attr__( 'Right Bottom', 'crio' );
		$l10n['center-top']            = esc_attr__( 'Center Top', 'crio' );
		$l10n['center-center']         = esc_attr__( 'Center Center', 'crio' );
		$l10n['center-bottom']         = esc_attr__( 'Center Bottom', 'crio' );
		$l10n['background-position']   = esc_attr__( 'Background Position', 'crio' );
		$l10n['background-opacity']    = esc_attr__( 'Background Opacity', 'crio' );
		$l10n['on']                    = esc_attr__( 'ON', 'crio' );
		$l10n['off']                   = esc_attr__( 'OFF', 'crio' );
		$l10n['all']                   = esc_attr__( 'All', 'crio' );
		$l10n['cyrillic']              = esc_attr__( 'Cyrillic', 'crio' );
		$l10n['cyrillic-ext']          = esc_attr__( 'Cyrillic Extended', 'crio' );
		$l10n['devanagari']            = esc_attr__( 'Devanagari', 'crio' );
		$l10n['greek']                 = esc_attr__( 'Greek', 'crio' );
		$l10n['greek-ext']             = esc_attr__( 'Greek Extended', 'crio' );
		$l10n['khmer']                 = esc_attr__( 'Khmer', 'crio' );
		$l10n['latin']                 = esc_attr__( 'Latin', 'crio' );
		$l10n['latin-ext']             = esc_attr__( 'Latin Extended', 'crio' );
		$l10n['vietnamese']            = esc_attr__( 'Vietnamese', 'crio' );
		$l10n['hebrew']                = esc_attr__( 'Hebrew', 'crio' );
		$l10n['arabic']                = esc_attr__( 'Arabic', 'crio' );
		$l10n['bengali']               = esc_attr__( 'Bengali', 'crio' );
		$l10n['gujarati']              = esc_attr__( 'Gujarati', 'crio' );
		$l10n['tamil']                 = esc_attr__( 'Tamil', 'crio' );
		$l10n['telugu']                = esc_attr__( 'Telugu', 'crio' );
		$l10n['thai']                  = esc_attr__( 'Thai', 'crio' );
		$l10n['serif']                 = _x( 'Serif', 'font style', 'crio' );
		$l10n['sans-serif']            = _x( 'Sans Serif', 'font style', 'crio' );
		$l10n['monospace']             = _x( 'Monospace', 'font style', 'crio' );
		$l10n['font-family']           = esc_attr__( 'Font Family', 'crio' );
		$l10n['font-size']             = esc_attr__( 'Font Size', 'crio' );
		$l10n['font-weight']           = esc_attr__( 'Font Weight', 'crio' );
		$l10n['line-height']           = esc_attr__( 'Line Height', 'crio' );
		$l10n['font-style']            = esc_attr__( 'Font Style', 'crio' );
		$l10n['letter-spacing']        = esc_attr__( 'Letter Spacing', 'crio' );
		$l10n['top']                   = esc_attr__( 'Top', 'crio' );
		$l10n['bottom']                = esc_attr__( 'Bottom', 'crio' );
		$l10n['left']                  = esc_attr__( 'Left', 'crio' );
		$l10n['right']                 = esc_attr__( 'Right', 'crio' );
		$l10n['color']                 = esc_attr__( 'Color', 'crio' );
		$l10n['add-image']             = esc_attr__( 'Add Image', 'crio' );
		$l10n['change-image']          = esc_attr__( 'Change Image', 'crio' );
		$l10n['remove']                = esc_attr__( 'Remove', 'crio' );
		$l10n['no-image-selected']     = esc_attr__( 'No Image Selected', 'crio' );
		$l10n['select-font-family']    = esc_attr__( 'Select a font-family', 'crio' );
		$l10n['variant']               = esc_attr__( 'Variant', 'crio' );
		$l10n['subsets']               = esc_attr__( 'Subset', 'crio' );
		$l10n['size']                  = esc_attr__( 'Size', 'crio' );
		$l10n['height']                = esc_attr__( 'Height', 'crio' );
		$l10n['spacing']               = esc_attr__( 'Spacing', 'crio' );
		$l10n['ultra-light']           = esc_attr__( 'Ultra-Light 100', 'crio' );
		$l10n['ultra-light-italic']    = esc_attr__( 'Ultra-Light 100 Italic', 'crio' );
		$l10n['light']                 = esc_attr__( 'Light 200', 'crio' );
		$l10n['light-italic']          = esc_attr__( 'Light 200 Italic', 'crio' );
		$l10n['book']                  = esc_attr__( 'Book 300', 'crio' );
		$l10n['book-italic']           = esc_attr__( 'Book 300 Italic', 'crio' );
		$l10n['regular']               = esc_attr__( 'Normal 400', 'crio' );
		$l10n['italic']                = esc_attr__( 'Normal 400 Italic', 'crio' );
		$l10n['medium']                = esc_attr__( 'Medium 500', 'crio' );
		$l10n['medium-italic']         = esc_attr__( 'Medium 500 Italic', 'crio' );
		$l10n['semi-bold']             = esc_attr__( 'Semi-Bold 600', 'crio' );
		$l10n['semi-bold-italic']      = esc_attr__( 'Semi-Bold 600 Italic', 'crio' );
		$l10n['bold']                  = esc_attr__( 'Bold 700', 'crio' );
		$l10n['bold-italic']           = esc_attr__( 'Bold 700 Italic', 'crio' );
		$l10n['extra-bold']            = esc_attr__( 'Extra-Bold 800', 'crio' );
		$l10n['extra-bold-italic']     = esc_attr__( 'Extra-Bold 800 Italic', 'crio' );
		$l10n['ultra-bold']            = esc_attr__( 'Ultra-Bold 900', 'crio' );
		$l10n['ultra-bold-italic']     = esc_attr__( 'Ultra-Bold 900 Italic', 'crio' );
		$l10n['invalid-value']         = esc_attr__( 'Invalid Value', 'crio' );

		return $l10n;
	}
}

/**
 * Add the theme configuration
 */
Kirki::add_config(
	'bgtfw',
	array(
		'option_type' => 'theme_mod',
		'capability'  => 'edit_theme_options',
		'gutenberg_support' => true,
	)
);
