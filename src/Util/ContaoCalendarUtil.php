<?php

namespace Hofff\Contao\LanguageRelations\Calendar\Util;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Config;
use Contao\Events;
use Contao\Input;
use Contao\PageModel;
use Hofff\Contao\LanguageRelations\Util\QueryUtil;


/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class ContaoCalendarUtil extends Events {

	/**
	 */
	public function __construct() {
	}

	/**
	 * @param integer|null $jumpTo
	 * @return integer|null
	 */
	public static function findCurrentEvent($jumpTo = null) {
		if(isset($_GET['events'])) {
			$idOrAlias = Input::get('events', false, true);
		} elseif(isset($_GET['auto_item']) && Config::get('useAutoItem')) {
			$idOrAlias = Input::get('auto_item', false, true);
		} else {
			return null;
		}

		$sql = <<<SQL
SELECT
	event.id			AS event_id,
	calendar.jumpTo		AS calendar_jump_to
FROM
	tl_calendar_events
	AS event
JOIN
	tl_calendar
	AS calendar
	ON calendar.id = event.pid
WHERE
	event.id = ? OR event.alias = ?
SQL;
		$result = QueryUtil::query(
			$sql,
			null,
			[ $idOrAlias, $idOrAlias ]
		);

		if(!$result->numRows) {
			return null;
		}

		if($jumpTo === null || $jumpTo == $result->calendar_jump_to) {
			return $result->event_id;
		}

		return null;
	}

	/**
	 * @param CalendarEventsModel $event
	 * @return string
	 */
	public static function getEventURL(CalendarEventsModel $event) {
		$calendar = CalendarModel::findByPk($event->pid);
		if(!$calendar) {
			return null;
		}

		$page = PageModel::findByPk($calendar->jumpTo);
		if(!$page) {
			return null;
		}

		$autoitem = Config::get('useAutoItem') && !Config::get('disableAlias');
		$paramToken = $autoitem ? '/%s' : '/events/%s';
		$url = $page->getFrontendUrl($paramToken);

		static $instance;
		$instance || $instance = new self;

		return $instance->generateEventUrl($event, $url);
	}

	/**
	 * @param array $ids
	 * @return void
	 */
	public static function prefetchEventModels(array $ids) {
		$calendars = [];
		foreach(CalendarEventsModel::findMultipleByIds(array_values($ids)) as $event) {
			$calendars[] = $event->pid;
		}

		$pages = [];
		foreach(CalendarModel::findMultipleByIds($calendars) as $calendars) {
			$calendars->jumpTo && $pages[] = $calendars->jumpTo;
		}

		PageModel::findMultipleByIds($pages);
	}

	/**
	 * @see \Contao\Module::compile()
	 */
	protected function compile() {
	}

}
