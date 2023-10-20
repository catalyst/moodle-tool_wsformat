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
defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * Tests for autocomplete_form class.
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \tool_wsformat\form\autocomplete_form
 */
class autocomplete_form_test extends \advanced_testcase {

    /**
     * Test that the array returned is not empty.
     */
    public function test_not_empty() {
        $this->resetAfterTest(true);
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        $length           = count($webservicearray);
        $this->assertNotEquals(0, $length);
    }

    /**
     * Test that an array is returned from the function.
     */
    public function test_array_returned() {
        $this->resetAfterTest(true);
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        $this->assertIsArray($webservicearray);
    }

    /**
     * Test whether array values are strings as expected by consumer.
     */
    public function test_array_strings() {
        $this->resetAfterTest(true);
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        foreach ($webservicearray as $webservice) {
            $this->assertIsString($webservice);
        }
    }

    /**
     * Test if external service is created successfully.
     */
    public function test_create_external_service() {
        $this->resetAfterTest(true);
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $this->resetAllData();
        $servicename  = $autocompleteform->create_external_service();
        $this->assertEquals('Webservice test service', $servicename);
    }

    /**
     * Test if external service is created and included in the select element list.
     */
    public function test_get_external_service_includes_new_service() {
        $this->resetAfterTest(true);
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $services  = $autocompleteform->get_external_services();
        $this->assertContains('Webservice test service', $services);
    }

    /**
     * Test that class does not attempt to insert plugin external service if already exists.
     */
    public function test_get_external_service_does_not_create_if_exists() {
        $this->resetAfterTest(true);
        $this->expectNotToPerformAssertions();
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $autocompleteform->get_external_services();
    }
}
