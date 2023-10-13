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
 * Implement autocomplete moodle form.
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat\form;

use moodleform;

/**
 * Form for selecting web services to format.
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autocomplete_form extends moodleform {

    /**
     * Define an autocomplete element for browsing webservices and a submit button.
     */
    public function definition() {
        global $DB;

        $webservicenames = $this->get_webservice_name_array();
        $servicenames = $this->get_external_services();

        $mform = $this->_form;

        $options = [
            'minchars'          => 2,
            'noselectionstring' => get_string('nowebservicesselected', 'tool_wsformat'),
            'multiple'          => true,
            'placeholder'       => get_string('searchwebservices', 'tool_wsformat'),
        ];

        $options2 = [
            'minchars'          => 2,
            'noselectionstring' => get_string('nowebservicesselected', 'tool_wsformat'),
            'placeholder'       => get_string('searchwebservices', 'tool_wsformat'),
        ];

        // Documentation: https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#autocomplete.
        $mform->addElement('autocomplete', 'selected_webservices', get_string('webservices', 'tool_wsformat'), $webservicenames,
         $options);
        $mform->addElement('select', 'selected_external_service', 'Choose service token', $servicenames,
         $options2);

        $buttonarray   = [];
        $buttonarray[] = $mform->createElement('submit', 'submit', get_string('updateselection', 'tool_wsformat'));

        $clearbutton   = '<button type="button" class="btn btn-secondary" '
        .'onclick="window.location.href=\'index.php\'">'
        .get_string('clearbtn', 'tool_wsformat')
        .'</button>';

        $buttonarray[] = $mform->createElement('html', $clearbutton);

        $mform->addGroup($buttonarray, 'buttonarr', '', null, false);
    }

    /**
     * Get web service names from database.
     */
    public function get_webservice_name_array(): array {
        global $DB;
        $webservicesobject = $DB->get_records('external_functions', [], '');

        $webservicenames = [];

        foreach ($webservicesobject as $key => $webservice) {
            $webservicenames[] = $webservice->name;
        }
        return $webservicenames;
    }

    /**
     * Get external service names from database.
     */
    public function get_external_services(): array {
        global $DB;
        $serviceobject = $DB->get_records('external_services', [], '');

        $servicenames = [];

        foreach ($serviceobject as $key => $service) {
            $servicenames[] = $service->shortname;
        }

        return $servicenames;
    }
}
