var $ = window.jQuery,
	BG = BOLDGRID.EDITOR;

import { Base } from './base.js';
import template from '../../../../includes/template/popover/column.html';

export class Column extends Base {
	constructor() {
		super();

		this.template = template;

		this.name = 'column';

		return this;
	}

	/**
	 * Get a position for the popover.
	 *
	 * @since 1.6
	 *
	 * @param  {object} clientRect Current coords.
	 * @return {object}            Css for positioning.
	 */
	getPositionCss( clientRect ) {
		return {
			top: clientRect.top,
			left: clientRect.left
		};
	}

	/**
	 * Get the current selector string depending on drag mode.
	 *
	 * @since 1.6
	 *
	 * @return {string} Selectors.
	 */
	getSelectorString() {
		if ( BG.Controls.$container.editting_as_row ) {
			return BG.Controls.$container.nestedColumnSelector;
		} else {
			return BG.Controls.$container.original_selector_strings.unformatted_column_selectors_string;
		}
	}
}

export { Column as default };
