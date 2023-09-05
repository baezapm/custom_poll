<?php

require_once('../../config.php');
require_login();


$question_id = required_param('question_id', PARAM_INT);
$options = required_param_array('options', PARAM_TEXT);
$user_id = required_param('user_id', PARAM_INT);


foreach ($options as  $option){
    if ($question_id) {

        // Insert vote to custom poll votes table to prevent users to vote twice
        $pollvote = new stdClass();
        $pollvote->question_id = $question_id;
        $pollvote->user_id = $user_id;

        $DB->insert_record( 'custom_poll_votes', $pollvote);

        $optionsToUpdate = [];

        foreach ($options as $option) {

            $optionData = $DB->get_record('custom_poll_options', ['id' => $option]);

            $optionData->vote_count++;
            $DB->update_record('custom_poll_options', $optionData);
        }

        redirect(new moodle_url('/my'));

    } else {

        $error_message = get_string('insertionerror', 'block_custom_poll');
    }
}
