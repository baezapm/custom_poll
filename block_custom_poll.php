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

use core_external\util as external_util;
//require_once ('vote_form.php');

/**
 * Form for editing HTML block instances.
 *
 * @package   block_custompoll
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_custom_poll extends block_base {
    public function init() {
        $this->title = get_string('blockname', 'block_custom_poll');
    }

    public function get_content() {
        global $USER, $OUTPUT, $DB, $PAGE;

        $PAGE->requires->css('/blocks/custom_poll/css/main.css');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        // Get the active poll
        $pollData = $DB->get_records_sql(
            "SELECT o.id as optionid, q.id AS question_id, q.question, o.option_name, o.vote_count, q.active
         FROM {custom_poll_questions} q
         LEFT JOIN {custom_poll_options} o ON q.id = o.question_id
         WHERE q.active = 1"
        );

        if ($pollData) {
            $current_key= array_key_first($pollData);
            $question = format_string($pollData[$current_key]->question);
            $question_id = $pollData[$current_key]->question_id;

            $has_voted = $DB->record_exists('custom_poll_votes', ['question_id' => $question_id, 'user_id' => $USER->id]);

            $content = html_writer::start_tag('div', ['class' => 'latest-poll']);
            $content .= html_writer::tag('h2', $question, ['class' => 'poll-question']);

            if (!$has_voted) {
                $url = new moodle_url('/blocks/custom_poll/vote.php');

                $content .= "\n".'<form class="poll-vote" id="poll-vote" method="post" action="'.$url.'">';
                $content .= '<input type="hidden" name="question_id" value="'.$question_id.'" />';
                $content .= '<input type="hidden" name="user_id" value="'.$USER->id.'" />';
                $content .= '<div class="form-group">';
                foreach ($pollData as $poll_option) {
                    $content .= '<label><input type="checkbox" name="options[]" value="'.$poll_option->optionid.'"/> '.$poll_option->option_name.'</label></br>';
                }

                $content .= '<div class="form-group">';
                $content .= '<input type="submit" id="custom-poll-vote" class="btn btn-primary btn-block" value="'.get_string('vote', 'block_custom_poll').'" />';
                $content .= '</div>';
                $content .= "</form>\n";
                $content .= '</div>';

            } else {
                // Mostrar los resultados de la encuesta
                $content .= html_writer::start_tag('ul', ['class' => 'poll-results']);

                foreach ($pollData as $poll_option) {
                    $content .= html_writer::start_tag('div');
                    $content .= html_writer::tag('span', $poll_option->option_name, ['class' => 'poll-option']);
                    $content .= html_writer::tag('span', $poll_option->vote_count. ' votes', ['class' => 'poll-votes']);
                    $content .= html_writer::end_tag('div');
                }

                $content .= html_writer::end_tag('ul');
            }

            $content .= html_writer::end_tag('div');
        }else{
            $content .= html_writer::start_tag('div');
            $content .= html_writer::tag('span', get_string('no_polls_active', 'block_custom_poll'), ['class' => 'no-poll-active']);
            $content .= html_writer::end_tag('div');
        }

        // Display the poll creation link (if the user has the capability)

        if (has_capability('block/custom_poll:createpoll', context_system::instance())) {
            $content .= html_writer::start_tag('div', ['class' => 'poll-actions']);
            $create_poll_link = new moodle_url('/blocks/custom_poll/create_poll.php');
            $content .= html_writer::tag('a', get_string('create_poll', 'block_custom_poll'),  [ 'href' => $create_poll_link, 'class' => 'btn btn-success poll-btn'] );

            $poll_list_link = new moodle_url('/blocks/custom_poll/list.php');
            $content .= html_writer::tag('a', get_string('poll_list', 'block_custom_poll'),  [ 'href' => $poll_list_link, 'class' => 'btn btn-primary poll-btn'] );
            $content .= html_writer::end_tag('div');
        }


        $this->content->text = $content;

        return $this->content;
    }
}
