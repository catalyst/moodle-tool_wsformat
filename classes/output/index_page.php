<?php

namespace tool_api_test\output;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Shows tool_analytics models list.
 */
class index_page implements \renderable, \templatable
{
  /**
   * pagedata
   */
  protected $numbers = array();


  public function __construct($numbers)
  {
    $this->numbers = $numbers;
  }

  /**
   * Exports the data.
   *
   * @param \renderer_base $output
   * @return \stdClass
   */
  public function export_for_template(\renderer_base $output)
  {
    global $PAGE;
    global $DB;

    $webservicesRecords = $DB->get_records('external_functions', array(), '', 'name, classname, methodname');

    $array = [];
    foreach ($webservicesRecords as $key => $value) {
      $temp = new \stdClass();
      $temp->name = $key;
      $array[] = $temp;
    }
    $data = new \stdClass();
    $data->array = $array;
    return $data;
  }
}
