<?php

namespace tool_wsformat;


class autocomplete_form_tests extends \basic_testcase {

    /**
     * Test that the length is correct.
     */
    public function test_length() {
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray = $autocompleteform->get_webservice_name_array();
        $length = count($webservicearray);
        $this->assertNotEquals(0, $length);
    }

    /**
     * Test that an array is returned from the function.
     */
    public function test_array_returned() {
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray = $autocompleteform->get_webservice_name_array();
        $this->assertIsArray($webservicearray);
    }

    /**
     * Test is array is in the correct order.
     */
    public function test_correct_order() {
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray = $autocompleteform->get_webservice_name_array();
        $this->assertEquals('core_auth_confirm_user', $webservicearray[0]);
        $this->assertEquals('tiny_equation_filter', $webservicearray[694]);
    }

    /**
     * Test whether array values are strings as expected by consumer.
     */
    public function test_array_strings() {
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray = $autocompleteform->get_webservice_name_array();
        foreach ($webservicearray as $webservice) {
            $this->assertIsString($webservice);
        }
    }
}
