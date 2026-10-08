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
 * Tests for Exit ticket reports and group visibility.
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_exiticket;

/**
 * Regression tests for the report's separate-groups access control.
 *
 * @covers \\mod_exiticket\\manager
 */
final class manager_test extends \advanced_testcase {
    /**
     * Restricted teachers must see only responses from their own selected group.
     */
    public function test_report_respects_separate_groups(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['groupmode' => SEPARATEGROUPS]);
        $activity = $generator->create_module('exiticket', [
            'course' => $course->id,
            'name' => 'Class reflection',
            'questioncount' => 2,
            'question1' => 'What did you learn?',
            'question2' => 'What is still unclear?',
        ], ['groupmode' => SEPARATEGROUPS]);

        $cm = get_coursemodule_from_instance('exiticket', $activity->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $teacher = $generator->create_user();
        $studenta = $generator->create_user();
        $studentb = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, 'teacher');
        $generator->enrol_user($studenta->id, $course->id, 'student');
        $generator->enrol_user($studentb->id, $course->id, 'student');

        $groupa = $generator->create_group(['courseid' => $course->id]);
        $groupb = $generator->create_group(['courseid' => $course->id]);
        $generator->create_group_member(['groupid' => $groupa->id, 'userid' => $studenta->id]);
        $generator->create_group_member(['groupid' => $groupb->id, 'userid' => $studentb->id]);

        manager::save_response($activity, $studenta->id, (object)[
            'answer1' => 'Group A answer',
            'answer2' => 'Group A question',
        ]);
        manager::save_response($activity, $studentb->id, (object)[
            'answer1' => 'Group B answer',
            'answer2' => 'Group B question',
        ]);

        $this->setUser($teacher);
        $this->assertFalse(has_capability('moodle/site:accessallgroups', $context));

        // A teacher without group membership may not receive the entire class when group is 0.
        $report = manager::get_report_data($activity, $context, 0);
        $this->assertSame(0, $report['eligiblecount']);
        $this->assertSame(0, $report['responsecount']);

        $generator->create_group_member(['groupid' => $groupa->id, 'userid' => $teacher->id]);
        $report = manager::get_report_data($activity, $context, (int)$groupa->id);
        $this->assertSame(1, $report['eligiblecount']);
        $this->assertSame(1, $report['responsecount']);
        $this->assertArrayHasKey($studenta->id, $report['users']);
        $this->assertArrayNotHasKey($studentb->id, $report['users']);

        // Forging a group selector must not expose responses from another group.
        $report = manager::get_report_data($activity, $context, (int)$groupb->id);
        $this->assertSame(0, $report['eligiblecount']);
        $this->assertSame(0, $report['responsecount']);
    }
}
