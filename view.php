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
 * view.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("exiticket", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$exiticket = $DB->get_record("exiticket", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/exiticket:view", $context);

$PAGE->set_url(new moodle_url("/mod/exiticket/view.php", ["id" => $cm->id]));
$PAGE->set_title(format_string($exiticket->name));
$PAGE->set_heading(format_string($course->fullname));

$event = \mod_exiticket\event\course_module_viewed::create([
    "objectid" => $exiticket->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("course_modules", $cm);
$event->add_record_snapshot("exiticket", $exiticket);
$event->trigger();

$canrespond = has_capability("mod/exiticket:submit", $context);
$canviewreport = has_capability("mod/exiticket:viewreport", $context);
$response = $canrespond ? \mod_exiticket\manager::get_response((int)$exiticket->id, (int)$USER->id) : null;
$form = null;

if ($canrespond && \mod_exiticket\manager::can_respond($exiticket, $response)) {
    $form = new \mod_exiticket\form\response_form(null, [
        "exiticket" => $exiticket,
        "cmid" => $cm->id,
    ]);

    if ($response) {
        $form->set_data((object)[
            "id" => $cm->id,
            "answer1" => $response->answer1,
            "answer2" => $response->answer2,
            "answer3" => $response->answer3,
        ]);
    }

    if ($data = $form->get_data()) {
        $saved = \mod_exiticket\manager::save_response($exiticket, (int)$USER->id, $data);
        \mod_exiticket\event\response_submitted::create([
            "objectid" => $saved->id,
            "context" => $context,
            "other" => ["exiticketid" => $exiticket->id],
        ])->trigger();
        redirect(
            new moodle_url("/mod/exiticket/view.php", ["id" => $cm->id]),
            get_string("responsesaved", "mod_exiticket"),
            null,
            notification::NOTIFY_SUCCESS
        );
    }
}

$viewdata = [
    "intro" => format_module_intro("exiticket", $exiticket, $cm->id, false),
    "hasintro" => trim((string)$exiticket->intro) !== "",
    "closed" => !empty($exiticket->timeclose) && time() > (int)$exiticket->timeclose,
    "closelabel" => !empty($exiticket->timeclose) ? get_string("closeson", "mod_exiticket", userdate($exiticket->timeclose)) : "",
    "canviewreport" => $canviewreport,
    "reporturl" => new moodle_url("/mod/exiticket/report.php", ["id" => $cm->id]),
    "hasresponse" => $response,
    "cannotedit" => $response && empty($exiticket->allowedit),
    "questions" => [],
];

if ($response) {
    $viewdata["questions"][] = [
        "question" => format_string($exiticket->question1),
        "answer" => format_text($response->answer1, FORMAT_PLAIN),
    ];
    $viewdata["questions"][] = [
        "question" => format_string($exiticket->question2),
        "answer" => format_text($response->answer2, FORMAT_PLAIN),
    ];
    if ((int)$exiticket->questioncount === 3) {
        $viewdata["questions"][] = [
            "question" => format_string($exiticket->question3),
            "answer" => format_text($response->answer3, FORMAT_PLAIN),
        ];
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($exiticket->name));
echo $OUTPUT->render_from_template("mod_exiticket/student_view", $viewdata);

if ($form) {
    $form->display();
} else if ($canrespond && !$response && !empty($exiticket->timeclose) && time() > (int)$exiticket->timeclose) {
    echo $OUTPUT->notification(get_string("closedwithoutresponse", "mod_exiticket"), notification::NOTIFY_WARNING);
}

echo $OUTPUT->footer();
