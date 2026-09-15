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
 * index.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once($CFG->libdir . "/tablelib.php");

$id = required_param("id", PARAM_INT);
$course = get_course($id);
require_login($course);
$PAGE->set_url(new moodle_url("/mod/exiticket/index.php", ["id" => $course->id]));
$PAGE->set_title(get_string("modulenameplural", "mod_exiticket"));
$PAGE->set_heading(format_string($course->fullname));

$instances = get_all_instances_in_course("exiticket", $course);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("modulenameplural", "mod_exiticket"));

if (!$instances) {
    echo $OUTPUT->notification(get_string("noexitickets", "mod_exiticket"), \core\output\notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    exit;
}

$table = new flexible_table("mod-exiticket-course-overview");
$table->define_columns(["name", "status", "responses"]);
$table->define_headers([
    get_string("activity"),
    get_string("status"),
    get_string("responses", "mod_exiticket"),
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute("class", "generaltable generalbox");
$table->setup();

foreach ($instances as $instance) {
    $cm = get_coursemodule_from_instance("exiticket", $instance->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    if (!has_capability("mod/exiticket:view", $context)) {
        continue;
    }

    $name = html_writer::link(new moodle_url("/mod/exiticket/view.php", ["id" => $cm->id]), format_string($instance->name));
    $status = !empty($instance->timeclose) && time() > (int)$instance->timeclose
        ? get_string("closed", "mod_exiticket")
        : get_string("open", "mod_exiticket");
    $responses = "—";

    if (has_capability("mod/exiticket:viewreport", $context)) {
        $reporturl = new moodle_url("/mod/exiticket/report.php", ["id" => $cm->id]);
        $restrictedgroups = groups_get_activity_groupmode($cm) == SEPARATEGROUPS
            && !has_capability("moodle/site:accessallgroups", $context);

        if ($restrictedgroups) {
            $responses = html_writer::link($reporturl, get_string("viewreport", "mod_exiticket"));
        } else {
            $report = \mod_exiticket\manager::get_report_data($instance, $context);
            $summary = get_string("responsessummary", "mod_exiticket", (object)[
                "responses" => $report["responsecount"],
                "eligible" => $report["eligiblecount"],
            ]);
            $responses = html_writer::link($reporturl, $summary);
        }
    }

    $table->add_data([$name, $status, $responses]);
}

$table->finish_output();
echo $OUTPUT->footer();
