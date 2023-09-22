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

/**
 * Class for processing and exporting web service data.
 */
class export_webservices {

    /**
     * Stores export type.
     * @var string
     */
    private $exporttype = '';

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

    /**
     * Constructor function - assign instance variables.
     * @param string $type
     * @param string $host
     * @param string $serializeddata
     */
    public function __construct(string $type, string $host, string $serializeddata) {
        $this->exporttype = $type;
        $this->host = $host;
        $this->serializeddata = $serializeddata;
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

    /**
     * Exports data as Postman Collection in a json file.
     * Sets header to initiate download with filename and extension.
     */
    public function export_as_postman() {
        header('Content-Disposition: attachment; filename=postman.json');
        header('Content-Type: application/json');

        // $prettyprintsingle = json_encode($unserializedjson[0], JSON_PRETTY_PRINT);
        // $prettyprintall = json_encode($unserializedjson, JSON_PRETTY_PRINT);
    }
}
