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
 * Setup inital plugin page
 *
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_external\external_api;

require('../../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());
global $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/admin/tool/wsformat/index.php');
$PAGE->set_title(get_string('pluginname', 'tool_wsformat'));
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

$output = $PAGE->get_renderer('tool_wsformat');

$plugindescriptiontemplate = new \tool_wsformat\output\plugin_description();
echo $output->render($plugindescriptiontemplate);

use tool_wsformat\form\autocomplete_form;

require_once($CFG->dirroot . '/webservice/lib.php');

$webservicemanager = new webservice();
$tokens = $DB->get_records('external_tokens', [], '');
$services = $DB->get_records('external_services', [], '');

// echo print_r($services);

// foreach ($services as $key => $service) {
//     echo $service->shortname;
// }




$mform = new autocomplete_form();
$mform->display();

$formarray = [];
$selectedservice;
if ($data = $mform->get_data()) {
    // echo print_r($data);
    // Populate formarray with selected form web services.
    foreach ($data->selected_webservices as $key => $value) {
        $formarray[] = (string) $value;
    }
    $selectedservice = $data->selected_external_service;
}

$selectedsectiontemplate = new \tool_wsformat\output\index_page($formarray);
$PAGE->requires->js_call_amd('tool_wsformat/eventListeners', 'init');

echo $output->render($selectedsectiontemplate);


/**
 * Function prints webservice function info including parameters and response objects. Used to aid development only.
 */
function print_webservices() {
    global $DB;
    $webservicesobject = $DB->get_records('external_functions', [], 'name');

    $functiondescs = [];
    foreach ($webservicesobject as $key => $webservice) {
        // Documentation: sites/moodle/lib/external/classes/external_api.php.
        $functiondescs[] = external_api::external_function_info($webservice);
    }

    return $functiondescs;
}


print_webservices();

echo $OUTPUT->footer();
