<?php
require_once('../../config.php');
require_login();

// Display the poll creation form
$PAGE->set_url(new moodle_url('/blocks/custom_poll/list.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('list', 'block_custom_poll'));
$PAGE->set_heading(get_string('list', 'block_custom_poll'));

echo $OUTPUT->header();


$pollData = $DB->get_records_sql(
    "SELECT *
         FROM {custom_poll_questions}"
);


$content = '<div class="container"><div class="row"><div class="col-12">';
$content .= '<table class="table table-bordered"><thead><tr><th scope="col">ID</th><th scope="col">Question</th><th scope="col">Active</th><th scope="col">Actions</th></tr></thead>';
$content .= '<tbody>';

foreach ($pollData as $poll){
    $delete_url = new moodle_url('/blocks/custom_poll/list.php', ['id' => $poll->id, 'action' => 'delete']);
    $active_url = new moodle_url('/blocks/custom_poll/list.php', ['id' => $poll->id, 'action' => 'active']);
    $content.= '<tr>';
    $content.= '<th scope="row">'.$poll->id.'</th>';
    $content.= '<td>'.$poll->question.'</td>';
    $content.= '<td>'.$poll->active.'</td>';
    $content.= '<td>';
    if($poll->active == 0){
        $content.= '<a  href="'.$active_url.'"class="btn btn-success" data-toggle="tooltip" data-placement="top" title="'.get_string('make_active', 'block_custom_poll').'" style="margin-right: 10px;"><i class="fas fa-check"></i>';
    }
    $content.= '<a href="'.$delete_url.'" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="'.get_string('delete', 'block_custom_poll').'"><i class="far fa-trash-alt"></i>';
    $content.= '</td>';
}

$content .= '</tbody></table></div></div></div>';

echo $content;


if($_GET){
    $question_id = $_GET['id'];
    $questionData = $DB->get_record('custom_poll_questions', ['id' => $question_id]);

    switch ($_GET['action']){
        case 'active':
            $currentActive = $DB->get_record('custom_poll_questions', ['active' => 1]);
            if($currentActive){
                $currentActive->active = 0;
                $DB->update_record('custom_poll_questions', $currentActive);
            }
            $questionData->active = 1;
            $DB->update_record('custom_poll_questions', $questionData);
            break;
        case 'delete':

            $DB->delete_records('custom_poll_votes', ['id' => $question_id]);
            $DB->delete_records('custom_poll_options', ['id' => $question_id]);
            $DB->delete_records('custom_poll_questions', ['id' => $question_id]);
            break;
    }

    redirect(new moodle_url('/blocks/custom_poll/list.php'));

}

echo $OUTPUT->footer();

