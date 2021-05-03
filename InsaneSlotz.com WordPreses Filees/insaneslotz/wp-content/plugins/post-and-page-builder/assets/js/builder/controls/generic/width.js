window.BOLDGRID = window.BOLDGRID || {};
BOLDGRID.EDITOR = BOLDGRID.EDITOR || {};
BOLDGRID.EDITOR.CONTROLS = BOLDGRID.EDITOR.CONTROLS || {};
BOLDGRID.EDITOR.CONTROLS.GENERIC = BOLDGRID.EDITOR.CONTROLS.GENERIC || {};

( function( $ ) {
	'use strict';

	var self,
		BG = BOLDGRID.EDITOR;

	BOLDGRID.EDITOR.CONTROLS.GENERIC.Width = {
		template: wp.template( 'boldgrid-editor-generic-width' ),

		render: function() {
			var $control = $( this.template() ),
				$customize = BG.Panel.$element.find( '.panel-body .customize' );

			$customize.find( '.section.width-control' ).remove();
			$customize.append( $control );

			return $control;
		},

		bind: function() {
			var maxVal = 100,
				$target = BG.Menu.getCurrentTarget(),
				width = $target[0].style.width || $target.attr( 'width' );

			width = width ? parseInt( width ) : maxVal;
			width = Math.min( width, maxVal );
			width = Math.max( width, 0 );

			BG.Panel.$element
				.find( '.panel-body .customize .width .slider' )
				.slider( {
					min: 10,
					max: 100,
					value: width,
					range: 'max',
					slide: function( event, ui ) {
						if ( 100 === ui.value ) {
							BG.Controls.addStyle( $target, 'width', 'auto' );
						} else {
							BG.Controls.addStyle( $target, 'width', ui.value + '%' );
						}
					}
				} )
				.siblings( '.value' )
				.html( width );
		}
	};

	self = BOLDGRID.EDITOR.CONTROLS.GENERIC.Width;
} )( jQuery );
