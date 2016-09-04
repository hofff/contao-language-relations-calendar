<?php

namespace Hofff\Contao\LanguageRelations\Calendar;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\PageModel;
use Hofff\Contao\LanguageRelations\Calendar\Util\ContaoCalendarUtil;
use Hofff\Contao\LanguageRelations\Module\ModuleLanguageSwitcher;
use Hofff\Contao\LanguageRelations\Relations;
use Hofff\Contao\LanguageRelations\Util\ContaoUtil;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class LanguageRelationsCalendar {

	/**
	 * @var Relations
	 */
	private static $relations;

	/**
	 * @return Relations
	 * @deprecated
	 */
	public static function getRelationsInstance() {
		isset(self::$relations) || self::$relations = new Relations(
			'tl_hofff_language_relations_calendar',
			'hofff_language_relations_calendar_item',
			'hofff_language_relations_calendar_relation'
		);
		return self::$relations;
	}

	/**
	 * @param array $items
	 * @param ModuleLanguageSwitcher $module
	 * @return array
	 */
	public function hookLanguageSwitcher(array $items, ModuleLanguageSwitcher $module) {
		$currentPage = $GLOBALS['objPage'];

		$currentEvent = ContaoCalendarUtil::findCurrentEvent($currentPage->id);
		if(!$currentEvent) {
			return $items;
		}

		$relatedEvents = self::getRelationsInstance()->getRelations($currentEvent);
		$relatedEvents[$currentPage->hofff_root_page_id] = $currentEvent;

		ContaoCalendarUtil::prefetchEventModels($relatedEvents);

		foreach($items as $rootPageID => &$item) {
			if(!isset($relatedEvents[$rootPageID])) {
				continue;
			}

			$event = CalendarEventsModel::findByPk($relatedEvents[$rootPageID]);
			if(!ContaoUtil::isPublished($event)) {
				continue;
			}

			$calendar = CalendarModel::findByPk($event->pid);
			if(!$calendar->jumpTo) {
				continue;
			}

			$page = PageModel::findByPk($calendar->jumpTo);
			if(!ContaoUtil::isPublished($page)) {
				continue;
			}

			$item['href']		= ContaoCalendarUtil::getEventURL($event);
			$item['pageTitle']	= strip_tags($event->title);
		}
		unset($item);

		return $items;
	}

}
