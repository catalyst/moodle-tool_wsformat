<?php

use core_external\external_api;

require('../../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());


$PAGE->set_context(context_system::instance());
$PAGE->set_url('/admin/tool/api_test/index.php');
$PAGE->set_title(get_string('pluginname', 'tool_api_test'));
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

$output = $PAGE->get_renderer('tool_api_test');

$plugin_description_template = new \tool_api_test\output\plugin_description();
echo $output->render($plugin_description_template);

use tool_api_test\form\autocomplete_form;

$mform = new autocomplete_form(); // Place moodle_url as argument to redirect on submit: new moodle_url('/admin/tool/api_test/test.php')
$mform->display();

$formarray = []; // array will be passed into mustache template

if ($mform->is_cancelled()) {
} else if ($data = $mform->get_data()) {

    // Populate formarray with selected form web services
    foreach ($data->webservice_form as $key => $value) {
        $formarray[] = (string) $value;
    }
} else {
    // Code runs when form is shown first time or if validation fails.
}

$selected_section_template = new \tool_api_test\output\index_page($formarray);
$PAGE->requires->js_call_amd('tool_api_test/test', 'init'); // initialise and call javascript file on the page

echo $output->render($selected_section_template);

// Function prints webservice function info including parameters and response objects. Used to aid development only
function printWebservices()
{

    global $DB;
    $webservicesObject = $DB->get_records('external_functions', array(), 'name');

    // Create array called functiondescs
    $functiondescs = array();

    foreach ($webservicesObject as $key => $webservice) {

        // Objects are key => value pairs
        // Here we state that for each key in $webservicesObject, give us the key and value as variables
        /* $webservice example: {
            "id":"584",
            "name":"auth_email_get_signup_settings",
            "classname":"auth_email_external",
            "methodname":"get_signup_settings",
            "classpath":null,
            "component":"auth_email",
            "capabilities":"",
            "services":null}
    */

        // sites/moodle/lib/external/classes/external_api.php
        $functiondescs[] = external_api::external_function_info($webservice);
        // $functiondescs[] = $webservice->name;
    }

    echo '<pre>';
    print_r($functiondescs);
    echo '</pre>';
}

// printWebservices();

echo $OUTPUT->footer();
