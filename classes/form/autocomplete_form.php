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
 * @package          tool_wsformat
 * @copyright        2023 Djarran Cotleanu
 * @author           Djarran Cotleanu
 * @license          http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat\form;

use moodleform;

/**
 * Form for selecting web services to format.
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu
 * @author    Djarran Cotleanu
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autocomplete_form extends moodleform {

    /**
     * Define an autocomplete element for browsing webservices and a submit button.
     */
    public function definition() {
        global $DB;

        $webservicenames = $this->get_webservice_name_array();

        $mform = $this->_form;

        $options = [
            'minchars' => 2,
            'noselectionstring' => 'No webservices selected',
            'multiple' => true,
            'placeholder' => 'Search webservices...',
        ];

        // Documentation: https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#autocomplete.
        $mform->addElement('autocomplete', 'webservice_form', 'Webservices:', $webservicenames, $options);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submit', 'Update Selection');
        
        $clearbutton = '<button type="button" class="btn btn-secondary" onclick="window.location.href=\'https://moodle.localhost/admin/tool/wsformat/index.php\'">Clear</button>';
        $buttonarray[] = $mform->createElement('html', $clearbutton);
        
        $mform->addGroup($buttonarray, 'buttonarr', '', array(' '), false);
        $
    }

    /**
     * Get web service names from database.
     */
    public function get_webservice_name_array(): array {
        global $DB;
        $webservicesobject = $DB->get_records('external_functions', array(), '');

        $webservicenames = array();

        foreach ($webservicesobject as $key => $webservice) {
            $webservicenames[] = $webservice->name;
        }

        return $webservicenames;
    }
}
