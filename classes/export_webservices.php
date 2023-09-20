<?php

namespace tool_wsformat;


class export_webservices {
    private $exporttype = '';
    private $host = '';
    private $serializeddata = '';

    public function __construct(string $type, string $host, string $serializeddata) {
        $this->exporttype = $type;
        $this->host = $host;
        $this->serializeddata = $serializeddata;
    }

    public function export_as_curl() {
        header('Content-Disposition: attachment; filename=curl.txt');
        header('Content-Type: application/plain');

        $curlcommands = json_decode($this->serializeddata, JSON_OBJECT_AS_ARRAY);

        foreach ($curlcommands as $curlcommand) {
            echo $curlcommand . "\n" . "\n";
        }
    }

    public function export_as_postman() {
        header('Content-Disposition: attachment; filename=postman.json');
        header('Content-Type: application/json');

        // $prettyprintsingle = json_encode($unserializedjson[0], JSON_PRETTY_PRINT);
        // $prettyprintall = json_encode($unserializedjson, JSON_PRETTY_PRINT);
    }

    public function example_echo() {
        header('Content-Disposition: attachment; filename=curl.txt');
        header('Content-Type: application/plain');
        echo 'shit';
    }
}
