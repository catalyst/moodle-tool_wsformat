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
use core_webservice_renderer;
use stdClass;

use core_external\external_multiple_structure;
use core_external\external_single_structure;


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

            //getting the params
            $paramObjectArray = $webserviceproperties -> parameters_desc -> keys;

            //echo '<pre>';
            //Check what param object array looks like
            //echo print_r($paramObjectArray);

            //Using the code from renderer.php
            $paramsArray = [];

            foreach($paramObjectArray as $paramname => $paramdesc) {
                // $checkArray = [];
                $filteredParams = $this->rest_param_description_html($paramdesc, $paramname);
                // echo '<pre>';
                // echo strval($filteredParams);
                // echo '</pre>';

                //turn listed params into it's seperate elements in the array
                $formatted = explode(PHP_EOL, $filteredParams);
                //remove the last empty element in the array
                array_pop($formatted);

                // for ($i = 0; $i <= count($formatted) - 1; $i++){
                //     echo '<pre>';
                //     echo count($formatted);
                //     echo "\n$i: $formatted[$i]";
                //     echo '</pre>';   
                // }

                for ($i = 0; $i <= count($formatted) - 1; $i++){
                    array_push($paramsArray, $formatted[$i]);
                }
            }

            // for($i = 0; $i < sizeOf($paramsArray); $i++){
            //     echo '<pre>';
            //     echo "$i: $paramsArray[$i]";
            //     echo '</pre>';
            // }

            //Creating the curl 
            $baseURL = "{{BASE_URL}}";
            $wsToken = "{{WS_TOKEN}}";
            $functionName= $object -> name;
            $functionDesc= $object -> description;
            // echo '<pre>';
            // echo $functionName;
            // echo '</pre>';

            //$curlString = `curl "${baseURL}/webservice/rest/server.php?wstoken=${wsToken}&wsfunction=${functionName}&moodlewsrestformat=json"`;
            $curlString = "curl" . " " . $baseURL . "/webservice/rest/server.php?wstoken=" . $wsToken . "&wsfunction=" . $functionName . "&moodlewsrestformat=json";
            // echo '<pre>';
            // echo $curlString;
            // echo '</pre>';

            //Add params into curlString
            foreach($paramsArray as $params){
                $curlString = $curlString . "&" . $params;
            }

            $object -> curl = $curlString;

            $filteredrecords[] = $object;

            $postmanURL = $baseURL . "/webservice/rest/server.php?wstoken=" . $wsToken . "&wsfunction=" . $functionName . "&moodlewsrestformat=json" . $params;
            $collection = [
                'info' => [
                    'name' => 'My Collection',
                    'description' => 'Postman Collection',
                    'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                ],
                'item' => [
                    [
                        'name' => $functionName,
                        'request' => [
                            'method' => 'POST',
                            'header' => [],
                            'url' => [
                                'raw' => $postmanURL,
                                'host' => [$baseURL],
                                'path' => ['webservice', 'rest', 'server.php'],
                                'query' => [
                                    [
                                        'key' => 'moodlewsrestformat',
                                        'value' => 'json',
                                    ],
                                    [
                                        'key' => 'Content-Type',
                                        'value' => 'application/json',
                                    ],
                                ],
                            ],
                        ],
                        'description' => $functionDesc
                    ],
                    'response' => [],
                ],
            ];

            $postmancol = json_encode($collection, JSON_PRETTY_PRINT);
            $collectionJson = str_replace('\\/', '/', $postmancol);
            $object -> postman = $collectionJson;
            // echo '<pre>' . $collectionJson . '</pre>';
        
        }

        $data = new stdClass();
        $data->formdata = $filteredrecords;
        $data->items_selected = true;
        $data->test = json_encode($filteredrecords);
        return $data;
    }

        

    //Taken from renderer.php
    public function rest_param_description_html($paramdescription, $paramstring) {
        $brakeline = <<<EOF


EOF;
        // description object is a list
        if ($paramdescription instanceof external_multiple_structure) {
            $paramstring = $paramstring . '[0]';
            $return = $this->rest_param_description_html($paramdescription->content, $paramstring);
            return $return;
        } else if ($paramdescription instanceof external_single_structure) {
            // description object is an object
            $singlestructuredesc = "";
            $initialparamstring = $paramstring;
            foreach ($paramdescription->keys as $attributname => $attribut) {
                $paramstring = $initialparamstring . '[' . $attributname . ']';
                $singlestructuredesc .= $this->rest_param_description_html(
                                $paramdescription->keys[$attributname], $paramstring);
            }
            return $singlestructuredesc;
        } else { 
            // description object is a primary type (string, integer)
            $paramstring = $paramstring . '=';
           
            switch ($paramdescription->type) {
                case PARAM_BOOL: // 0 or 1 only for now
                case PARAM_INT:
                    $type = '{{INT}}';
                    break;
                case PARAM_FLOAT;
                    $type = '{{DOUBLE}}';
                    break;
                default:
                    $type = '{{STRING}}';
            }
          return $paramstring . $type . $brakeline;

        }
    }
}
