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
 * restore_exiticket_stepslib.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_exiticket_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the activity restore structure.
     *
     * @return array
     */
    protected function define_structure(): array {
        $paths = [new restore_path_element("exiticket", "/activity/exiticket")];
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("exiticket_response", "/activity/exiticket/responses/response");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the activity instance.
     *
     * @param array $data Restored data.
     */
    protected function process_exiticket($data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (!empty($data->timeclose)) {
            $data->timeclose = $this->apply_date_offset($data->timeclose);
        }

        $newitemid = $DB->insert_record("exiticket", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restores one student response.
     *
     * @param array $data Restored data.
     */
    protected function process_exiticket_response($data): void {
        global $DB;

        $data = (object)$data;
        $data->exiticketid = $this->get_new_parentid("exiticket");
        $data->userid = $this->get_mappingid("user", $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if (!$data->userid) {
            return;
        }

        $newitemid = $DB->insert_record("exiticket_responses", $data);
        $this->set_mapping("exiticket_response", $data->id, $newitemid);
    }

    /**
     * Restores related files.
     */
    protected function after_execute(): void {
        $this->add_related_files("mod_exiticket", "intro", null);
    }
}
