<?php

namespace Hofff\Contao\LanguageRelations\Calendar\DCA;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CalendarEventsDCA {

	/**
	 * @param string $table
	 * @return void
	 */
	public function hookLoadDataContainer($table) {
		if($table != 'tl_calendar_events') {
			return;
		}

		$palettes = &$GLOBALS['TL_DCA']['tl_calendar_events']['palettes'];
		foreach($palettes as $key => &$palette) {
			if($key != '__selector__') {
				$palette .= ';{hofff_language_relations_legend}';
				$palette .= ',hofff_language_relations';
			}
		}
		unset($palette, $palettes);
	}

}
