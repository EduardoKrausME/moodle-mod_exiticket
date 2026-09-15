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
 * backup_exiticket_stepslib.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class backup_exiticket_activity_structure_step.
 */
class backup_exiticket_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the activity backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $exiticket = new backup_nested_element("exiticket", ["id"], [
            "name", "intro", "introformat", "questioncount", "question1", "question2", "question3",
            "allowedit", "timeclose", "timecreated", "timemodified",
        ]);
        $responses = new backup_nested_element("responses");
        $response = new backup_nested_element("response", ["id"], [
            "userid", "answer1", "answer2", "answer3", "timecreated", "timemodified",
        ]);

        $exiticket->add_child($responses);
        $responses->add_child($response);
        $exiticket->set_source_table("exiticket", ["id" => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $response->set_source_table("exiticket_responses", ["exiticketid" => backup::VAR_PARENTID]);
        }

        $response->annotate_ids("user", "userid");
        $exiticket->annotate_files("mod_exiticket", "intro", null);

        return $this->prepare_activity_structure($exiticket);
    }
}
