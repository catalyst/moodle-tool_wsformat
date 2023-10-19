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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/webservice/lib.php');
global $CFG;

use core_external\external_api;
use context_system;
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
     * The service token of the selected external service.
     * Is null upon class instantiation.
     *
     * @var string
     */
    private $servicetoken = null;

    /**
     * Constructor function - assign instance variables.
     *
     * @param array  $selectedwebserviceindices
     * @param int  $selectedserviceindex
     */
    public function __construct(array $selectedwebserviceindices, int $selectedserviceindex = null) {
        global $CFG;

        $this->host        = $CFG->wwwroot;
        $this->webservices = $this->get_selected_webservice_objects($selectedwebserviceindices);
        $this->handle_external_service($selectedserviceindex);
    }

    /**
     * Handles the acquiring of the token for a given external service, or creation of a token
     * if it doesn't yet exist. 
     * 
     * Adds webservices to the plugin's external service.
     *
     * @param  int|null $selectedserviceindex The index of the selected external service.
     */
    private function handle_external_service(int | null $selectedserviceindex) {
        global $DB;

        if ($selectedserviceindex === null) {
            return;
        }

        $externalservicesarray = array_values($DB->get_records('external_services'));
        $externalserviceid = $externalservicesarray[$selectedserviceindex]->id;

        $token = $this->get_service_token($externalserviceid);

        // If no token exists, create one
        if ($token === false) {
            $this->servicetoken = $this->create_token($externalservicesarray[$selectedserviceindex]);
        } else {
            $this->servicetoken = $token->token;
        }

        // Add functions to service if service belongs to wsformat plugin
        if ($externalservicesarray[$selectedserviceindex]->shortname === 'wsformat_plugin') {
            foreach ($this->webservices as $webservice) {
                $this->add_function_to_service($webservice->name, $externalserviceid);
            }
        }

    }

    /**
     * Creates a token for an external service
     *
     * @param  object $externalserviceobject The external service object returned from the database
     * @return string created token
     */
    private function create_token(object $externalserviceobejct): string {
        global $USER;

        $token = \core_external\util::generate_token(
            EXTERNAL_TOKEN_PERMANENT,
            $externalserviceobejct,
            $USER->id,
            context_system::instance(),
            0,
            0
        );

        return $token;
    }

    /**
     * Adds function (webservice) to the external service if not yet added.
     *
     * @param  string $webservicename Webservice name
     * @param  int $externalserviceid External service id
     */
    private function add_function_to_service(string $webservicename, int $externalserviceid) {
        $webservicemanager = new \webservice();

        // Add webservice to external service if not already added.
        if (!$webservicemanager->service_function_exists(
            $webservicename,
            $externalserviceid
        )) {
            $webservicemanager->add_external_function_to_service(
                $webservicename,
                $externalserviceid
            );
        }
    }

    /**
     * Returns the first token for a given external service
     *
     * @param  string $externalserviceid The id of the external service to get a token for.
     * @return object|false External service object or false if no tokens exist.
     */
    private function get_service_token(string $externalserviceid): object | false {
        global $DB;

        $sql = "SELECT
                    t.token, s.name
                FROM
                    {external_tokens} t, {external_services} s
                WHERE
                    s.id=? AND s.id = t.externalserviceid";

        // Only handling the use case where only one token exists for the service.
        $token = $DB->get_records_sql($sql, array($externalserviceid));

        // Reset returns the first element of an array or false if array is empty.
        return reset($token);
    }

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
            echo $curlstring . PHP_EOL . PHP_EOL;
        }
    }

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
    }

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
    }


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
    }


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
            }
            return $paramstring . $type . $brakeline;
        }
    }

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
    }

    /**
     * Create a request string for cURL.
     *
     * @param  object $webservice  The object.
     * @param  array  $paramsarray array of parameters for the request.
     * @return string The generated string.
     */
    public function create_request_string(object $webservice, array $paramsarray): string {
        $baseurl = '{{BASE_URL}}';

        $token = $this->servicetoken !== null ? $this->servicetoken : '{{WS_TOKEN}}';

        $functionname = $webservice->name;

        $curlstring = $this->host . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction='
            . $functionname . '&moodlewsrestformat=json';

        // Add params into curlString.
        foreach ($paramsarray as $params) {
            $curlstring = $curlstring . '&' . $params;
        }
        return $curlstring;
    }

    /**
     * Creates a Postman collection object for given Postman items.
     *
     * @param array $postmanitems An array of Postman request item objects.
     * @return object The created Postman collection object.
     */
    private function create_postman_collection(array $postmanitems): object {
        $token = $this->servicetoken !== null ? $this->servicetoken : '{{WS_TOKEN}}';

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
                    'value' => $this->host,
                    'type'  => 'string',
                ],
                [
                    'key'   => 'WSTOKEN',
                    'value' => $token,
                    'type'  => 'string',
                ],
            ],
            'auth'     => [
                'type'   => 'apikey',
                'apikey' => [

                    [
                        'key'   => 'value',
                        'value' => $token,
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
    }

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
                    'host'  => [$this->host],
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
                            'value' => $webservice->name,
                        ],
                        ...$keyvalpairs,
                    ],
                ],
                'description' => $webservice->description,
            ],
            'response' => [],
        ];
        return $object;
    }
}
