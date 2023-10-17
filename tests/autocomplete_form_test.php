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
namespace tool_wsformat\test;
use tool_wsformat\form\autocomplete_form;
defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Implement autocomplete moodle form.
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autocomplete_form_test extends \basic_testcase {

    /**
     * Test that the length is correct.
     */
    public function test_length() {
        $autocompleteform = new autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        $length           = count($webservicearray);
        $this->assertNotEquals(0, $length);

    }

    /**
     * Test that an array is returned from the function.
     */
    public function test_array_returned() {
        $autocompleteform = new autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        $this->assertIsArray($webservicearray);
    }

    /**
     * Test is array is in the correct order.
     */
    public function test_correct_order() {
        $autocompleteform = new autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        $this->assertEquals('core_auth_confirm_user', $webservicearray[0]);
        $this->assertEquals('tiny_equation_filter', $webservicearray[694]);
    }

    /**
     * Test whether array values are strings as expected by consumer.
     */
    public function test_array_strings() {
        $autocompleteform = new autocomplete_form();
        $webservicearray  = $autocompleteform->get_webservice_name_array();
        foreach ($webservicearray as $webservice) {
            $this->assertIsString($webservice);
        }
    }
}
