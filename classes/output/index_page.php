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

    /**
     * Stores the selected webservice indices.
     *
     * @var array
     */
    protected $selectedwebserviceindices = array();

    /**
     * Constructor function - assign instance variable.
     * @param array $indicies
     */
    public function __construct(array $indicies) {
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
        $curl_urls = [];
        foreach ($this->selectedwebserviceindices as $index) {
            //Mine (new version):
            $webservice = $webservicesrecords[$index];
            $webserviceproperties = external_api::external_function_info($webservice);
            $object = new stdClass();
            $object->name = $webserviceproperties->name;
            $object->description = $webserviceproperties->description;

            //getting the params
            $paramObjectArray = $webserviceproperties->parameters_desc->keys;

            //echo '<pre>';
            //Check what param object array looks like
            //echo print_r($paramObjectArray);

            //Using the code from renderer.php
            $paramsArray = [];

            foreach ($paramObjectArray as $paramname => $paramdesc) {
                // $checkArray = [];
                $filteredParams = $this->rest_param_description_html($paramdesc, $paramname);
                // print_r($filteredParams);
                // echo '<pre>';
                // echo strval($filteredParams);
                // echo '</pre>';

                // echo '<pre>';
                // print_r($filteredParams);
                // echo '</pre>';
                //turn listed params into it's seperate elements in the array
                $formatted = explode(PHP_EOL, $filteredParams);
                //remove the last empty element in the array
                array_pop($formatted);
                // echo '<pre>';
                // print_r($formatted);
                // echo '</pre>';
                // print_r($formatted);
                // for ($i = 0; $i <= count($formatted) - 1; $i++){
                //     echo '<pre>';
                //     echo count($formatted);
                //     echo "\n$i: $formatted[$i]";
                //     echo '</pre>';   
                // }

                for ($i = 0; $i <= count($formatted) - 1; $i++) {
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
            $functionName = $object->name;
            // echo '<pre>';
            // echo $functionName;
            // echo '</pre>';

            //$curlString = `curl "${baseURL}/webservice/rest/server.php?wstoken=${wsToken}&wsfunction=${functionName}&moodlewsrestformat=json"`;
            $curlString = "curl" . " " . $baseURL . "/webservice/rest/server.php?wstoken=" . $wsToken . "&wsfunction=" . $functionName . "&moodlewsrestformat=json";
            // echo '<pre>';
            // echo $curlString;
            // echo '</pre>';
            // print_r($paramsArray);
            //Add params into curlString
            foreach ($paramsArray as $params) {
                $curlString = $curlString . "&" . $params;
            }
            $curlStringForUrl = str_replace('&', '%26', $curlString);

            $object->curl = $curlString;

            $filteredrecords[] = $object;
            $curl_urls[] = $curlStringForUrl;
        }

        $data = (object) [
            'formdata' => $filteredrecords,
            'items_selected' => true,
            'download' => json_encode($filteredrecords),
            'urls' => json_encode($curl_urls)
        ];
        return $data;
    }

    //Taken from renderer.php
    public function rest_param_description_html($paramdescription, $paramstring) {
        $brakeline = <<<EOF


        EOF;

        // description object is a list
        if ($paramdescription instanceof external_multiple_structure) {
            echo '<h1>';
            echo 'Instance: List';
            echo '</h1>';
            $paramstring = $paramstring . '[0]';
            echo '<pre>';
            echo '$paramdescription->content';
            echo '<br>';
            print_r($paramdescription->content);
            echo '</pre>';
            echo '<br>';
            echo '$paramstring';
            echo '<br>';
            echo $paramstring;
            echo '<br>';
            echo $this->rest_param_description_html($paramdescription->content, $paramstring);
            $return = $this->rest_param_description_html($paramdescription->content, $paramstring);
            return $return;
        } else if ($paramdescription instanceof external_single_structure) {
            echo '<h1>';
            echo 'Instance: Object';
            echo '</h1>';
            // description object is an object
            $singlestructuredesc = "";
            $initialparamstring = $paramstring;
            echo 'Entering foreach block';
            echo '<br>';
            foreach ($paramdescription->keys as $attributname => $attribut) {

                $paramstring = $initialparamstring . '[' . $attributname . ']';
            echo '<h3>';
            echo 'Current parameter: ' . $paramstring;

            echo '</h3>';
                echo '<pre>';
                echo '$paramdescription->keys[$attributname';
                echo '<br>';
                print_r($paramdescription->keys[$attributname]);
                echo '</pre>';
                echo '<br>';
                echo '$paramstring';
                echo '<br>';
                echo $paramstring;
                echo '<br>';
                $singlestructuredesc .= $this->rest_param_description_html(
                    $paramdescription->keys[$attributname],
                    $paramstring
                );

                // print_r($paramdescription->keys);
                // echo $paramstring;
            }
            return $singlestructuredesc;
        } else {
            echo '<h1>';
            echo 'Instance: Type';
            echo '</h1>';
            // description object is a primary type (string, integer)
            $paramstring = $paramstring . '=';
            $type = '';

            switch ($paramdescription->type) {
                    // 0 or 1 only for now
                case PARAM_INT:
                    $type = '{{INT}}';
                    echo '<h3>';
                    echo 'Type: int';
                    echo '</h3>';
                    break;
                case PARAM_BOOL:
                    $type = '{{BOOL}}';
                    echo '<h3>';
                    echo 'Type: bool';
                    echo '</h3>';
                    break;
                case PARAM_TEXT:
                    $type = '{{TEXT}}';
                    echo '<h3>';
                    echo 'Type: text';
                    echo '</h3>';
                    break;
                case PARAM_ALPHA:
                    $type = '{{ALPHA}}';
                    echo '<h3>';
                    echo 'Type: alpha';
                    echo '</h3>';
                    break;
                case PARAM_FLOAT;
                    echo '<h3>';
                    echo 'Type: double';
                    echo '</h3>';
                    $type = '{{DOUBLE}}';
                    break;
                default:
                    echo '<h3>';
                    echo 'Type: string';
                    echo '</h3>';
                    $type = '{{STRING}}';
            }

            echo $paramstring . $type;
                    echo '<br>';
            return $paramstring . $type . $brakeline;
            // print_r($paramdescription->type);
        }
    }
}
