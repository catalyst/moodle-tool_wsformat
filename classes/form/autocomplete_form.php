<?php

namespace tool_api_test\form;

require_once($CFG->libdir . '/formslib.php');

use moodleform;

class autocomplete_form extends moodleform
{

    public function definition()
    {
        global $DB;

        $mform = $this->_form;


        $webservicesObject = $DB->get_records('external_functions', array(), '');

        $webserviceNames = array();

        // filter returned object
        foreach ($webservicesObject as $key => $webservice) {
            $webserviceNames[] = $webservice->name;
        }
        // Documentation: https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#autocomplete
        $mform->addElement('autocomplete', 'webservice_form', 'Webservice:', $webserviceNames, array(
            'minchars' => 1,
            'multiple' => true,
            'matchcontains' => true,
            'showprogress' => true,
            'source' => function ($request, $response) use ($DB) {
                $webfunctions = $DB->get_record_sql('SELECT classname FROM mdl_external_functions WHERE classname LIKE ?', array('%' . $request . '%'));
                $options = [];
                foreach ($webfunctions as $ws) {
                    $options[] = $ws->classname;
                }
                $response->content = $options;
                $response->status = '200 OK';
            }
        ));
        $mform->addElement('submit', 'submit', 'Save');

        // if ($mform->is_submitted()){

        // }
    }
}
