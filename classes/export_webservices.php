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
 * Variables and methods relating to exporting and downloading of webservice formats
 *
 * @package          tool_wsformat
 * @copyright        2023 Djarran Cotleanu
 * @author           Djarran Cotleanu
 * @license          http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat;

use core_external\external_api;
use core_webservice_renderer;
use stdClass;

use core_external\external_multiple_structure;
use core_external\external_single_structure;

/**
 * Class for processing and exporting web service data.
 */
class export_webservices {

    /**
     * Stores the host address of the server.
     * @var string
     */
    private $host = '';

    /**
     * Stores webservice export data passed to download.php from template.
     * @var string
     */
    private $serializeddata = '';

    private $selectedwebserviceindices = [];

    /**
     * Constructor function - assign instance variables.
     * @param string $type
     * @param string $host
     * @param string $serializeddata
     */
    public function __construct(string $host, string $serializeddata, array $selectedwebserviceindices) {
        $this->host = $host;
        $this->serializeddata = $serializeddata;
        $this->selectedwebserviceindices = $selectedwebserviceindices;
    }


    /**
     * Exports data as cURL commands in a text file.
     * Sets header to initiate download with filename and extension.
     */
    public function export_as_curl() {
        header('Content-Disposition: attachment; filename=curl.txt');
        header('Content-Type: application/plain');

        $curlcommands = json_decode($this->serializeddata, JSON_OBJECT_AS_ARRAY);

        foreach ($curlcommands as $curlcommand) {
            echo $curlcommand . "\n" . "\n";
        }
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

    private function get_indexed_webservice_records(): array {
        global $DB;

        // Get_records returns an object array where key for each object is the name of the webservice.
        // Use array_values to change key to the index of each object so that we can filter based on $selectedWebserviceIndices.
        $webservicesrecords = array_values($DB->get_records('external_functions', array(), ''));

        return $webservicesrecords;
    }

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
                    "raw" => $this->create_request_string($webservice, $paramsarray),
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
    public function test_export($selected) {
        header('Content-Disposition: attachment; filename=curl.txt');
        header('Content-Type: application/plain');


        echo $this->host . "\n" . "\n";

        // foreach ($this->selectedwebserviceindices as $index) {
        //     echo $index . "\n" . "\n";
        // }

        $webservices = $this->get_selected_webservice_objects();

        $curlstrings = [];
        $postmanitems = [];
        foreach ($webservices as $webservice) {

            $paramsarray = $this->get_formatted_param_array($webservice);

            $curlstrings[] = "curl " . $this->create_request_string($webservice, $paramsarray);
            $postmanitems[] = $this->create_postman_request_item($webservice, $paramsarray);
        }

        // foreach ($curlstrings as $curlstring) {
        //     echo print_r($curlstring) . "\n" . "\n";
        // }
        // foreach ($postmanitems as $item) {
        //     echo print_r($item) . "\n" . "\n";
        // }

        $postmancollection = $this->create_postman_collection($postmanitems);
        echo print_r($postmancollection);
    }

    /**
     * Exports data as Postman Collection in a json file.
     * Sets header to initiate download with filename and extension.
     */
    public function export_as_postman() {
        header('Content-Disposition: attachment; filename=postman.json');
        header('Content-Type: application/json');

        $postmancollection = json_decode($this->serializeddata, true);
        echo $this->serializeddata;

        // $prettyprintsingle = json_encode($unserializedjson[0], JSON_PRETTY_PRINT);
        // $prettyprintall = json_encode($unserializedjson, JSON_PRETTY_PRINT);
    }
}
