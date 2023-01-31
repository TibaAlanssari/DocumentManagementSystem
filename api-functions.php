<?php

// ? APIs will always give you in order the status, message, and action
// ? API functions below -----------------------------------------------

include('db-functions.php');

function create_session($username, $password)
{
    $data = "username=$username&password=$password";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/create_session');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    $timeStart = microtime(true); //start timer
    $result = curl_exec($ch); // executes our curl request
    $timeEnd = microtime(true); //end timer
    $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    curl_close($ch);
    $cinfo = json_decode($result, true); // curl response payload array
    if ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
    {
//        log_api_call("create_session", "OK", $cinfo[2], $executionTime, $cinfo[1], $cinfo[2]);
        return $cinfo[2]; // returns session id
    }
    else
    {
//        log_api_call("create_session", "ERROR", $cinfo[2], $executionTime, $cinfo[1], "ERROR");
        return null;
    }
}

function close_session($session_id, $username)
{
    $data = "sid=$session_id&uid=$username";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/close_session');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    $timeStart = microtime(true); //start timer
    $result = curl_exec($ch); // executes our curl request
    $timeEnd = microtime(true); //end timer
    $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    curl_close($ch);
    $cinfo = json_decode($result, true); // curl response payload array
    if ($cinfo[0] == "Status: OK")
    {
//        log_api_call("close_session", "OK", $cinfo[2], $executionTime, $cinfo[1], $session_id);
        return;
    }
    else
    {
//        log_api_call("close_session", "ERROR", $cinfo[2], $executionTime, $cinfo[1], $session_id);
        return;
    }
}

function request_files($session_id, $username)
{
    $data = "sid=$session_id&uid=$username";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/query_files');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    $timeStart = microtime(true); //start timer
    $result = curl_exec($ch); // executes our curl request
    $timeEnd = microtime(true); //end timer
    $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    curl_close($ch);
    $cinfo = json_decode($result, true); // curl response payload array
    if ($cinfo[0] == "Status: OK")
    {
        if ($cinfo[2] == "Action: None")
        {
//            log_api_call("query_files", "OK", "None", $executionTime, $cinfo[1], $session_id);
            echo "No new files found";
            echo "\r\n";
            return false;
        }
        else
        {
            $tmp = explode(" ", $cinfo[1]);
            $files = explode(",", $tmp[1]);
//            log_api_call("query_files", "OK", $cinfo[2], $executionTime, $files, $session_id);
            return $files;
        }
    }
    else
    {
//        log_api_call("query_files", "ERROR", $cinfo[2], $executionTime, $cinfo[1], $session_id);
        return null;
    }
}

function download_document($session_id, $username, $file)
{
    $tmp = explode("/", $file);
    $fileName = $tmp[4];

    // builds API curl request
    echo"File: $fileName\r\n";
    $data = "sid=$session_id&uid=$username&fid=$fileName";
    echo"Data: $data\r\n";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/request_file');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    $content = null;
    try {
        $timeStart = microtime(true); //start timer
        $result = curl_exec($ch); // executes our curl request
        $content = $result;
        $timeEnd = microtime(true); //end timer
        $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    } catch (Exception $e) {
        $api_id = log_api_call("request_file", "ERROR", $fileName, $executionTime, "None", $session_id);
//        log_incomplete_request($api_id, $file, $e->getMessage());
        curl_close($ch);
        return;
    }
    $cinfo = json_decode($result, true); // curl response payload array
//    $api_id = log_api_call("request_file", "OK", $fileName, $executionTime, "None", $session_id);
    if (file_exists("/receive/$fileName") || file_exists("/uploads/$fileName")) {    // checks if file is already in our system
//        log_incomplete_request($api_id, $file, "File already exists");
        return;
    }
    $cinfo = json_decode($result, true); // curl response payload array
    $cmeta = curl_getinfo($ch); // curl response payload array
    if (curl_errno($ch)) {
//        log_incomplete_request($api_id, $file, curl_error($ch));
        curl_close($ch);
        return;
    }
    curl_close($ch);
    $fileSize = $cmeta['download_content_length'];
    if ($fileSize == 0) {
//        log_malicious_files($file, "empty file", $cmeta['primary_ip']);
        return;
    }
//    $fp = fopen("/var/www/html/receive/$fileName", "wb");
//    fwrite($fp, $result);
//    fclose($fp);
    $metaData = explode('-', $fileName);
    $account = $metaData[0];
    $docType = $metaData[1];
    $fileTypeTmp = explode(".", $fileName);
    $fileType = $fileTypeTmp[count($fileTypeTmp)-1];

    $loan_number = intval($account);
    $datetime = date_create()->format('Y-m-d H:i:s');
    $dblink = db_connect("docsStorage");
    $content = addslashes($content);
    $path = "/var/www/html/upload/$fileName";
    $uploadBy = "user@mail.com";
    $uploadDate = date_create()->format('Y-m-d H:i:s');
    $status = "active";
    $sql="Insert into `documents` (`loan_number`,`file_name`,`path`,`upload_by`,`upload_date`,`status`,`file_type`,`file_size`,`content`) values ('$loan_number','$fileName','$path','$uploadBy','$uploadDate','$status','$docType','$fileSize','$content')";
    $result = $dblink->query($sql);
    $dblink->close();
//    store_document($fileName, $account, $tag, "/var/www/html/upload/$fileName", "user@mail.com", "active", $fileType, $fileSize, $content);
}

function request_loans($session_id, $username)
{
    $data = "sid=$session_id&uid=$username";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/request_loans');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    $timeStart = microtime(true); //start timer
    $result = curl_exec($ch); // executes our curl request
    $timeEnd = microtime(true); //end timer
    $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    curl_close($ch);
    $cinfo = json_decode($result, true); // curl response payload array
    if ($cinfo[0] == "Status: OK")
    {
        if ($cinfo[2] == "Action: None")
        {
//            log_api_call("request_loans", "OK", "None", $executionTime, $cinfo[1], $session_id);
            echo "No loans found";
            echo "\r\n";
            return false;
        }
        else
        {
            echo '$cinof[1]: ' . $cinfo[1] . "\r";
            $tmp = explode(" ", $cinfo[1]);
            $loans = explode(",", $tmp[1]);
            array_walk($loans, function(&$value, $key) {
                $value = str_replace('"', '', $value);
                $value = str_replace('[', '', $value);
                $value = str_replace(']', '', $value);
            });
            foreach ($loans as $loan) {
                echo "Loan: $loan\r\n";
            }
//            log_api_call("request_loans", "OK", $cinfo[2], $executionTime, $loans, $session_id);
            return $loans;
        }
    }
    else
    {
//        log_api_call("request_loans", "ERROR", $cinfo[2], $executionTime, $cinfo[1], $session_id);
        echo "Request Loans Error: $cinfo[1]\r\n";
        return null;
    }
}

function query_docs_by_loan($session_id, $username, $loan_number)
{
    $data = "sid=$session_id&uid=$username&lid=$loan_number";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/request_file_by_loan');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    $timeStart = microtime(true); //start timer
    $result = curl_exec($ch); // executes our curl request
    $timeEnd = microtime(true); //end timer
    $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    curl_close($ch);
    $cinfo = json_decode($result, true); // curl response payload array
    if ($cinfo[0] == "Status: OK")
    {
        if ($cinfo[2] == "Action: None")
        {
//            log_api_call("query_files", "OK", "None", $executionTime, $cinfo[1], $session_id);
            echo "No new files found";
            echo "\r\n";
            return false;
        }
        else
        {
            $tmp = explode(" ", $cinfo[1]);
            $files = explode(",", $tmp[1]);

            array_walk($files, function(&$value, $key) {
                $value = str_replace('"', '', $value);
                $value = str_replace('[', '', $value);
                $value = str_replace(']', '', $value);
            });

            echo "$loan_number: \r\n";
            foreach ($files as $file) {
                echo "File: $file\r\n";
            }
            echo "\r\n";
//            log_api_call("query_files", "OK", $cinfo[2], $executionTime, $files, $session_id);
            return $files;
        }
    }
    else
    {
//        log_api_call("query_files", "ERROR", $cinfo[2], $executionTime, $cinfo[1], $session_id);
        echo "Error: $cinfo[1]\r\n";
        return null;
    }
}

function download_doc_filename($session_id, $username, $file) {
    $fileName = $file;

    // builds API curl request
    echo"File: $fileName\r\n";
    $data = "sid=$session_id&uid=$username&fid=$fileName";
    $ch = curl_init('https://cs4743.professorvaladez.com/api/request_file');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($data))
    );
    try {
        $timeStart = microtime(true); //start timer
        $result = curl_exec($ch); // executes our curl request
        $timeEnd = microtime(true); //end timer
        $executionTime = ($timeEnd - $timeStart) / 60; //dividing with 60 will give the execution time in seconds
    } catch (Exception $e) {
        $api_id = log_api_call("request_file", "ERROR", $fileName, $executionTime, "None", $session_id);
        log_incomplete_request($api_id, $file, $e->getMessage());
        curl_close($ch);
        return;
    }
    $cinfo = json_decode($result, true); // curl response payload array
    if ($cinfo[0] != "Status: OK") {
        echo "ERROR\r\n";
        echo "$cinfo[1]\r\n";
        return;
    }
    $api_id = log_api_call("request_file", "OK", $fileName, $executionTime, "None", $session_id);
    if (file_exists("/receive/$fileName") || file_exists("/uploads/$fileName")) {    // checks if file is already in our system
        log_incomplete_request($api_id, $file, "File already exists");
        return;
    }
    $cinfo = json_decode($result, true); // curl response payload array
    $cmeta = curl_getinfo($ch); // curl response payload array
    if (curl_errno($ch)) {
        log_incomplete_request($api_id, $file, curl_error($ch));
        curl_close($ch);
        return;
    }
    curl_close($ch);
    $fileSize = $cmeta['download_content_length'];
    if ($fileSize == 0) {
        log_malicious_files($file, "empty file", $cmeta['primary_ip']);
        return;
    }
    $fp = fopen("/var/www/html/receive/$fileName", "wb");
    fwrite($fp, $result);
    fclose($fp);
    $metaData = explode('-', $fileName);
    $account = $metaData[0];
    $tag = $metaData[1];
    $fileTypeTmp = explode(".", $fileName);
    $fileType = $fileTypeTmp[count($fileTypeTmp)-1];
    store_document($fileName, $account, $tag, "/var/www/html/upload/$fileName", "juan.valadez@utsa.edu", "active", $fileType, $fileSize);
}

?>