<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $url = $CFG->wwwroot . '/' . $CFG->admin . '/tool/api_test/index.php';
    $ADMIN->add('development', new admin_externalpage('toolapi_test', 'API Test', $url));
}