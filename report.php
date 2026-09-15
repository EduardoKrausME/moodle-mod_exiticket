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
 * report.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("exiticket", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$exiticket = $DB->get_record("exiticket", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/exiticket:viewreport", $context);

$PAGE->set_url(new moodle_url("/mod/exiticket/report.php", ["id" => $cm->id]));
$PAGE->set_title(get_string("report", "mod_exiticket"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string("report", "mod_exiticket"));

$groupid = groups_get_activity_group($cm, true);
$report = \mod_exiticket\manager::get_report_data($exiticket, $context, $groupid);
$rate = $report["eligiblecount"] > 0 ? round(($report["responsecount"] / $report["eligiblecount"]) * 100) : 0;

$template = [
    "responsecount" => $report["responsecount"],
    "eligiblecount" => $report["eligiblecount"],
    "pendingcount" => $report["pendingcount"],
    "rate" => $rate,
    "hasresponses" => $report["responsecount"] > 0,
    "questions" => [],
];

foreach ($report["questions"] as $question) {
    $item = [
        "question" => format_string($question["text"]),
        "responses" => [],
    ];
    foreach ($report["responses"] as $response) {
        $user = $report["users"][$response->userid] ?? null;
        if (!$user) {
            continue;
        }
        $item["responses"][] = [
            "fullname" => fullname($user),
            "profileurl" => (new moodle_url("/user/view.php", ["id" => $user->id, "course" => $course->id]))->out(false),
            "answer" => format_text($response->{$question["key"]}, FORMAT_PLAIN),
            "time" => userdate($response->timemodified),
        ];
    }
    $template["questions"][] = $item;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("reportfor", "mod_exiticket", format_string($exiticket->name)));
groups_print_activity_menu($cm, $PAGE->url);
echo $OUTPUT->render_from_template("mod_exiticket/report", $template);
echo $OUTPUT->footer();
