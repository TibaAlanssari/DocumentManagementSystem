<?php

include("functions.php");

class loan_number
{
    public $loan_number;
    public $doc_types = array();
    public $missing_doc_types = array();
    public $has_all_doc_types = false;

    function __construct($loan_number)
    {
        $this->loan_number = $loan_number;
    }
    function add_doc_type($doc_type)
    {
        if (!in_array($doc_type, $this->doc_types)) {
            $this->doc_types[] = $doc_type;
        }
    }
    function check_for_all_doc_types($doc_types)
    {
        $this->has_all_documents = true;
        foreach ($doc_types as $doc_type) {
            if (!in_array($doc_type, $this->doc_types)) {
                $this->has_all_documents = false;
            }
        }
    }
}

$credit = $closing = $title = $financial = $personal = $internal = $legal = $other = 0;

$dblink = db_connect("docstorage");
$sql = "SELECT file_type FROM `documents`";
$result = $dblink->query($sql) or
    die("Error: " . $dblink->error);

while ($row = $result->fetch_assoc()) {
    if ($row['file_type'] == "Credit") {
        $credit++;
    } elseif ($row['file_type'] == "Closing") {
        $closing++;
    } elseif ($row['file_type'] == "Title") {
        $title++;
    } elseif ($row['file_type'] == "Financial") {
        $financial++;
    } elseif ($row['file_type'] == "Personal") {
        $personal++;
    } elseif ($row['file_type'] == "Internal") {
        $internal++;
    } elseif ($row['file_type'] == "Legal") {
        $legal++;
    } elseif ($row['file_type'] == "Other") {
        $other++;
    }
}

$doc_types = array("Credit", "Closing", "Title", "Financial", "Personal", "Internal", "Legal", "Other");
$doc_counts = array($credit, $closing, $title, $financial, $personal, $internal, $legal, $other);

$sql = "SELECT DISTINCT loan_number FROM `documents`";

$result = $dblink->query($sql) or
    die("Error: " . $dblink->error);

$loan_numbers = array();

while ($row = $result->fetch_assoc()) {
    $loan_numbers[] = new loan_number($row['loan_number']);
}

foreach ($loan_numbers as $loan_number) {
    $sql = "SELECT file_type FROM `documents` WHERE loan_number = " . $loan_number->loan_number;
    $result = $dblink->query($sql) or
    die("Error: " . $dblink->error);

    while ($row = $result->fetch_assoc()) {
        $loan_number->add_doc_type($row['file_type']);
    }

//    $loan_number->check_for_missing_doc_types($doc_types);
    $loan_number->check_for_all_doc_types($doc_types);
}

echo "List of loan numbers that are missing at least one doc_type";
echo "<BR>";
foreach ($loan_numbers as $loan_number) {
    if (!$loan_number->has_all_documents) {
        $missing_docs = array_diff($doc_types, $loan_number->doc_types);
        echo $loan_number->loan_number . " is missing " . implode(", ", $missing_docs);
        echo "<br />";
    }
}
echo "<br />";
echo "List of loan numbers that have all doc_types";
foreach ($loan_numbers as $loan_number) {
    if ($loan_number->has_all_doc_types) {
        echo $loan_number->loan_number . " has all doc_types.";
        echo "<br />";
    }
}
echo "<br />";
echo "List of total number of documents by doc_type";
echo "<br />";
foreach ($doc_types as $key => $doc_type) {
    echo $doc_type . ": " . $doc_counts[$key];
    echo "<br />";
}
$dblink->close();
?>