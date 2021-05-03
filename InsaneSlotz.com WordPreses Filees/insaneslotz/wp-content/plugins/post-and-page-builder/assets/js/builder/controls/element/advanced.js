var $ = window.jQuery,
	BG = BOLDGRID.EDITOR;

export class Advanced {
	constructor() {
		this.name = 'Advanced';

		this.panel = {
			title: 'Advanced',
			height: '650px',
			width: '375px',
			customizeCallback: true,
			customizeSupport: [
				'margin',
				'padding',
				'border',
				'width',
				'box-shadow',
				'border-radius',
				'animation',
				'background-color',
				'blockAlignment',
				'device-visibility',
				'customClasses'
			]
		};
	}

	/**
	 * Initialize this controls, usually runs right after the constructor.
	 *
	 * @since 1.6
	 */
	init() {
		BG.Controls.registerControl( this );
	}

	_setTargetType( targetType ) {
		BG.Panel.$element.find( '.customize-navigation' ).attr( 'data-element-type', targetType );
	}

	/**
	 * Open the palette customization panel.
	 *
	 * @since 1.6.0
	 */
	openPanel( $target, targetType ) {
		this.$target = $target;
		BG.Menu.$element.targetData[this.name] = $target;

		BG.Panel.clear();
		BG.Panel.showFooter();
		BG.Panel.open( this );
		this._setTargetType( targetType );
		BG.Panel.enterCustomization();
		BG.Panel.customizeOpenEvent();
	}
}

export { Advanced as default };
