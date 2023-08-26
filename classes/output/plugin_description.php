<?php

namespace tool_api_test\output;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Shows tool_analytics models list.
 */
class plugin_description implements \renderable, \templatable
{

    public function __construct()
    {
    }

    public function export_for_template(\renderer_base $output)
    {
    }
}
