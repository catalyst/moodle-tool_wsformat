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
 * Admin tool presets plugin to load some settings.
 *
 * @package          tool_wsformat
 * @copyright        2023 Djarran Cotleanu, Jacqueline Mail
 * @author           Djarran Cotleanu, Jacqueline Mail
 * @license          http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat\output;

use core_external\external_api;
use stdClass;

/**
 * Class for processing data for index_page template.
 */
class index_page implements \renderable, \templatable {
    protected $selectedwebserviceindices = array();

    /**
     * Constructor function - assign instance variable.
     */
    public function __construct($indicies) {
        $this->selectedwebserviceindices = $indicies;
    }

    /**
     * Exports the data for the index_page.mustache template
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $DB;

        // Return empty object if no selected webservices.
        if (empty($this->selectedwebserviceindices)) {
            return new stdClass();
        }

        // Get_records returns an object array where key for each object is the name of the webservice.
        // Use array_values to change key to the index of each object so that we can filter based on $selectedWebserviceIndices.
        $webservicesrecords = array_values($DB->get_records('external_functions', array(), ''));

        // echo '<pre>';
        // echo print_r($webservicesrecords);
        // echo '</pre>';
        $filteredrecords = [];
        foreach ($this->selectedwebserviceindices as $index) {
            //Mine:
            $webservice = $webservicesrecords[$index];
            $webserviceproperties = external_api::external_function_info($webservice);
            $object = new stdClass();
            $object->name = $webserviceproperties->name;
            $object->description = $webserviceproperties->description;
            $filteredrecords[] = $object;
        }

        echo '<pre>';

        //Check what properties the selected webservice has 
        //echo print_r($webserviceproperties); //note that 1 = ture and 0 = false
        //Check what our filtered records has
        echo print_r($filteredrecords);
       
        echo '</pre>';

        $data = new stdClass();
        $data->formdata = $filteredrecords;
        $data->items_selected = true;
        $data->test = json_encode($filteredrecords);

        $data->text = "";

        return $data;
    }
}
