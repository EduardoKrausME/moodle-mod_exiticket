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
 * response_submitted.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_exiticket\event;

/**
 * Class response_submitted.
 */
class response_submitted extends \core\event\base {
    /**
     * Initializes event properties.
     */
    protected function init(): void {
        $this->data["crud"] = "c";
        $this->data["edulevel"] = self::LEVEL_PARTICIPATING;
        $this->data["objecttable"] = "exiticket_responses";
    }

    /**
     * Returns the event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string("eventresponsesubmitted", "mod_exiticket");
    }

    /**
     * Returns the event description.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' submitted exit ticket response '{$this->objectid}' " .
            "for activity '{$this->contextinstanceid}'.";
    }

    /**
     * Returns the activity URL.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url {
        return new \moodle_url("/mod/exiticket/view.php", ["id" => $this->contextinstanceid]);
    }

    /**
     * Returns object id mapping for restore.
     *
     * @return array
     */
    public static function get_objectid_mapping(): array {
        return ["db" => "exiticket_responses", "restore" => "exiticket_response"];
    }

    /**
     * Returns mappings for ids stored in other.
     *
     * @return array
     */
    public static function get_other_mapping(): array {
        return ["exiticketid" => ["db" => "exiticket", "restore" => "exiticket"]];
    }
}
