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
use webservice;

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

        $webservicenames = $this->get_webservice_name_array();
        $servicenames = $this->get_external_services();

        $mform = $this->_form;

        $autocompleteoptions = [
            'minchars'          => 2,
            'noselectionstring' => get_string('nowebservicesselected', 'tool_wsformat'),
            'multiple'          => true,
            'placeholder'       => get_string('searchwebservices', 'tool_wsformat'),
        ];


        // Documentation: https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#autocomplete.
        $mform->addElement(
            'autocomplete',
            'selected_webservices',
            get_string('webservices', 'tool_wsformat'),
            $webservicenames,
            $autocompleteoptions
        );

        $mform->addElement(
            'select',
            'selected_external_service',
            'Choose service token',
            $servicenames,
        );

        $submitbutton = $mform->createElement('submit', 'submit', get_string('updateselection', 'tool_wsformat'));
        $clearbutton = $mform->createElement('button', 'ws_clear_button', get_string('clearbtn', 'tool_wsformat'));
        $mform->addGroup([$submitbutton, $clearbutton], 'buttongroup', '', null, false);
    }

    /**
     * Get web service names from database.
     */
    public function get_webservice_name_array(): array {
        global $DB;
        $webservicesobject = $DB->get_records('external_functions');

        $webservicenames = [];

        foreach ($webservicesobject as $key => $webservice) {
            $webservicenames[] = $webservice->name;
        }
        return $webservicenames;
    }

    /**
     * Get external service names from database and create the wsformat's external
     * service that is used for testing purposes.
     */
    public function get_external_services(): array {
        global $DB;
        $serviceobject = $DB->get_records('external_services');
        $lastkey = end($serviceobject);
        // echo print_r($lastKey);
        // echo print_r($serviceobject);

        $wsformatexists = false;
        $servicenames = [];
        foreach ($serviceobject as $key => $service) {
            $servicenames[] = $service->name; // Add service name to array.

            if ($service->shortname === 'wsformat_plugin') {
                $wsformatexists = true;
            }

            // Create external service if it doesn't exist.
            if ($service->shortname === $lastkey->shortname) {
                if ($wsformatexists === false) {
                    $servicenames[] = $this->create_external_service();
                }
            }
        }

        return $servicenames;
    }

    /**
     * Create plugin external service that is used for testing purposes.
     */
    private function create_external_service(): string {
        $webservicemanager = new webservice();

        $wsformatservicename = 'Webservice test service';
        $serviceobject = (object) [
            'name' => $wsformatservicename,
            'shortname' => 'wsformat_plugin',
            'enabled' => 1,
            'restrictedusers' => 0,
            'downloadfiles' => 0,
            'uploadfiles' => 0,
            'requiredcapability' => '',
            'id' => 0, // Default value used when creating a new service.
            'submitbutton' => 'Add service'
        ];

        $webservicemanager->add_external_service($serviceobject);
        
        return $wsformatservicename;
    }
}
