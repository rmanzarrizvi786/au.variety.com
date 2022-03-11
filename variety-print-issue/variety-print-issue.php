<?php
/**
 * Plugin Name: Variety Print Issue
 * Plugin URI: http://www.variety.com
 * Version: 1.0
 * Author: Hau Vong, PMC
 * Author URI: http://www.pmc.com
 * Author Email: hvong@pmc.com
 * License: PMC proprietary. All rights reserved.
 *
 * This plugin is written to automate the process of creating new print issue represented by a taxonomy.
 * The print issue taxonomy is use to identify each printed issue where editors can assign the printed article to.
 * Each print issue taxonomy represent a single issue.  The print taxonomy slug and title represent a single print
 * issue. The slug has a parsable format [volume]-[issue]-[FullMonth-Day-Year].
 *
 * In order to identify the current issue, we create a fix taxonomy call issue marker with slug 'issue-marker'.
 * We use the taxonomy marker to point to the current issue by setting the issue-marker parent to current print
 * issue taxonomy. Each week, we need to create a new taxonomy issue and move the issue-marker.
 *
 * The issue marker is use in by a custom feed where it use the issue-marker to identify current print
 * so it can package all articles belonging to the current print for syndication.
 *
 * Instead of having editors manually do all this task every day, we implement a cron job task to run once a week.
 * In order to know what volume and issue to assign, we're relying on a set of schedule by identifying the starting
 * issue and volume for a specific date.  When editors know a volume to be change on a certain date, they can queue
 * them up a head of time.
 *
 * For each print volume, the issue number start from a given number and auto increase by one each week.
 * For each print volume increase/change, the issue number restart from a given number (usually from 1)
 * Sometime due to holiday, the print schedule might be off, or there was one-off print.
 * The volume, issue, date might out of sync and not correct.  This is where editor can make correction each week
 * from the admin alert when a new print issue is create.
 *
 * Updated in May 2017 by XWP.
 * Shortcode added in April 2020 by Lara Schenck
 *
 * @see http://docs.pmc.com/2013/07/26/variety-print-issue-management/
 *
 */

/* All classes initialized here */

\Variety\Plugins\Variety_Print_Issue\Print_Issue_Shortcode::get_instance();

\Variety\Plugins\Variety_Print_Issue\Print_Issue_Setting::get_instance();

\Variety\Plugins\Variety_Print_Issue\Print_Issue_Alert::get_instance();

\Variety\Plugins\Variety_Print_Issue\Print_Issue::get_instance();
