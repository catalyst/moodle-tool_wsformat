<?php

$serializedJson = $_GET['data-json'];
$unserializedJson = json_decode($serializedJson, true);

$prettyPrintSingle = json_encode($unserializedJson[0], JSON_PRETTY_PRINT);
$prettyPrintAll = json_encode($unserializedJson, JSON_PRETTY_PRINT);

header('Content-Disposition: attachment; filename=file.json');
header('Content-Type: application/json');
echo $prettyPrintAll;
