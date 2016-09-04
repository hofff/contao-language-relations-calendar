<?php

$GLOBALS['BE_MOD']['content']['calendar']['stylesheet'][]
	= 'system/modules/hofff_language_relations/assets/css/style.css';

$GLOBALS['TL_HOOKS']['loadDataContainer']['hofff_language_relations_calendar']
	= [ 'Hofff\\Contao\\LanguageRelations\\Calendar\\DCA\\CalendarEventsDCA', 'hookLoadDataContainer' ];
$GLOBALS['TL_HOOKS']['sqlCompileCommands']['hofff_language_relations_calendar']
	= [ 'Hofff\\Contao\\LanguageRelations\\Calendar\\Database\\Installer', 'hookSQLCompileCommands' ];
$GLOBALS['TL_HOOKS']['hofff_language_relations_language_switcher']['hofff_language_relations_calendar']
	= [ 'Hofff\\Contao\\LanguageRelations\\Calendar\\LanguageRelationsCalendar', 'hookLanguageSwitcher' ];
