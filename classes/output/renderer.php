<?php

namespace tool_api_test\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Renderer class.
 */
class renderer extends plugin_renderer_base
{
    protected function render_index_page(\tool_api_test\output\index_page $index_page)
    {
        $data = $index_page->export_for_template($this);
        return parent::render_from_template('tool_api_test/index_page', $data);

    }
}
