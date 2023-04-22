<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provides meta-data about the plugin.
 *
 * @package     local_message_cleaner
 * @author      2023 Gonzalo Romero <https://github.com/GonDragon>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function delete_old_messages()
{
    global $DB;

    // Calculate the timestamp for messages older than 6 months
    $sixMonthsAgo = time() - (6 * 30 * 24 * 60 * 60);

    // Retrieve private messages older than 6 months
    $sql = "SELECT id
            FROM {messages}
            WHERE timecreated < :sixmonthsago";
    $params = array('sixmonthsago' => $sixMonthsAgo);
    $oldMessages = $DB->get_records_sql($sql, $params);

    // Load messaging API functions
    require_once($CFG->dirroot . '/message/lib.php');

    // Delete each old private message
    foreach ($oldMessages as $oldMessage) {
        message_delete_message($oldMessage, true); // The second parameter is "fulldelete"
    }

    // Log the task completion
    $messageAmount = count($oldMessages);
    mtrace(get_string('deleted_messages', 'local_message_cleaner'));
}
