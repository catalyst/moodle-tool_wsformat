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

    private function get_indexed_webservice_records(): array {
        global $DB;

        // Get_records returns an object array where key for each object is the name of the webservice.
        // Use array_values to change key to the index of each object so that we can filter based on $selectedWebserviceIndices.
        $webservicesrecords = array_values($DB->get_records('external_functions', array(), ''));

        return $webservicesrecords;
    }

    private function get_selected_webservice_objects(): array {

        $webservicesrecords = $this->get_indexed_webservice_records();

        $webservices = [];
        foreach ($this->selectedwebserviceindices as $index) {
            $webservice = $webservicesrecords[$index];
            $webservices[] = external_api::external_function_info($webservice);
        }

        return $webservices;
    }

    private function get_formatted_param_array($webservice): array {

        $paramobjectarray = $webservice->parameters_desc->keys;


        //Using the code from renderer.php
        $formattedparamsarray = [];

        foreach ($paramobjectarray as $paramname => $paramdesc) {

            $filteredParams = $this->rest_param_description_html($paramdesc, $paramname);

            $formatted = explode(PHP_EOL, $filteredParams);

            array_pop($formatted);

            for ($i = 0; $i <= count($formatted) - 1; $i++) {
                array_push($formattedparamsarray, $formatted[$i]);
            }
        }

        return $formattedparamsarray;
    }

    private function create_request_string($webservice, $paramsarray): string {

        $baseURL = "{{BASE_URL}}";
        $wsToken = "{{WS_TOKEN}}";

        $functionName = $webservice->name;

        $curlstring = $baseURL . "/webservice/rest/server.php?wstoken=" . $wsToken . "&wsfunction=" . $functionName . "&moodlewsrestformat=json";

        //Add params into curlString
        foreach ($paramsarray as $params) {
            $curlstring = $curlstring . "&" . $params;
        }

        return $curlstring;
    }

    private function create_postman_collection($postmanitems) {

        $collection = [
            'info' => [
                'name' => 'My Collection',
                'description' => 'Postman Collection',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            "item" => [...$postmanitems],
            'variable' => [
                [
                    'key' => 'BASE_URL',
                    'value' => 'http://moodle.localhost',
                    'type' => 'string',
                ],
                [
                    'key' => 'WSTOKEN',
                    'value' => '{{WSTOKEN}}',
                    'type' => 'string',
                ]

            ],
            'auth' => [
                'type' => 'apikey',
                'apikey' => [

                    [
                        'key' => 'value',
                        'value' => '{{WSTOKEN}}',
                        'type' => 'string',
                    ],
                    [
                        'key' => 'key',
                        'value' => 'wstoken',
                        'type' => 'string',
                    ],
                    [
                        'key' => 'in',
                        'value' => 'query',
                        'type' => 'string',
                    ]

                ]
            ]

        ];

        return $collection;
    }

    private function create_postman_request_item($webservice, $paramsarray) {

        $paramString = implode(',', $paramsarray);
        $paramPairs = explode(',', $paramString);
        $keyValPairs = [];
        foreach ($paramPairs as $paramPair) {
            // Split each pair by = to separate key and value
            $paramParts = explode('=', $paramPair);

            // Ensure we have both key and value before assigning
            if (count($paramParts) === 2) {
                $keyValuePairs[$paramParts[0]] = $paramParts[1];
            }
            $keyValPair = [
                'key' => $paramParts[0],
                'value' => $paramParts[1],
            ];

            $keyValPairs[] = $keyValPair;
        }

        $object = [
            "name" => $webservice->name,
            "request" => [
                "method" => "GET",
                "header" => [],
                "url" => [
                    "raw" => $this->url_safe($this->create_request_string($webservice, $paramsarray)) ,
                    "host" => [
                        "{{BASE_URL}}"
                    ],
                    "path" => [
                        "webservice",
                        "rest",
                        "server.php"
                    ],
                    "query" => [
                        [
                            "key" => "moodlewsrestformat",
                            "value" => "json"
                        ],
                        [
                            "key" => "wsfunction",
                            "value" => "core_webservice_get_site_info"
                        ],
                        ...$keyValPairs
                    ]
                ],
                "description" => $webservice->name
            ],
            "response" => []
        ];

        return $object;
    }


    /**
     * Exports the data for the index_page.mustache template
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output): stdClass {

        // Return empty object if no selected webservices.
        if (empty($this->selectedwebserviceindices)) {
            return (object) [];
        }

        $webservices = $this->get_selected_webservice_objects();

        $filteredrecords = [];
        $curlstringsforexport = [];
        $postmanitems = [];
        foreach ($webservices as $webservice) {

            //getting the params
            $paramsarray = $this->get_formatted_param_array($webservice);

            $curlstring = "curl " . $this->create_request_string($webservice, $paramsarray);
            $curlstringsforexport[] = str_replace('&', '%26', $curlstring);

            $postmanitems[] = $this->create_postman_request_item($webservice, $paramsarray);

            // echo '<pre>';
            // echo print_r($testcol);
            // echo '</pre>';

            $webserviceexport = (object) [
                'name' => $webservice->name,
                'description' => $webservice->description,
                'curl' => $curlstring,
                'postman' => 'shit',
            ];
            $filteredrecords[] = $webserviceexport;
        }

        $postmancollection = $this->create_postman_collection($postmanitems);

        $data = (object) [
            'formdata' => $filteredrecords,
            'items_selected' => true,
            'download' => json_encode($filteredrecords),
            'urls' => json_encode($curlstringsforexport),
            'postmancollection' => json_encode($postmancollection),
        ];

        return $data;
    }
    
    private function url_safe($url) {
        return str_replace('&', '%26', $url);
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
                    $paramdescription->keys[$attributname],
                    $paramstring
                );

                // print_r($paramdescription->keys);
                // echo $paramstring;
            }
            return $singlestructuredesc;
        } else {
            // description object is a primary type (string, integer)
            $paramstring = $paramstring . '=';
            $type = '';

            switch ($paramdescription->type) {
                    // 0 or 1 only for now
                case PARAM_INT:
                    $type = '{{INT}}';
                    break;
                case PARAM_BOOL:
                    $type = '{{BOOL}}';
                    break;
                case PARAM_TEXT:
                    $type = '{{TEXT}}';
                    break;
                case PARAM_ALPHA:
                    $type = '{{ALPHA}}';
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
