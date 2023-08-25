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


use tool_api_test\form\autocomplete_form;

$form = new \tool_api_test\form\autocomplete_form();
$form->display();

$numbers = range(1, 10);
$numbersAsString = array_map('strval', $numbers);

$templatable = new \tool_api_test\output\index_page($numbersAsString);
$PAGE->requires->js_call_amd('tool_api_test/test', 'init');

$output = $PAGE->get_renderer('tool_api_test');
echo $output->render($templatable);

// $columns = ['name', 'classname', 'methodname'];
// $webservicesObject = $DB->get_records('external_functions', array(), '', 'name, classname, methodname');

// $count = $DB->count_records('external_functions', array());
// echo $count;
// echo '<pre>';
// print_r($webservicesObject);
// echo '</pre>';

// $webservicesRecords = $DB->get_records('external_functions', array(), '', 'name');
//
//
$webservicesObject = $DB->get_records('external_functions', array(), 'name');
// foreach ()
$count = $DB->count_records('external_functions', array());
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
    // $functiondescs[] = external_api::external_function_info($webservice);
    $functiondescs[] = $webservice->name;
}


echo '<pre>';
print_r($functiondescs);
echo '</pre>';


echo $OUTPUT->footer();
