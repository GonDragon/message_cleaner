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

namespace local_message_cleaner\task;

/**
 * Message Cleaner Task.
 */
class message_cleaner extends \core\task\scheduled_task
{

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('message_cleaner', 'local_message_cleaner');
    }

    /**
     * Execute the task.
     */
    public function execute()
    {
        global $DB;

        // Calculate the timestamp for 6 months ago.
        $six_months_ago = time() - (6 * 30 * 24 * 60 * 60);

        // Start a transaction to ensure data consistency.
        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete records older than 6 months from the 'message_read' table.
            $DB->delete_records_select('message_read', 'timecreated < :six_months_ago', array('six_months_ago' => $six_months_ago));

            // Delete records older than 6 months from the 'messages' table.
            $DB->delete_records_select('messages', 'timecreated < :six_months_ago', array('six_months_ago' => $six_months_ago));

            // Commit the transaction.
            $transaction->allow_commit();
        } catch (Exception $e) {
            // Rollback the transaction if there's an error.
            $transaction->rollback($e);
        }
        // global $DB, $CFG;

        // // Calculate the timestamp for messages older than 6 months
        // $sixMonthsAgo = time() - (6 * 30 * 24 * 60 * 60);

        // // Retrieve private messages older than 6 months
        // $sql = "SELECT id, useridfrom
        //     FROM {messages}
        //     WHERE timecreated < :sixmonthsago";
        // $params = array('sixmonthsago' => $sixMonthsAgo);
        // $oldMessages = $DB->get_records_sql($sql, $params);

        // // Load messaging API functions
        // require_once($CFG->dirroot . '/message/lib.php');

        // // Delete each old private message
        // foreach ($oldMessages as $oldMessage) {
        //     mtrace("Deleting message with ID: " . $oldMessage->id . " From userid: " . $oldMessage->useridfrom); // Debug: print the message ID
        //     \core_message\api::delete_message($oldMessage->useridfrom, $oldMessage->id);
        //     \core_message\api::delete_message_for_all_users($oldMessage->id);
        // }

        // // Log the task completion
        // mtrace("Deleted " . count($oldMessages) . " private messages older than 6 months");
    }
}
