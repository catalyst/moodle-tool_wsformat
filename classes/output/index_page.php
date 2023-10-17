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
 * @package   tool_wsformat
 * @copyright 2023 Djarran Cotleanu, Jacqueline Mail, Zach Pregl
 * @author    Djarran Cotleanu, Jacqueline Mail, Zach Pregl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_wsformat\output;

/**
 * Class for processing data for index_page template.
 */
class index_page implements \renderable, \templatable {

    /**
     * Stores the selected webservice indices.
     *
     * @var array
     */
    protected $selectedwebserviceindices = [];

    /**
     * Stores the index of the selected external service.
     *
     * @var array
     */
    protected $serviceindex = null;

    /**
     * Stores the user id of the logged in user.
     *
     * @var array
     */
    protected $userid = null;

    /**
     * Constructor function - assign instance variable.
     *
     * @param array $webserviceindicies Indicies of the selected web services to export.
     * @param int $serviceindex Index of the selected external service.
     * @param int $userid ID of the logged in user.
     */
    public function __construct(array $webserviceindicies, int $serviceindex = null, int $userid) {
        $this->selectedwebserviceindices = $webserviceindicies;
        $this->serviceindex = $serviceindex;
        $this->userid = $userid;
    }

    /**
     * Exports the data for the index_page.mustache template
     *
     * @param  \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output): object {
        global $CFG;

        // Return empty object if no selected webservices.
        if (empty($this->selectedwebserviceindices)) {
            return (object) [];
        }

        $hostaddress = $CFG->wwwroot;
        echo $hostaddress;

        $exportwebservices = new \tool_wsformat\export_webservices($this->selectedwebserviceindices);

        $webservicesexport = [];
        foreach ($exportwebservices->webservices as $webservice) {
            $paramsarray = $exportwebservices->get_formatted_param_array($webservice);
            $curlstring  = 'curl ' . $exportwebservices->create_request_string($webservice, $paramsarray);

            $webservicesexport[] = (object) [
                'name'        => $webservice->name,
                'description' => $webservice->description,
                'curl'        => $curlstring,
            ];
        }

        $data = (object) [
            'formdata'        => $webservicesexport,
            'serviceindex'    => $this->serviceindex,
            'items_selected'  => true,
            'selectedindexes' => json_encode($this->selectedwebserviceindices),
        ];

        return $data;
    }
}
