<?php
require_once('../../config.php');
require_login();


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather form data
    $question = required_param('question', PARAM_TEXT);
    $options = required_param_array('options', PARAM_TEXT);
    $active = optional_param('active', 0, PARAM_TEXT);

    // Validate and process the data
    if (empty($question) || empty($options)) {
        $error_message = get_string('invaliddata', 'block_custom_poll');
    } else {

        // Check if another poll is active
        $pollData = null;
        if($active){
            $pollData = $DB->get_records_sql(
                "SELECT *
         FROM {custom_poll_questions} WHERE active = 1"
            );
        }

        if(!$pollData){
            // Create a new poll question in the database
            $pollquestion = new stdClass();
            $pollquestion->question = $question;
            $pollquestion->active = (@$active) ? 1 : 0;

            // Insert the question and get the new ID
            $pollquestion->id = $DB->insert_record('custom_poll_questions', $pollquestion);

            if ($pollquestion->id) {
                // Insert the poll options
                foreach ($options as $option) {
                    if($option != ''){
                        $pollresponse = new stdClass();
                        $pollresponse->question_id = $pollquestion->id;
                        $pollresponse->option_name = $option;

                        $DB->insert_record( 'custom_poll_options', $pollresponse);
                    }
                }

                $success = get_string('creation_success', 'block_custom_poll');;
                redirect(new moodle_url('/blocks/custom_poll/create_poll.php', ['success' => $success]));

            } else {
                $error_message = get_string('insertionerror', 'block_custom_poll');
                redirect(new moodle_url('/blocks/custom_poll/create_poll.php', ['error' => $error_message]));
            }
        }else{
            $error_message = get_string('another_active', 'block_custom_poll');
            redirect(new moodle_url('/blocks/custom_poll/create_poll.php', ['error' => $error_message]));
        }

    }
} else {
    // Display the poll creation form
    $PAGE->set_url(new moodle_url('/blocks/custom_poll/create_poll.php'));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('create_poll', 'block_custom_poll'));
    $PAGE->set_heading(get_string('create_poll', 'block_custom_poll'));
    $error_message = ($_GET['error']) ?: '';
    $success = ($_GET['success']) ?: '';

    echo $OUTPUT->header();

    echo '<row><div class="col-md-6">';

    if (!empty($error_message)) {
        // Display the error message
        echo $OUTPUT->notification($error_message, 'error');
    }else if($success){
        echo $OUTPUT->notification(get_string('pollcreated', 'block_custom_poll'), 'success');
    }

    echo '<div class="form-group">';
    echo '<form method="post" action="create_poll.php">';
    echo '<div class="form-group">';
    echo '<label for="question">' . get_string('question', 'block_custom_poll') . ':</label>';
    echo '<input class="form-control" type="text" id="question" name="question" required placeholder="'. get_string('enter_question', 'block_custom_poll').'">';
    echo '</div>';

    echo '<div class="form-group">';
    for ($i = 1; $i <= 4; $i++) {
        echo '<label for="option' . $i . '">Option ' . $i . ':</label>';
        echo '<input class="form-control" type="text" id="option' . $i . '" name="options[]" placeholder="'. get_string('enter_option', 'block_custom_poll').'">';
    }
    echo '</div>';

    echo '<div class="form-check">';
    echo '<label class="form-check-label"><input class="form-check-inpu" type="checkbox" name="active"> '. get_string('active', 'block_custom_poll').'</label>';
    echo '</div>';

    echo '<button type="submit"  class="btn btn-primary create-form-btn" >' . get_string('submit', 'block_custom_poll') . '</button>';
    echo '</form>';
    echo '</div></row>';

    echo $OUTPUT->footer();
}