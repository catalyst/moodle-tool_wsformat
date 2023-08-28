<?php

namespace tool_api_test\form;

require_once($CFG->libdir . '/formslib.php');

use moodleform;

class autocomplete_form extends moodleform
{

    public function definition()
    {
        global $DB;

        $webserviceNames = $this->getWebserviceNameArray();

        $mform = $this->_form;

        $options = [
            'minchars' => 2, // 
            'noselectionstring' => 'No webservices selected',
            'multiple' => true,
            'placeholder' => 'Search webservices...',
        ];

        // Documentation: https://docs.moodle.org/dev/lib/formslib.php_Form_Definition#autocomplete
        $mform->addElement('autocomplete', 'webservice_form', 'Webservices:', $webserviceNames, $options); // (type of form, name of form, title shown when rendered, array of results, options array)

        $mform->addElement('submit', 'submit', 'Update Selection'); // Will submit the form and cause a rerender of the page if no redirect
    }

    public function getWebserviceNameArray(): array
    {
        global $DB;
        $webservicesObject = $DB->get_records('external_functions', array(), '');

        $webserviceNames = array();

        // filter returned object
        foreach ($webservicesObject as $key => $webservice) {
            $webserviceNames[] = $webservice->name;
        }

        return $webserviceNames;
    }
}
