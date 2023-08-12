<?php

namespace tool_api_test\output;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Shows tool_analytics models list.
 */
class index_page implements \renderable, \templatable {

    /**
     * pagedata
     */
    protected $numbers = array();


    public function __construct($numbers) {
      $this->numbers = $numbers;
    }

    /**
     * Exports the data.
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output): \stdClass {
        global $PAGE;
      $data = new \stdClass();
      return $data;
    }
}
