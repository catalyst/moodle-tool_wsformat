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
 * Language strings
 *
 * @package          tool_wsformat
 * @copyright        2023 Djarran Cotleanu
 * @author           Djarran Cotleanu
 * @license          http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_wsformat\export_webservices;

require('../../../config.php');
$hostaddress = $CFG->wwwroot;

require_login();

define('EXPORT_CURL', 'curl');
define('EXPORT_POSTMAN', 'postman');

$selected = required_param('selected', PARAM_TEXT);
$exporttype = required_param('export-type', PARAM_TEXT);

$selectedwebserviceindices = json_decode($selected);

$export = new export_webservices($hostaddress, $selectedwebserviceindices);

switch ($exporttype) {
    case EXPORT_CURL:
        $export->export_as_curl();
        break;
    case EXPORT_POSTMAN:
        $export->export_as_postman();
        break;
}
