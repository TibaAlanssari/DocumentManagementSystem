<?php 

//starting session
$username = "fso799";
$password = "$9W%%23Zf7!4xTh@yY";
$data = "username=$username&password=$password";
$ch = curl_init('https://cs4743.professorvaladez.com/api/create_session?username=fso799&password=$9W%%23Zf7!4xTh@yY');
curl_setopt($ch, CURLOPT_POST, 1);
#curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencode',
    'content-length: ' . strlen($data)));
$timeStart = microtime(true); #start timer
$result = curl_exec($ch); #executes our curl request
$timeEnd = microtime(true); #end timer
$executionTime = ($timeEnd - $timeStart) / 60; #dividing with 60 will give the execution time in seconds
curl_close($ch);
$cinfo = json_decode($result, true); #curl response payload array
if ($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created")
{
	$sid = $cinfo[2];
	#everytime we create a new connection we have to reset the data
	$data = "sid=$sid&uid=$username";
	#TODO:log this info
	echo "\r\nSession was created successfully!\r\n";
	echo "The session ID is\r\n";
	echo "$sid\r\n";
	echo "The execution time is\r\n";
	echo "$executionTime\r\n";

	//Query files
	$ch=curl_init('https://cs4743.professorvaladez.com/api/query_files?sid=' . $sid . '&uid=fso799');
	curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
	#curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencode', 
		'content-length: ' . strlen($data))); #sending length of content and content type everytime

	$timeStart = microtime(true); #capture start execution time of script
	$result = curl_exec($ch); #executes the given CURL session
	$timeEnd = microtime(true); #capture end execution time of script
	$executionTime = (($timeEnd - $timeStart)/60);  
	curl_close($ch); #close CURL session
	$cinfo = json_decode($result, true); #data we are getting is JSON but we want to decode it to turn the results into an array
	if($cinfo[0]=="Status: OK"){
		if ($cinfo[2] == "Action: None")
		{
			#TODO: log info
			echo "\r\nNo new files to import found\r\n";
			echo "SID: $sid\r\n";
			echo "Username: $username\r\n";
			echo "Query files execution time: $executionTime\r\n";
			echo "\r\n";

		}
		else
		{
			
			$tmp=explode(":",$cinfo[1]);
			$files=explode(",", $tmp[1]);
			echo "Number of new files to import found: ".count($files)."\r\n";
			echo "\r\n";
			
			echo "Querying files:\r\n";
			#TODO: log info
			foreach($files as $key=>$value)
			{
				echo $value."\r\n";
			}
			echo "Query files execution time: $executionTime\r\n";
			
			//Recieve files
			echo "Recieving files:";
			foreach($files as $key=>$value)
			{
			
				$tmp=explode("/", $value);
				$file=$tmp[4];
				echo 'explode at '.$tmp[4];
				echo "File: $file\r\n";
				$data="sid=$sid&uid=$username&fid=$file";
				$ch=curl_init('https://cs4743.professorvaladez.com/api/request_file?sid=' . $sid . '&uid=fso799&fid=' . $file);
				curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
				//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'content-type: application/x-www-form-urlencode', 
					'content-length: ' . strlen($data))); #sending length of content and content type everytime
				
				$timeStartRecieve = microtime(true); #capture start execution time of script
				$result = curl_exec($ch); #executes the given CURL session
				$timeEndRecieve = microtime(true); #capture end execution time of script
				$executionTimeRecieve = (($timeEndRecieve - $timeStartRecieve)/60);  
				$content=$result;
                $content = addslashes($content);

				$fp=fopen("/var/www/html/recieve/$file","wb");
				fwrite($fp,$content);
				fclose($fp);
				echo "\r\n$file written to file system\r\n";
				
				//include in database
				//include("functions.php");
				//$dblink=db_connect('docstorage');
				$hostname="localhost";
    			$username="webuser";
    			$password="(ymOZ@us-lluIX!B";
    			$db="docstorage";
    			$dblink=new mysqli($hostname,$username,$password,$db);
				if (mysqli_connect_errno())
				{
					die("Error connectiong to databse: ".mysqli_connect_errno());
				}
				
				//upload to database
				$path="/var/www/html/recieve/";
				$status = 'active';
				
				$uploadDate=date("Y-m-d H:i:s");
				$uploadBy="user@utsa.mail";
				$uploadDName=date("Y-m-d_H:i:s_");
				//$fileName=str_replace(" ", "_",$_FILES['userfile']['name']);
				//$fileName=$uploadDName.$file;
				//$docType='[a-zA-Z]+';
				$fileInfo = explode('-', $file);
				$loanNum = $fileInfo[0];
				$docType = $fileInfo[1];
				$cmeta = curl_getinfo($ch);
				//echo filesize($file);
				//$tmpName=$_FILES[$file]['tmp_name'];
				$fileSize = $cmeta['download_content_length'];
				//$fileSize=filesize($file);
				//$fileType=$_FILES[$file]['type'];
				//$path="/var/www/html/uploads/";
				//$fp=fopen($tmpName, 'r');
				//$content=fread($fp, filesize($tmpName));
				//fclose($fp);
				//$contentClean=addslashes($content);
                //$sql = "INSERT INTO `documents`(`loan_number`, `file_name`, `path`, `upload_by`, `upload_date`, `status`, `file_type`, `file_size`, `content`) VALUES ('','$file','[value-3]','[value-4]','[value-5]','[value-6]','[value-7]','[value-8]','[value-9]')";
				$sql="Insert into `documents` (`loan_number`,`file_name`,`path`,`upload_by`,`upload_date`,`status`,`file_type`,`file_size`,`content`) values ('$loanNum','$file','$path','$uploadBy','$uploadDate','$status','$docType','$fileSize','$content')";
				$dblink->query($sql) or
					die("Something went wrong with $sql<br>".$dblink->error);
				
				
			}
			echo "\r\n";
			echo "Recieve files execution time: $executionTimeRecieve\r\n";
		}
	}
	//Closing session
	$ch=curl_init('https://cs4743.professorvaladez.com/api/close_session?sid=' . $sid . '&username=fso799');
	curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencode', 
		'content-length: ' . strlen($data))); #sending length of content and content type everytime

	$timeStart = microtime(true); #capture start execution time of script
	$result = curl_exec($ch); #executes the given CURL session
	$timeEnd = microtime(true); #capture end execution time of script
	$executionTime = (($timeEnd - $timeStart)/60);  
	curl_close($ch); #close CURL session
	$cinfo = json_decode($result, true); #data we are getting is JSON but we want to decode it to turn the results into an array
	if ($cinfo[0] == "Status: OK")
	{
		#TODO: log later
		echo "Session was successfully closed!\r\n";
		echo "SID: $sid\r\n";
		echo "Close session execution time is $executionTime\r\n";
	}
	else #error happened closing session
	{
		#output errors
		#TODO: log this info later
		echo $cinfo[0];
		echo "\r\n";
		echo $cinfo[1];
		echo "\r\n";
		echo $cinfo[2];
		echo "\r\n";
	}	
}

#an error occurred - session was not successfully created
else
{
	#output errors
	#TODO: log info
	echo $cinfo[0];
	echo "\r\n";
	echo $cinfo[1];
	echo "\r\n";
	echo $cinfo[2];
	echo "\r\n";
	
}

//clearning session
if ($cinfo[0] == "Status: OK")
	{
	
		$data = "username=$username&password=$password";

		$ch=curl_init('https://cs4743.professorvaladez.com/api/clear_session?username=fso799&password=$9W%%23Zf7!4xTh@yY');
		curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'content-type: application/x-www-form-urlencode', 
			'content-length: ' . strlen($data))); #sending length of content and content type everytime

		$cinfo = json_decode($result, true); #data we are getting is JSON but we want to decode it to turn the results into an array
	if ($cinfo[0] == "Status: OK")
	{
		#TODO: log later
		echo "\r\n";
		echo "Session was successfully cleared!\r\n";
		
	}
	else #error happened closing session
	{
		#output errors
		#TODO: log this info later
		echo $cinfo[0];
		echo "\r\n";
		echo $cinfo[1];
		echo "\r\n";
		echo $cinfo[2];
		echo "\r\n";
	}
}
?>
