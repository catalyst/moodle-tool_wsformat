<?php

namespace tool_wsformat;


class autocomplete_form_tests extends \basic_testcase {
    public function test_adding() {
        $autocompleteform = new \tool_wsformat\form\autocomplete_form();
        $webservicearray = $autocompleteform->get_webservice_name_array();
        $length = count($webservicearray);
        $this->assertIsArray($webservicearray);
        $this->assertNotEquals(0, $length);
    }
}