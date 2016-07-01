<?php
include_once "data.php";
include_once "bib.php";
function getSpiderWebData($dimensions)
{
    $data = array();
    foreach ($dimensions as $dimension => $subdimensions) {
        foreach ($subdimensions as $subdimension => $element) {
            $dimensionName = "$dimension-$subdimension";

            for ($level = 1; $level <= 4; $level++) {
                if (!array_key_exists($data[$level][$dimension], $subdimension)) {
                    $data[$level][$dimension][$subdimension]['count'] = 0;
                    $data[$level][$dimension][$subdimension]['selected'] = 0;
                }
                foreach ($element as $elementName => $content) {
                    if ($level == $content["level"]) {
                        $data[$level][$dimension][$subdimension]['count']++;
                        if(elementIsSelected($elementName)) {
                            $data[$level][$dimension][$subdimension]['selected']++;
                        }
                    }

                }
            }
        }
    }
    return $data;
}




function fwritecsv2($filePointer, $dataArray, $delimiter = ",", $enclosure = "\"")
{
    // Write a line to a file
    // $filePointer = the file resource to write to
    // $dataArray = the data to write out
    // $delimeter = the field separator

    // Build the string
    $string = "";

    // for each array element, which represents a line in the csv file...
    foreach ($dataArray as $line) {

        // No leading delimiter
        $writeDelimiter = FALSE;

        foreach ($line as $dataElement) {
            // Replaces a double quote with two double quotes
            $dataElement = str_replace("\"", "\"\"", $dataElement);

            // Adds a delimiter before each field (except the first)
            if ($writeDelimiter) $string .= $delimiter;

            // Encloses each field with $enclosure and adds it to the string
            $string .= $enclosure . $dataElement . $enclosure;

            // Delimiters are used every time except the first.
            $writeDelimiter = TRUE;
        }
        // Append new line
        $string .= "\n";

    } // end foreach($dataArray as $line)
    // Write the string to the file
    fwrite($filePointer, $string);
}

//var_dump( getSpiderWebData($dimensions));exit;

function deleteElement(&$data, $elementName)
{
    $count = 0;
    foreach ($data as $element) {
        if ($elementName == $element["element"]) {
            unset($data[$count]);
        }
        $count++;
    }
    return false;
}

if ($_REQUEST['dimension'] == null) {
    echo json_encode(getSpiderWebData($dimensions));
} else {
    $csvFile = 'selectedData.csv';
    $csv = getCsv();
    $dimension = $_REQUEST['dimension'];
    $subdimension = $_REQUEST['subdimension'];
    $element = $_REQUEST['element'];

    if (elementIsSelected($element)) {
        deleteElement($csv, $element);
    } else {
        $newEntry['dimension'] = $dimension;
        $newEntry['subdimension'] = $subdimension;
        $newEntry['element'] = $element;
        $csv[] = $newEntry;
    }


    $keys = array_keys($csv[0]);
    $csv = array_merge(array($keys), $csv);
    $fp = fopen($csvFile, 'w');
    fwritecsv2($fp, $csv, ",");
    fclose($fp);
}