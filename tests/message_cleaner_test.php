<?php
defined('MOODLE_INTERNAL') || die();

use local_message_cleaner\task\message_cleaner;

class message_cleaner_test extends advanced_testcase
{

    public function test_execute()
    {
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

        $this->print_messages();

        // Old message (older than 6 months)
        $this->create_private_message($user1->id, $user2->id, 'Old message', $timeSevenMonthsAgo);

        // Recent message (within the last 6 months)
        $this->create_private_message($user1->id, $user2->id, 'Recent message', $timeFiveMonthsAgo);

        $this->print_messages();

        // Get the initial count of private messages
        $initialMessageCount = $DB->count_records('messages');

        // Instantiate and execute the task
        $task = new message_cleaner();
        $task->execute();

        $this->print_messages();

        // Check if only the old message has been deleted
        $currentMessageCount = $DB->count_records('messages');
        $this->assertEquals($initialMessageCount - 1, $currentMessageCount, "Unexpected number of messages remaining.\n");


        // Check if the recent message still exists
        $recentMessageExists = $DB->record_exists('messages', ['useridfrom' => $user1->id, 'useridto' => $user2->id, 'smallmessage' => 'Recent message']);
        $this->assertTrue($recentMessageExists);
    }

    /**
     * Helper function to create private messages.
     */
    private function create_private_message($fromuserid, $touserid, $message, $timecreated)
    {
        global $DB;

        $record = new stdClass();
        $record->useridfrom = $fromuserid;
        $record->useridto = $touserid;
        $record->smallmessage = $message;
        $record->timecreated = $timecreated;
        $record->notification = 0;
        $record->conversationid = rand(1, 1000); // Add this line to set a random conversation ID

        $DB->insert_record('messages', $record);
    }

    private function print_messages()
    {
        global $DB;

        $messages = $DB->get_records('messages');

        if (empty($messages)) {
            echo "No messages found.\n";
        } else {
            foreach ($messages as $message) {
                echo "Message ID: {$message->id}, From: {$message->useridfrom}, Text: '{$message->smallmessage}', Time: {$message->timecreated}\n";
            }
        }
    }
}
