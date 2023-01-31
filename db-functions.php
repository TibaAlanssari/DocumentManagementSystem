<?php

function db_connect($db)
{
    $hostname="localhost";
    $username="webuser";
    $password="(ymOZ@us-lluIX!B";
    //$db="docStorage";
    $dblink=new mysqli($hostname,$username,$password,"docstorage");
    if (mysqli_connect_errno())
    {
        die("Error connecting to database: ".mysqli_connect_error());
    }
    return $dblink;
}

function store_document($name, $account, $tag, $path, $upload_by, $status, $type, $size, $content) {
    $loan_number = intval($account);
    echo "Loan Number: " . $loan_number . "\r\n";
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("docsStorage");
    $sql = "INSERT INTO `documents`(`loan_number`, `file_name`, `path`, `upload_by`, `upload_date`, `status`, `file_type`, `file_size`, `content`) VALUES ('$loan_number', '$name', '$path', '$upload_by', '$datetime', '$status', '$type', '$size', '$content')";
    $result = $dblink->query($sql);
    $dblink->close();
}

function log_api_call($end_point, $status, $action, $execution_time, $msg, $sid) {
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("Logs");
    $api_sql = "INSERT INTO `API Logs`(`end_point`, `status`, `action`, `time`, `execution_time`, `sid`) VALUES ('$end_point','$status','$action','$datetime','$execution_time','$sid')";
    $api_id =  null;

    $result = $dblink->query($api_sql);
    if ($result) {
        $api_id = $dblink->insert_id;
        if (($end_point == "query_files" || $end_point == "request_loans") && $action != "None")  {
            foreach ($msg as $key=>$value) {
                if($end_point == "query_files") {
                    $tmp = explode("/", $value);
                    $file = $tmp[4];
                } else {
                    $file = $value;
                }
                $msg_sql = "INSERT INTO `API Messages`(`api_id`, `msg`) VALUES ('$api_id','$file')";
                $result = $dblink->query($msg_sql);
            }
        } elseif ($end_point != "request_file"){
            $msg_sql = "INSERT INTO `API Messages`(`api_id`, `msg`) VALUES ('$api_id','$msg')";
            $result = $dblink->query($msg_sql);
        }
    }
    $dblink->close();
    return $api_id;
}

function log_error($quick_reference, $trace) {
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("Logs");
    $sql = "INSERT INTO `Error Logs`(`qref`, `trace`, `dtg`) VALUES ('$quick_reference','$trace','$datetime')";
    $result = $dblink->query($sql);
    $dblink->close();
}

function log_bad_request($path) {
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("Logs");
    $sql = "INSERT INTO `Bad Requests`(`path`, `dtg`) VALUES ('$path','$datetime')";
    $result = $dblink->query($sql);
    $dblink->close();
}

function log_incomplete_request($api_id, $path, $trace) {
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("Logs");
    $sql = "INSERT INTO `Incomplete Requests`(`api_id`, `path`, `trace`, `dtg`) VALUES ('$api_id','$path','$trace','$datetime')";
    $result = $dblink->query($sql);
    $dblink->close();
}

function log_malicious_files($name, $reason, $ip) {
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("Logs");
    $sql = "INSERT INTO `Malicious Files`(`name`, `reason`, `ip`, `dtg`) VALUES ('$name', '$reason', '$ip', '$datetime')";
    $result = $dblink->query($sql);
    $dblink->close();
}

function addLoanNumber($loan_number) {
    $dblink = db_connect("DocStorage");
    $sql = "INSERT INTO `loan_numbers` (`loan_number`) VALUES ('$loan_number')";
    $result = $dblink->query($sql) or
    die("Error: " . $dblink->error);
    $dblink->close();
}

?>