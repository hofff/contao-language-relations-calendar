<?php

Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_calendar_tree');
Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_calendar_aggregate');
Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_calendar_relation');
Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_calendar_item');
