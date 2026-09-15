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
 * lib.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Declares the Moodle features supported by the activity.
 *
 * @param string $feature Feature constant.
 * @return bool|string|null
 */
function exiticket_supports(string $feature) {
    if (defined("FEATURE_MOD_PURPOSE") && $feature === FEATURE_MOD_PURPOSE) {
        if (defined("MOD_PURPOSE_ASSESSMENT")) {
            return MOD_PURPOSE_ASSESSMENT;
        }
        if (defined("MOD_PURPOSE_OTHER")) {
            return MOD_PURPOSE_OTHER;
        }
        return MOD_ARCHETYPE_OTHER;
    }
    if (defined("FEATURE_COMPLETION") && $feature === FEATURE_COMPLETION) {
        return true;
    }

    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Creates an activity instance.
 *
 * @param stdClass $data Form data.
 * @param mod_exiticket_mod_form|null $mform Form instance.
 * @return int New instance id.
 */
function exiticket_add_instance(stdClass $data, mod_exiticket_mod_form $mform = null): int {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    return $DB->insert_record("exiticket", $data);
}

/**
 * Updates an activity instance.
 *
 * @param stdClass $data Form data.
 * @param mod_exiticket_mod_form|null $mform Form instance.
 * @return bool
 */
function exiticket_update_instance(stdClass $data, mod_exiticket_mod_form $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    return $DB->update_record("exiticket", $data);
}

/**
 * Deletes an activity instance and its responses.
 *
 * @param int $id Instance id.
 * @return bool
 */
function exiticket_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->record_exists("exiticket", ["id" => $id])) {
        return false;
    }

    $DB->delete_records("exiticket_responses", ["exiticketid" => $id]);
    $DB->delete_records("exiticket", ["id" => $id]);
    return true;
}
