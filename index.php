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

// Prepare the data (context) for the template
$template_data = new stdClass(); // Create new object

// Load the template and render it, passing it $template_data
// See templates/mytemplate.mustache
echo $OUTPUT->render_from_template('tool_api_test/mytemplate', $template_data);


$webservicesObject = $DB->get_records('external_functions', array(), 'name');
$count = $DB->count_records('external_functions', array());
echo $count;
echo '<pre>';
print_r($webservicesObject);
echo '</pre>';





echo $OUTPUT->box_end();


echo $OUTPUT->footer();
