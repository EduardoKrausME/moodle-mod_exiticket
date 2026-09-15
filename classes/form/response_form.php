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
 * response_form.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_exiticket\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * Class response_form.
 */
class response_form extends \moodleform {
    /**
     * Defines the student response form.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $exiticket = $this->_customdata["exiticket"];

        $this->add_question("answer1", $exiticket->question1);
        $this->add_question("answer2", $exiticket->question2);

        if ((int)$exiticket->questioncount === 3) {
            $this->add_question("answer3", $exiticket->question3);
        }

        $mform->addElement("hidden", "id", $this->_customdata["cmid"]);
        $mform->setType("id", PARAM_INT);

        $this->add_action_buttons(false, get_string("saveresponse", "mod_exiticket"));
    }

    /**
     * Adds one required multiline question.
     *
     * @param string $name Field name.
     * @param string $label Question text.
     */
    private function add_question(string $name, string $label): void {
        $mform = $this->_form;
        $mform->addElement("textarea", $name, format_string($label), [
            "rows" => 5,
            "maxlength" => 10000,
            "class" => "w-100",
        ]);
        $mform->setType($name, PARAM_RAW);
        $mform->addRule($name, get_string("required"), "required", null, "client");
        $mform->addRule($name, get_string("maximumchars", "", 10000), "maxlength", 10000, "client");
    }
}
