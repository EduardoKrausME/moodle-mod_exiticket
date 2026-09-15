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
 * provider.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_exiticket\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes personal data stored by the plugin.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("exiticket_responses", [
            "exiticketid" => "privacy:metadata:exiticket_responses:exiticketid",
            "userid" => "privacy:metadata:exiticket_responses:userid",
            "answer1" => "privacy:metadata:exiticket_responses:answer1",
            "answer2" => "privacy:metadata:exiticket_responses:answer2",
            "answer3" => "privacy:metadata:exiticket_responses:answer3",
            "timecreated" => "privacy:metadata:exiticket_responses:timecreated",
            "timemodified" => "privacy:metadata:exiticket_responses:timemodified",
        ], "privacy:metadata:exiticket_responses");
        return $collection;
    }

    /**
     * Gets contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {exiticket} e ON e.id = cm.instance
                  JOIN {exiticket_responses} r ON r.exiticketid = e.id
                 WHERE r.userid = :userid";
        $params = [
            "contextlevel" => CONTEXT_MODULE,
            "modname" => "exiticket",
            "userid" => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Exports personal data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            $cm = get_coursemodule_from_id("exiticket", $context->instanceid, 0, false, MUST_EXIST);
            $exiticket = $DB->get_record("exiticket", ["id" => $cm->instance], "*", MUST_EXIST);
            $response = $DB->get_record("exiticket_responses", [
                "exiticketid" => $exiticket->id,
                "userid" => $contextlist->get_user()->id,
            ]);

            if (!$response) {
                continue;
            }

            $contextdata = helper::get_context_data($context, $contextlist->get_user());
            helper::export_context_files($context, $contextlist->get_user());
            writer::with_context($context)->export_data([], $contextdata);

            $data = (object)[
                "question1" => $exiticket->question1,
                "answer1" => $response->answer1,
                "question2" => $exiticket->question2,
                "answer2" => $response->answer2,
                "timecreated" => transform::datetime($response->timecreated),
                "timemodified" => transform::datetime($response->timemodified),
            ];
            if ((int)$exiticket->questioncount === 3) {
                $data->question3 = $exiticket->question3;
                $data->answer3 = $response->answer3;
            }

            writer::with_context($context)->export_data([get_string("responses", "mod_exiticket")], $data);
        }
    }

    /**
     * Deletes all user data in a module context.
     *
     * @param \context $context Context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("exiticket", $context->instanceid);
        if ($cm) {
            $DB->delete_records("exiticket_responses", ["exiticketid" => $cm->instance]);
        }
    }

    /**
     * Deletes data for one user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id("exiticket", $context->instanceid);
            if ($cm) {
                $DB->delete_records("exiticket_responses", [
                    "exiticketid" => $cm->instance,
                    "userid" => $contextlist->get_user()->id,
                ]);
            }
        }
    }

    /**
     * Adds users with personal data in a context.
     *
     * @param userlist $userlist User list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        if (!$userlist->get_context() instanceof \context_module) {
            return;
        }

        $sql = "SELECT r.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {exiticket_responses} r ON r.exiticketid = cm.instance
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql("userid", $sql, [
            "modname" => "exiticket",
            "cmid" => $userlist->get_context()->instanceid,
        ]);
    }

    /**
     * Deletes data for approved users in a context.
     *
     * @param approved_userlist $userlist Approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("exiticket", $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, "uid");
        $params["exiticketid"] = $cm->instance;
        $DB->delete_records_select("exiticket_responses", "exiticketid = :exiticketid AND userid {$insql}", $params);
    }
}
