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
 * mod_form.php
 *
 * @package   mod_exiticket
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . "/course/moodleform_mod.php");

/**
 * Class mod_exiticket_mod_form.
 */
class mod_exiticket_mod_form extends moodleform_mod {
    /**
     * Defines the activity settings form.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("exiticketname", "mod_exiticket"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");
        $mform->addRule("name", get_string("maximumchars", "", 255), "maxlength", 255, "client");

        $this->standard_intro_elements();

        $mform->addElement("header", "questionsheader", get_string("questions", "mod_exiticket"));
        $mform->addElement("select", "questioncount", get_string("questioncount", "mod_exiticket"), [
            2 => get_string("twoquestions", "mod_exiticket"),
            3 => get_string("threequestions", "mod_exiticket"),
        ]);
        $mform->setDefault("questioncount", 3);

        $mform->addElement("text", "question1", get_string("question1", "mod_exiticket"), ["size" => 80]);
        $mform->setType("question1", PARAM_TEXT);
        $mform->setDefault("question1", get_string("defaultquestion1", "mod_exiticket"));
        $mform->addRule("question1", null, "required", null, "client");
        $mform->addRule("question1", get_string("maximumchars", "", 255), "maxlength", 255, "client");

        $mform->addElement("text", "question2", get_string("question2", "mod_exiticket"), ["size" => 80]);
        $mform->setType("question2", PARAM_TEXT);
        $mform->setDefault("question2", get_string("defaultquestion2", "mod_exiticket"));
        $mform->addRule("question2", null, "required", null, "client");
        $mform->addRule("question2", get_string("maximumchars", "", 255), "maxlength", 255, "client");

        $mform->addElement("text", "question3", get_string("question3", "mod_exiticket"), ["size" => 80]);
        $mform->setType("question3", PARAM_TEXT);
        $mform->setDefault("question3", get_string("defaultquestion3", "mod_exiticket"));
        $mform->hideIf("question3", "questioncount", "eq", 2);
        $mform->addRule("question3", get_string("maximumchars", "", 255), "maxlength", 255, "client");

        $mform->addElement("header", "availabilityheader", get_string("availability"));
        $mform->addElement("date_time_selector", "timeclose", get_string("timeclose", "mod_exiticket"), ["optional" => true]);
        $mform->addElement("advcheckbox", "allowedit", get_string("allowedit", "mod_exiticket"));
        $mform->setDefault("allowedit", 1);
        $mform->addHelpButton("allowedit", "allowedit", "mod_exiticket");

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Validates activity settings.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if ((int)$data["questioncount"] === 3 && trim((string)$data["question3"]) === "") {
            $errors["question3"] = get_string("required");
        }

        return $errors;
    }
}
