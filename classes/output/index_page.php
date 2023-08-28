<?php

namespace tool_api_test\output;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Shows tool_analytics models list.
 */
class index_page implements \renderable, \templatable
{
  protected $selectedWebserviceIndices = array(); // Indicies (0, 1, ...) from external_functions table of selected webservices from autocomplete form

  public function __construct($indicies)
  {
    $this->selectedWebserviceIndices = $indicies;
  }

  /**
   * Exports the data for the index_page.mustache template
   *
   * @param \renderer_base $output
   * @return \stdClass
   */
  public function export_for_template(\renderer_base $output): stdClass
  {
    global $DB;

    // Return empty object if no selected webservices
    if (empty($this->selectedWebserviceIndices)) {
      return new stdClass();
    }

    // get_records returns an object array where key for each object is the name of the webservice.  
    // Use array_values to change key to the index of each object so that we can filter based on $selectedWebserviceIndices
    $webservicesRecords = array_values($DB->get_records('external_functions', array(), '', 'name'));

    $filteredRecords = []; // Array to store the filtered records
    foreach ($this->selectedWebserviceIndices as $index) {
      $filteredRecords[] = $webservicesRecords[$index];
    }

    // $data object will be passed to index_page template and useable as mustache tags
    $data = new stdClass();
    $data->formdata = $filteredRecords;
    $data->items_selected = true;
    $data->test = json_encode($filteredRecords);

    return $data;
  }
}
