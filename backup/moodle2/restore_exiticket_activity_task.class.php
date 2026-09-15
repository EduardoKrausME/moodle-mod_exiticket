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
 * restore_exiticket_activity_task.class.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/exiticket/backup/moodle2/restore_exiticket_stepslib.php");

/**
 * Class restore_exiticket_activity_task.
 */
class restore_exiticket_activity_task extends restore_activity_task {
    /**
     * Defines activity-specific restore settings.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines activity-specific restore steps.
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_exiticket_activity_structure_step("exiticket_structure", "exiticket.xml"));
    }

    /**
     * Defines content link decoding rules.
     *
     * @return array
     */
    public static function define_decode_contents(): array {
        return [new restore_decode_content("exiticket", ["intro"], "exiticket")];
    }

    /**
     * Defines activity link decoding rules.
     *
     * @return array
     */
    public static function define_decode_rules(): array {
        return [
            new restore_decode_rule("EXITICKETVIEWBYID", "/mod/exiticket/view.php?id=$1", "course_module"),
            new restore_decode_rule("EXITICKETINDEX", "/mod/exiticket/index.php?id=$1", "course"),
        ];
    }

    /**
     * Defines restore log rules.
     *
     * @return array
     */
    public static function define_restore_log_rules(): array {
        return [];
    }

    /**
     * Defines course restore log rules.
     *
     * @return array
     */
    public static function define_restore_log_rules_for_course(): array {
        return [];
    }
}
