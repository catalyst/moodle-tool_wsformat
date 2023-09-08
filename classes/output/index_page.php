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
            //Mine (new version):
            $webservice = $webservicesrecords[$index];
            $webserviceproperties = external_api::external_function_info($webservice);
            $object = new stdClass();
            $object->name = $webserviceproperties->name;
            $object->description = $webserviceproperties->description;

            ///DOESN'T WORK FOR ARRAY PARAMS
            //gets an array of all the required params
            $paramObjectArray = $webserviceproperties -> parameters_desc -> keys;
            $params = array_keys($paramObjectArray);//gets the keys of the array

            for ($i = 0; $i <= sizeof($paramObjectArray); $i++){
                echo '<pre>';
                echo print_r($params.value());
                echo '</pre>';
            }


            echo '<pre>';
            //Check what param object array looks like
            //echo print_r($paramObjectArray);
            //Check what param looks like
            echo print_r($params);
            echo '</pre>';

            $baseURL = "{{BASE_URL}}";
            $curlString = `curl "${baseURL}/webservice/rest/server.php?wstoken=...&wsfunction=...&moodlewsrestformat=json"`
    
            

            //$paramproperties = external_api::external_function_parameters($webservice);
            //the above returns call to undefined method core_external\exeternal_api::external_function_parameters()
            $filteredrecords[] = $object;
        }

        // echo '<pre>';
        // //Check what our filtered records has
        // echo print_r($filteredrecords);
        // //Check what properties the selected webservice has 
        // echo print_r($webserviceproperties); //note that 1 = ture and 0 = false
        
        // echo print_r("\n");
        
       
        // echo '</pre>';

        $data = new stdClass();
        $data->formdata = $filteredrecords;
        $data->items_selected = true;
        $data->test = json_encode($filteredrecords);
        return $data;
    }
}
