<?php

include('api-functions.php');

$username = "fso799";
$password = "$9W%%23Zf7!4xTh@yY";
$sessionId = create_session($username, $password);
echo "Session ID: $sessionId \r\n";

$loans= request_loans($sessionId, $username);

$dblink = db_connect("DocStorage");
//$sql = "SELECT loan_number FROM `loan_numbers`";
//$result = $dblink->query($sql) or
//    die("Error: " . $dblink->error);

//$known_loan_numbers = array();
//while ($row = $result->fetch_assoc()) {
//    $known_loan_numbers[] = $row['loan_number'];
//}

//$files = query_docs_by_loan($sessionId, $username, $known_loan_numbers[1]);
//download_document($sessionId, $username, "/a/a/a/$files[0]");

foreach ($loans as $loan) {
//    if (!in_array($loan, $known_loan_numbers)) {
    addLoanNumber($loan);
//    }
    $files = query_docs_by_loan($sessionId, $username, $loan);
    foreach ($files as $file) {
        download_document($sessionId, $username, "/a/a/a/$file");
    }
}

$dblink->close();
close_session($sessionId, $username);
?>