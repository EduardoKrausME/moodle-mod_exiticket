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
 * Generator for Exit ticket activity tests.
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides default values for generating Exit ticket activities in PHPUnit.
 */
class mod_exiticket_generator extends testing_module_generator {
    /**
     * Creates an Exit ticket instance.
     *
     * @param array|stdClass|null $record Module fields.
     * @param array|null $options Module options.
     * @return stdClass Created activity.
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object)(array)$record;
        $record->questioncount = $record->questioncount ?? 3;
        $record->question1 = $record->question1 ?? 'What did you learn?';
        $record->question2 = $record->question2 ?? 'What is still unclear?';
        $record->question3 = $record->question3 ?? 'What should we revisit?';
        $record->timeclose = $record->timeclose ?? 0;
        $record->allowedit = $record->allowedit ?? 1;

        return parent::create_instance($record, $options);
    }
}
