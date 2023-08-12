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
// echo $OUTPUT->heading(get_string('pluginname', 'tool_api_test'));
// echo get_string('plugindescription', 'tool_api_test');
echo $OUTPUT->box_start();


// Create
$numbers = range(1, 10);
$numbersAsString = array_map('strval', $numbers);

$templatable = new \tool_api_test\output\index_page($numbersAsString);

$output = $PAGE->get_renderer('tool_api_test');
echo $output->render($templatable);

$columns = ['name', 'classname', 'methodname'];
$webservicesObject = $DB->get_records('external_functions', array(), '', 'name, classname, methodname');
$array = [];
foreach ($webservicesObject as $key => $value) {
  $array[] = $key;
}

$count = $DB->count_records('external_functions', array());
echo $count;
echo '<pre>';
print_r($array);
echo '</pre>';





echo $OUTPUT->box_end();


echo $OUTPUT->footer();
