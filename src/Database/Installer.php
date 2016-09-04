<?php

namespace Hofff\Contao\LanguageRelations\Calendar\Database;

use Contao\Database;
use Hofff\Contao\LanguageRelations\Util\StringUtil;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Installer {

	/**
	 * @param array $queries
	 * @return void
	 */
	public function hookSQLCompileCommands($queries) {
		$tables = array_flip(Database::getInstance()->listTables(null, true));

		if(!isset($tables['hofff_language_relations_calendar_item'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getItemView());
		}
		if(!isset($tables['hofff_language_relations_calendar_relation'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getRelationView());
		}
		if(!isset($tables['hofff_language_relations_calendar_aggregate'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getAggregateView());
		}
		if(!isset($tables['hofff_language_relations_calendar_tree'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getTreeView());
		}

		return $queries;
	}

	/**
	 * @return string
	 */
	protected function getItemView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_calendar_item AS

SELECT
	root_page.hofff_language_relations_group_id	AS group_id,
	root_page.id								AS root_page_id,
	page.id										AS page_id,
	event.id									AS item_id
FROM
	tl_calendar_events
	AS event
JOIN
	tl_calendar
	AS calendar
	ON calendar.id = event.pid
JOIN
	tl_page
	AS page
	ON page.id = calendar.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
SQL;
	}

	/**
	 * @return string
	 */
	protected function getRelationView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_calendar_relation AS

SELECT
	item.group_id									AS group_id,
	item.root_page_id								AS root_page_id,
	item.page_id									AS page_id,
	item.item_id									AS item_id,
	related_item.item_id							AS related_item_id,
	related_item.page_id							AS related_page_id,
	related_item.root_page_id						AS related_root_page_id,
	related_item.group_id							AS related_group_id,
	item.root_page_id != related_item.root_page_id
		AND item.group_id = related_item.group_id	AS is_valid,
	reflected_relation.item_id IS NOT NULL			AS is_primary

FROM
	tl_hofff_language_relations_calendar
	AS relation
JOIN
	hofff_language_relations_calendar_item
	AS item
	ON item.item_id = relation.item_id
JOIN
	hofff_language_relations_calendar_item
	AS related_item
	ON related_item.item_id = relation.related_item_id

LEFT JOIN
	tl_hofff_language_relations_calendar
	AS reflected_relation
	ON reflected_relation.item_id = relation.related_item_id
	AND reflected_relation.related_item_id = relation.item_id
SQL;
	}

	/**
	 * @return string
	 */
	protected function getAggregateView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_calendar_aggregate AS

SELECT
	calendar.id					AS aggregate_id,
	CONCAT('c', calendar.id)	AS tree_root_id,
	root_page.id				AS root_page_id,
	grp.id						AS group_id,
	grp.title					AS group_title,
	root_page.language			AS language
FROM
	tl_calendar
	AS calendar
JOIN
	tl_page
	AS page
	ON page.id = calendar.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
JOIN
	tl_hofff_language_relations_group
	AS grp
	ON grp.id = root_page.hofff_language_relations_group_id
SQL;
	}

	/**
	 * @return string
	 */
	protected function getTreeView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_calendar_tree AS

SELECT
	0																AS pid,
	CONCAT('c', calendar.id)										AS id,
	calendar.title													AS title,
	0																AS selectable,
	root_page.hofff_language_relations_group_id						AS group_id,
	root_page.language												AS language,
	'calendar'														AS type,
	NULL															AS date
FROM
	tl_calendar
	AS calendar
JOIN
	tl_page
	AS page
	ON page.id = calendar.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id

UNION SELECT
	CONCAT('c', calendar.id)											AS pid,
	CONCAT('c', calendar.id, '_', YEAR(FROM_UNIXTIME(event.startDate)))	AS id,
	YEAR(FROM_UNIXTIME(event.startDate))								AS title,
	0																	AS selectable,
	root_page.hofff_language_relations_group_id							AS group_id,
	root_page.language													AS language,
	'year'																AS type,
	YEAR(FROM_UNIXTIME(event.startDate))								AS date
FROM
	tl_calendar_events
	AS event
JOIN
	tl_calendar_archive
	AS calendar
	ON calendar.id = event.pid
JOIN
	tl_page
	AS page
	ON page.id = calendar.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
GROUP BY
	YEAR(FROM_UNIXTIME(event.startDate)),
	calendar.id,
	root_page.language,
	root_page.hofff_language_relations_group_id

UNION SELECT
	CONCAT('c', calendar.id, '_', YEAR(FROM_UNIXTIME(event.startDate)))	AS pid,
	event.id															AS id,
	event.title															AS title,
	1																	AS selectable,
	root_page.hofff_language_relations_group_id							AS group_id,
	root_page.language													AS language,
	'entry'																AS type,
	event.startDate														AS date
FROM
	tl_calendar_events
	AS event
JOIN
	tl_calendar
	AS calendar
	ON calendar.id = event.pid
JOIN
	tl_page
	AS page
	ON page.id = calendar.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
SQL;
	}

}
