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
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Zach Pregl
 * @author    Djarran Cotleanu, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat;

use core_external\external_api;

use core_external\external_multiple_structure;
use core_external\external_single_structure;

/**
 * Class for processing and exporting web service data.
 */
class export_webservices {

    /**
     * Stores the host address of the server.
     *
     * @var string
     */
    private $host = '';

    /**
     * An array to hold webservice objects.
     *
     * @var array
     */
    public $webservices = [];


    /**
     * Constructor function - assign instance variables.
     *
     * @param string $host
     * @param array  $selectedwebserviceindices
     */
    public function __construct(string $host, array $selectedwebserviceindices) {
        $this->host        = $host;
        $this->webservices = $this->get_selected_webservice_objects($selectedwebserviceindices);
    } //end __construct()


    /**
     * Exports data as cURL commands in a text file.
     * Sets header to initiate download with filename and extension.
     */
    public function export_as_curl(): void {
        header('Content-Disposition: attachment; filename=curl.txt');
        header('Content-Type: application/plain');

        $curlstrings = [];
        foreach ($this->webservices as $webservice) {
            $paramsarray = $this->get_formatted_param_array($webservice);

            $curlstrings[] = 'curl ' . $this->create_request_string($webservice, $paramsarray);
        }

        foreach ($curlstrings as $curlstring) {
            echo $curlstring . "\n" . "\n";
        }
    } //end export_as_curl()


    /**
     * Exports data as Postman Collection in a json file.
     * Sets header to initiate download with filename and extension.
     */
    public function export_as_postman(): void {
        header('Content-Disposition: attachment; filename=postman.json');
        header('Content-Type: application/json');

        $postmanitems = [];
        foreach ($this->webservices as $webservice) {
            $paramsarray = $this->get_formatted_param_array($webservice);

            $postmanitems[] = $this->create_postman_request_item($webservice, $paramsarray);
        }

        $postmancollection = $this->create_postman_collection($postmanitems);
        $beautifiedjson    = json_encode($postmancollection, JSON_PRETTY_PRINT);
        echo $beautifiedjson;
    } //end export_as_postman()


    /**
     * Retrieves an array of webservice objects based on provided indices.
     *
     * @param  array $selectedwebserviceindices An array of indices to grab the webservice objects.
     * @return array An array of webservice objects.
     */
    private function get_selected_webservice_objects(array $selectedwebserviceindices): array {
        $webservicesrecords = $this->get_indexed_webservice_records();

        $webservices = [];
        foreach ($selectedwebserviceindices as $index) {
            $webservice    = $webservicesrecords[$index];
            $webservices[] = external_api::external_function_info($webservice);
        }

        return $webservices;
    } //end get_selected_webservice_objects()


    /**
     * Retrieves indexed webservice records from the database.
     *
     * @return array An array of the indexed webservices.
     */
    private function get_indexed_webservice_records(): array {
        global $DB;

        // Get_records returns an object array where key for each object is the name of the webservice.
        // Use array_values to change key to the index of each object so that we can filter based on $selectedWebserviceIndices.
        $webservicesrecords = array_values($DB->get_records('external_functions', [], ''));

        return $webservicesrecords;
    } //end get_indexed_webservice_records()


    /**
     * Generates HTML for REST parameter description.
     *
     * @param  object $paramdescription Description object for the parameter.
     * @param  string $paramstring      Parameter string to be formatted.
     * @return mixed HTML formatted parameter description.
     */
    public function rest_param_description_html(object $paramdescription, string $paramstring): mixed {
        $brakeline = <<<EOF


        EOF;

        // Description object is a list.
        if ($paramdescription instanceof external_multiple_structure) {
            $paramstring = $paramstring . '[0]';
            $return      = $this->rest_param_description_html($paramdescription->content, $paramstring);
            return $return;
        } else if ($paramdescription instanceof external_single_structure) {
            // Description object is an object.
            $singlestructuredesc = '';
            $initialparamstring  = $paramstring;
            foreach ($paramdescription->keys as $attributname => $attribut) {
                $paramstring          = $initialparamstring . '[' . $attributname . ']';
                $singlestructuredesc .= $this->rest_param_description_html(
                    $paramdescription->keys[$attributname],
                    $paramstring
                );
            }

            return $singlestructuredesc;
        } else {
            // Description object is a primary type (string, integer).
            $paramstring = $paramstring . '=';
            $type        = '';

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
            } //end switch

            return $paramstring . $type . $brakeline;
        } //end if

    } //end rest_param_description_html()


    /**
     * Generates an array of formatted parameters for a given webservice.
     *
     * @param  object $webservice The webservice object.
     * @return array An array of formatted parameters.
     */
    public function get_formatted_param_array(object $webservice): array {
        $paramobjectarray = $webservice->parameters_desc->keys;

        // Using the code from renderer.php.
        $formattedparamsarray = [];

        foreach ($paramobjectarray as $paramname => $paramdesc) {
            $filteredparams = $this->rest_param_description_html($paramdesc, $paramname);

            $formatted = explode(PHP_EOL, $filteredparams);

            array_pop($formatted);

            for ($i = 0; $i <= (count($formatted) - 1); $i++) {
                array_push($formattedparamsarray, $formatted[$i]);
            }
        }

        return $formattedparamsarray;
    } //end get_formatted_param_array()


    /**
     * Create a request string for cURL.
     *
     * @param  object $webservice  The object.
     * @param  array  $paramsarray array of parameters for the request.
     * @return string The generated string.
     */
    public function create_request_string(object $webservice, array $paramsarray): string {
        $baseurl = '{{BASE_URL}}';
        $wstoken = '{{WS_TOKEN}}';

        $functionname = $webservice->name;

        $curlstring = $baseurl . '/webservice/rest/server.php?wstoken=' . $wstoken . '&wsfunction='
            . $functionname . '&moodlewsrestformat=json';

        // Add params into curlString.
        foreach ($paramsarray as $params) {
            $curlstring = $curlstring . '&' . $params;
        }

        return $curlstring;
    } //end create_request_string()


    /**
     * Creates a Postman collection object for given Postman items.
     *
     * @param array $postmanitems An array of Postman request item objects.
     * @return object The created Postman collection object.
     */
    private function create_postman_collection(array $postmanitems): object {
        $collection = (object) [
            'info'     => [
                'name'        => 'My Collection',
                'description' => 'Postman Collection',
                'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item'     => [...$postmanitems],
            'variable' => [
                [
                    'key'   => 'BASE_URL',
                    'value' => 'http://moodle.localhost',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'WSTOKEN',
                    'value' => '{{WSTOKEN}}',
                    'type'  => 'string',
                ],

            ],
            'auth'     => [
                'type'   => 'apikey',
                'apikey' => [

                    [
                        'key'   => 'value',
                        'value' => '{{WSTOKEN}}',
                        'type'  => 'string',
                    ],
                    [
                        'key'   => 'key',
                        'value' => 'wstoken',
                        'type'  => 'string',
                    ],
                    [
                        'key'   => 'in',
                        'value' => 'query',
                        'type'  => 'string',
                    ],

                ],
            ],

        ];

        return $collection;
    } //end create_postman_collection()


    /**
     * Creates a Postman request item object for a given webservice and parameter array.
     *
     * @param  object $webservice  The webservice object.
     * @param  array  $paramsarray An array of parameters for the request.
     * @return object The created Postman request item object.
     */
    private function create_postman_request_item(object $webservice, array $paramsarray): object {
        $paramstring = implode(',', $paramsarray);
        $parampairs  = explode(',', $paramstring);
        $keyvalpairs = [];

        if (!empty($paramstring)) {
            foreach ($parampairs as $parampair) {
                // Split each pair by = to separate key and value.
                $paramparts = explode('=', $parampair);

                // Ensure we have both key and value before assigning.
                if (count($paramparts) === 2) {
                    $keyvaluepairs[$paramparts[0]] = $paramparts[1];
                }

                $keyvalpair = [
                    'key'   => $paramparts[0],
                    'value' => $paramparts[1],
                ];

                $keyvalpairs[] = $keyvalpair;
            }
        }

        $object = (object) [
            'name'     => $webservice->name,
            'request'  => [
                'method'      => 'GET',
                'header'      => [],
                'url'         => [
                    'raw'   => $this->create_request_string($webservice, $paramsarray),
                    'host'  => ['{{BASE_URL}}'],
                    'path'  => [
                        'webservice',
                        'rest',
                        'server.php',
                    ],
                    'query' => [
                        [
                            'key'   => 'moodlewsrestformat',
                            'value' => 'json',
                        ],
                        [
                            'key'   => 'wsfunction',
                            'value' => 'core_webservice_get_site_info',
                        ],
                        ...$keyvalpairs,
                    ],
                ],
                'description' => $webservice->description,
            ],
            'response' => [],
        ];

        return $object;
    } //end create_postman_request_item()


}//end class
