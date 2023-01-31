
<link href="assets/css/report.css" rel="stylesheet"/>
<?php

$page="report.php";
echo '<div class="title" style="text-align:center"><h1>Reporting and Analytics</h1></div>';
 
//connect to database
include("functions.php");
$dblink=db_connect("docstorage");

# Report 1 - Total Number of Unique Loan Numbers
$sql="SELECT DISTINCT `loan_number` FROM `documents`";
$result=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink->error);
$loanArray=array();
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	$loanArray[]=$data['loan_number'];
}

echo '<div class="subtitles"><h3>Total Number of Unique Loan Numbers:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';
echo '<div class="subtitles"style="text-align:center";> Total Number: ' . count($loanArray) . '</div>';

echo '<br>';
echo '<div class="subtitles" style="text-align:center;"> Loan Numbers:</div>';
echo '<br>';
foreach($loanArray as $key=>$value){
	#echo '<div>Loan Number: '.$value.'</div>';
	$sql="Select count(`loan_number`) from `documents` where `loan_number` like '%$value%'";
	$rst=$dblink->query($sql) or
		die("Something went wrong with: $sql<br>".$dblink->error);
	$tmp=$rst->fetch_array(MYSQLI_NUM);
	echo '<div style="text-align:center;">' . $value .'</div>';
}
echo '<br>';


#Report 2 - Total Size of All Documents Recieved From The API and The Average Size of All Documents Across All Loans
echo '<div class="subtitles"><h3>Total Size of All Documents Recieved:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';
$sql = "SELECT SUM(`file_size`) as sum FROM `documents`";
$result=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink->error);
$val = $result -> fetch_array();
	$fileSizeSum = $val['sum'];

echo '<div style="text-align:center";>' . $fileSizeSum .' Bytes</div>';
echo '<br>';

echo '<div class="subtitles"><h3>Average Size of All Documents Across All Loans:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';

$sql = "SELECT count(`file_size`) as entries FROM `documents`";
$res=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink->error);
$value = $res -> fetch_array();
	$fileSizeEntries = $value['entries'];

echo '<div style="text-align:center";>' . $fileSizeSum/$fileSizeEntries .' Bytes</div>';
echo '<br>';

#Report 3 - For each loan number, the total number of documents recieved and the average number of documents across all loan numbers. Compare each loan number to the average and state if it is above or below average
echo '<div class="subtitles"><h3>For Each Loan Number - Total Number of Documents Recieved:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';

foreach($loanArray as $key=>$value){
	#echo '<div>Loan Number: '.$value.'</div>';
	$sql="Select count(`loan_number`) from `documents` where `loan_number` like '%$value%'";
	$rst=$dblink->query($sql) or
		die("Something went wrong with: $sql<br>".$dblink->error);
	$tmp=$rst->fetch_array(MYSQLI_NUM);
	echo '<div style="text-align:center;">' . $value .' has '.$tmp[0].'</div>';
	
	$loanNumsDocSum = $loanNumsDocSum + $tmp[0];
}

$avgNumofDocs = $loanNumsDocSum/count($loanArray);

echo "<br>";
echo '<div class="subtitles"><h3>Avg Number of Documents: ' . $avgNumofDocs . '</h3></div>';
echo "<br>";

#Compare each loan number to the average and state if it is above or below average
echo '<div class="subtitles"><h3>Loan Numbers Compared to Average:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';

foreach($loanArray as $key=>$value){
$sql="Select count(`loan_number`) from `documents` where `loan_number` like '%$value%'";
	$rst=$dblink->query($sql) or
		die("Something went wrong with: $sql<br>".$dblink->error);
	$tmp=$rst->fetch_array(MYSQLI_NUM);
	if ($tmp[0] > $avgNumofDocs){
		$aboveOrBelowAvg = "above avg";
	}
	elseif ($tmp[0] < $avgNumofDocs){
		$aboveOrBelowAvg = "below avg";
	}
	echo '<div style="text-align:center";>' . $value .' is ' . $aboveOrBelowAvg .'</div>';
}

#Report 4 - A complete loan is one that has at least one of the following documents: credit, closing, title, financial, personal, internal, legal, other
#A list of all loan numbers that are missing at least one of these documents and which document(s) is missing (100 pts)
#A list of all loan numbers that have all documents (100 pts)
#List the total number of each document received across all loan numbers (100 pts

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

echo '<br>';
echo '<div class="subtitles"><h3>Loan Numbers Missing Documents:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';
foreach ($loan_numbers as $loan_number) {
    if (!$loan_number->has_all_documents) {
        $missing_docs = array_diff($doc_types, $loan_number->doc_types);
        echo '<div style="text-align:center";>' . $loan_number->loan_number . " is missing " . implode(", ", $missing_docs) . '</div>';
        echo "<br />";
    }
}

echo '<br>';
echo '<div class="subtitles"><h3>Complete Loan Numbers:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';
foreach ($loan_numbers as $loan_number) {
    if ($loan_number->has_all_doc_types) {
        echo '<div style="text-align:center";>' . $loan_number->loan_number . " has all doc_types." . '</div>';
        echo "<br />";
    }
}
echo "<div style=text-align:center;>None</div>";

echo '<br>';
echo '<div class="subtitles"><h3>Total Number of Each Document:</h3></div>';
echo '<hr style="width:50%;text-align:center;margin-center:0">';
foreach ($doc_types as $key => $doc_type) {
    echo  '<div style="text-align:center";>' . $doc_type . ": " . $doc_counts[$key] . '</div>';
    echo "<br />";
}
$dblink->close();

?>