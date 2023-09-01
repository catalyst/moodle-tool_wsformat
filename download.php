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
 * @package          tool_api_test
 * @copyright        2023 Djarran Cotleanu
 * @author           Djarran Cotleanu
 * @license          http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_login();

$serializedjson = required_param('data-json', PARAM_TEXT);
$unserializedjson = json_decode($serializedjson, true);

$prettyprintsingle = json_encode($unserializedjson[0], JSON_PRETTY_PRINT);
$prettyprintall = json_encode($unserializedjson, JSON_PRETTY_PRINT);

header('Content-Disposition: attachment; filename=file.json');
header('Content-Type: application/json');
echo $prettyprintall;
