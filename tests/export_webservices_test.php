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

namespace tool_wsformat;

use core_external\external_api;
use tool_wsformat\export_webservices;
use tool_wsformat\form\autocomplete_form;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * Tests for export_webservices class.
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \tool_wsformat\export_webservices
 */
class export_webservices_test extends \advanced_testcase {

    /**
     * Test correct params are returned.
     */
    public function test_get_params_correct() {
        global $DB;
        $this->resetAfterTest(true);

        $webservicefromdb = $DB->get_record('external_functions', ['name' => 'core_auth_confirm_user']);
        $webservice = external_api::external_function_info($webservicefromdb);

        new autocomplete_form();
        $exportwebservices = new export_webservices([0, 1, 2, 3, 4]); // Pass arbitrary array.
        $params = $exportwebservices->get_formatted_param_array($webservice);

        $this->assertEquals('username={{STRING}}', $params[0]);
        $this->assertEquals('secret={{STRING}}', $params[1]);
    }

    /**
     * Test correct params are returned.
     */
    public function test_correct_request_string() {
        global $DB;
        global $CFG;
        $this->resetAfterTest(true);

        $webservicefromdb = $DB->get_record('external_functions', ['name' => 'core_auth_confirm_user']);
        $webservice = external_api::external_function_info($webservicefromdb);

        new autocomplete_form();
        $exportwebservices = new export_webservices([0, 1, 2, 3, 4], 0); // Pass arbitrary array.
        $params = $exportwebservices->get_formatted_param_array($webservice);

        $requeststring = $exportwebservices->create_request_string($webservice, $params);

        $expectedstring = $CFG->wwwroot . '/webservice/rest/server.php?wstoken=' . $exportwebservices->servicetoken . '&wsfunction='
            . $webservicefromdb->name . '&moodlewsrestformat=json';

        foreach ($params as $param) {
            $expectedstring = $expectedstring . '&' . $param;
        }

        $this->assertEquals($expectedstring, $requeststring);
    }

    /**
     * Test that token is created.
     */
    public function test_create_token() {
        global $DB;
        global $CFG;
        $this->resetAfterTest(true);

        $exportwebservices = new export_webservices([0, 1, 2]); // Pass arbitrary array.

        // Initialise form (to create plugin's external service).
        $form = new autocomplete_form();
        $servicenames = $form->get_external_services();

        // Get index of plugin's external service.
        $index = array_search('Webservice test service', $servicenames);

        // Get plugin's external service object.
        $externalservicesarray = array_values($DB->get_records('external_services'));
        $serviceobject = $externalservicesarray[$index];

        // Check that token doesn't exist.
        $tokenexist = $exportwebservices->get_service_token($serviceobject->id);
        $this->assertFalse($tokenexist);

        $exportwebservices->create_token($serviceobject);

        // Check that token now exists after creation.
        $tokenexist = $exportwebservices->get_service_token($serviceobject->id);
        $this->assertNotFalse($tokenexist);
    }

    public function test_add_function_to_service() {
        global $DB;
        $this->resetAfterTest(true);

        $webservicestoadd = [
            'core_auth_is_minor', 'core_auth_resend_confirmation_email',
            'core_backup_get_async_backup_links_backup',
        ];

        // Initialise form (to create plugin's external service).
        new autocomplete_form();

        // Get plugin's external service.
        $externalservice = $DB->get_record('external_services', ['name' => 'Auto create test service']);
        echo 'hey';
        echo print_r($externalservice);

        // Check if external service contains the functions we intend to add.
        foreach ($webservicestoadd as $function) {
            $exists = $DB->record_exists(
                'external_services_functions',
                [
                    'externalserviceid' => $externalservice->id,
                    'functionname' => $function,
                ]
            );
            $this->assertFalse($exists);
        }

        // Initialise export_webservices to test function.
        $exportwebservices = new export_webservices([0, 1, 2]);

        // Add function and test if it was successfully added.
        foreach ($webservicestoadd as $function) {
            $exportwebservices->add_function_to_service($function, $externalservice->id);
            $exists = $DB->record_exists(
                'external_services_functions',
                [
                    'externalserviceid' => $externalservice->id,
                    'functionname' => $function,
                ]
            );
            $this->assertNotFalse($exists);
        }
    }

    /**
     * Test correct params are returned.
     */
    public function test_create_postman_collection() {
        $this->resetAfterTest(true);

        new autocomplete_form();
        $exportwebservices = new export_webservices([0, 1, 2, 3, 4], 1); // Pass arbitrary array.

        // Create Postman Collection.
        $postmanitems = [];
        foreach ($exportwebservices->webservices as $webservice) {
            $paramsarray = $exportwebservices->get_formatted_param_array($webservice);

            $postmanitems[] = $exportwebservices->create_postman_request_item($webservice, $paramsarray);
        }

        $postmancollection = $exportwebservices->create_postman_collection($postmanitems);

        // Assert that object contains required keys.
        $this->assertObjectHasAttribute('info', $postmancollection);
        $this->assertObjectHasAttribute('item', $postmancollection);
        $this->assertObjectHasAttribute('variable', $postmancollection);
        $this->assertObjectHasAttribute('auth', $postmancollection);

        // Assert that collection contains the amount of webservices passed into export_webservices.
        $this->assertEquals(5, count($postmancollection->item));

        // Assert that token is inserted in collection.
        $this->assertEquals($exportwebservices->servicetoken, $postmancollection->auth['apikey'][0]['value']);
    }
}
