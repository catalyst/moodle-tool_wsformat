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
class index_page implements \renderable, \templatable
{

    /**
     * Stores the selected webservice indices.
     *
     * @var array
     */
    protected $selectedwebserviceindices = [];


    /**
     * Constructor function - assign instance variable.
     *
     * @param array $indicies
     */
    public function __construct(array $indicies)
    {
        $this->selectedwebserviceindices = $indicies;

    }//end __construct()


    /**
     * Exports the data for the index_page.mustache template
     *
     * @param  \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output): object
    {
        // Return empty object if no selected webservices.
        if (empty($this->selectedwebserviceindices)) {
            return (object) [];
        }

        $exportwebservices = new \tool_wsformat\export_webservices('http', $this->selectedwebserviceindices);

        $webservicesexport = [];
        foreach ($exportwebservices->webservices as $webservice) {
            $paramsarray = $exportwebservices->get_formatted_param_array($webservice);
            $curlstring  = 'curl '.$exportwebservices->create_request_string($webservice, $paramsarray);

            $webservicesexport[] = (object) [
                'name'        => $webservice->name,
                'description' => $webservice->description,
                'curl'        => $curlstring,
            ];
        }

        $data = (object) [
            'formdata'        => $webservicesexport,
            'items_selected'  => true,
            'selectedindexes' => json_encode($this->selectedwebserviceindices),
        ];

        return $data;

    }//end export_for_template()


}//end class
