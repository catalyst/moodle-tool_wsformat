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
 * Implement renderable class
 *
 * @package          tool_wsformat
 * @copyright        2023 Djarran Cotleanu
 * @author           Djarran Cotleanu
 * @license          http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat\output;

use plugin_renderer_base;

/**
 * Renderer class.
 */
class renderer extends plugin_renderer_base {
    /**
     * Render index_page mustache template.
     */
    protected function render_index_page(\tool_wsformat\output\index_page $indexpage) {
        $data = $indexpage->export_for_template($this);
        return parent::render_from_template('tool_wsformat/index_page', $data);
    }

    /**
     * Renders the plugin_description mustache template.
     * @param \tool_wsformat\output\plugin_description $plugindescription The plugin description object.
     * @return string|boolean 
     */
    protected function render_plugin_description(\tool_wsformat\output\plugin_description $plugindescription) {
        return parent::render_from_template('tool_wsformat/plugin_description', new \stdClass);
    }
}
