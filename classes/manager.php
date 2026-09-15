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
 * manager.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_exiticket;

/**
 * Class manager.
 */
class manager {
    /**
     * Returns the current user's response.
     *
     * @param int $exiticketid Activity instance id.
     * @param int $userid User id.
     * @return \stdClass|null
     */
    public static function get_response(int $exiticketid, int $userid): ?\stdClass {
        global $DB;

        $response = $DB->get_record("exiticket_responses", [
            "exiticketid" => $exiticketid,
            "userid" => $userid,
        ]);

        return $response ?: null;
    }

    /**
     * Creates or updates a response.
     *
     * @param \stdClass $exiticket Activity instance.
     * @param int $userid User id.
     * @param \stdClass $data Submitted response data.
     * @return \stdClass Saved response.
     */
    public static function save_response(\stdClass $exiticket, int $userid, \stdClass $data): \stdClass {
        global $DB;

        $now = time();
        $record = self::get_response((int)$exiticket->id, $userid);

        if (!$record) {
            $record = (object)[
                "exiticketid" => $exiticket->id,
                "userid" => $userid,
                "timecreated" => $now,
            ];
        }

        $record->answer1 = trim((string)$data->answer1);
        $record->answer2 = trim((string)$data->answer2);
        $record->answer3 = (int)$exiticket->questioncount === 3 ? trim((string)($data->answer3 ?? "")) : "";
        $record->timemodified = $now;

        if (!empty($record->id)) {
            $DB->update_record("exiticket_responses", $record);
        } else {
            $record->id = $DB->insert_record("exiticket_responses", $record);
        }

        return $record;
    }

    /**
     * Returns whether a response can currently be submitted or edited.
     *
     * @param \stdClass $exiticket Activity instance.
     * @param \stdClass|null $response Existing response.
     * @return bool
     */
    public static function can_respond(\stdClass $exiticket, ?\stdClass $response): bool {
        if (!empty($exiticket->timeclose) && time() > (int)$exiticket->timeclose) {
            return false;
        }

        if ($response && empty($exiticket->allowedit)) {
            return false;
        }

        return true;
    }

    /**
     * Returns report data for an activity and optional group.
     *
     * @param \stdClass $exiticket Activity instance.
     * @param \context_module $context Module context.
     * @param int $groupid Group id, or zero for all participants.
     * @return array
     */
    public static function get_report_data(\stdClass $exiticket, \context_module $context, int $groupid = 0): array {
        global $DB;

        $users = get_enrolled_users(
            $context,
            "mod/exiticket:submit",
            $groupid,
            "u.id,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.email",
            "u.lastname ASC, u.firstname ASC"
        );
        $userids = array_keys($users);

        $responses = [];
        if ($userids) {
            [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, "uid");
            $params["exiticketid"] = $exiticket->id;
            $responses = $DB->get_records_select(
                "exiticket_responses",
                "exiticketid = :exiticketid AND userid {$insql}",
                $params,
                "timemodified DESC"
            );
        }

        $responsesbyuser = [];
        foreach ($responses as $response) {
            $responsesbyuser[(int)$response->userid] = $response;
        }

        $questions = [
            ["key" => "answer1", "text" => $exiticket->question1],
            ["key" => "answer2", "text" => $exiticket->question2],
        ];
        if ((int)$exiticket->questioncount === 3) {
            $questions[] = ["key" => "answer3", "text" => $exiticket->question3];
        }

        return [
            "users" => $users,
            "responses" => $responses,
            "responsesbyuser" => $responsesbyuser,
            "questions" => $questions,
            "eligiblecount" => count($users),
            "responsecount" => count($responses),
            "pendingcount" => max(0, count($users) - count($responses)),
        ];
    }
}
