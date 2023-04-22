<?php
defined('MOODLE_INTERNAL') || die();

use local_message_cleaner\task\message_cleaner;

class local_message_cleaner_message_cleaner_testcase extends advanced_testcase {

    public function test_execute() {
        global $DB;

        // Set up the test environment
        $this->resetAfterTest(true);

        // Create two test users
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Create some private messages between the users
        $timeNow = time();
        $timeSevenMonthsAgo = $timeNow - (7 * 30 * 24 * 60 * 60);
        $timeFiveMonthsAgo = $timeNow - (5 * 30 * 24 * 60 * 60);

        // Old message (older than 6 months)
        $this->create_private_message($user1->id, $user2->id, 'Old message', $timeSevenMonthsAgo);

        // Recent message (within the last 6 months)
        $this->create_private_message($user1->id, $user2->id, 'Recent message', $timeFiveMonthsAgo);

        // Get the initial count of private messages
        $initialMessageCount = $DB->count_records('messages');

        // Instantiate and execute the task
        $task = new message_cleaner();
        $task->execute();

        // Check if only the old message has been deleted
        $currentMessageCount = $DB->count_records('messages');
        $this->assertEquals($initialMessageCount - 1, $currentMessageCount);

        // Check if the recent message still exists
        $recentMessageExists = $DB->record_exists('messages', ['useridfrom' => $user1->id, 'useridto' => $user2->id, 'smallmessage' => 'Recent message']);
        $this->assertTrue($recentMessageExists);
    }

    /**
     * Helper function to create private messages.
     */
    private function create_private_message($fromuserid, $touserid, $message, $timecreated) {
        global $DB;

        $record = new stdClass();
        $record->useridfrom = $fromuserid;
        $record->useridto = $touserid;
        $record->smallmessage = $message;
        $record->timecreated = $timecreated;
        $record->notification = 0;

        $DB->insert_record('messages', $record);
    }
}
