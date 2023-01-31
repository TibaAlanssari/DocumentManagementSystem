<?php 
$username="fso799";
$password="$9W%%23Zf7!4xTh@yY";
$data="username=$username&password=$password";

#curl command
$ch=curl_init('https://cs4743.professorvaladez.com/api/create_session?username=fso799&password=$9W%%23Zf7!4xTh@yY');
	curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencode', 
		'content-length: ' . strlen($data))); #sending length of content and content type everytime
$time_start = microtime(true); #capture start execution time of script
$result = curl_exec($ch); #executes the given CURL session
$time_end = microtime(true); #capture end execution time of script
$execution_time = (($time_end - $time_start)/60);  
curl_close($ch); #close CURL session
$dataArray = json_decode($result, true); #data we are getting is JSON but we want to decode it to turn the results into an array

#two statuses from curl commands (okay and error)

#we check status to make sure that the session was successfully created
if ($dataArray[0] == "Status: OK" && $dataArray[1]=="MSG: Session Created")
{
	
	$sid=$dataArray[2];
	#everytime we create a new connection we have to reset your data
	$data="sid=$sid&username=$username";
	#TODO:log this info later
	echo "\r\nSession was created successfully!\r\n";
	echo "The session ID is\r\n";
	echo "$sid\r\n";
	echo "The execution time is\r\n";
	echo "$execution_time\r\n";
	
	#closing session
	$ch=curl_init('https://cs4743.professorvaladez.com/api/close_session?sid=' . $sid . '&username=fso799');
		curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'content-type: application/x-www-form-urlencode', 
			'content-length: ' . strlen($data))); #sending length of content and content type everytime

	$time_start = microtime(true); #capture start execution time of script
	$result = curl_exec($ch); #executes the given CURL session
	$time_end = microtime(true); #capture end execution time of script
	$execution_time = (($time_end - $time_start)/60);  
	curl_close($ch); #close CURL session
	$dataArray = json_decode($result, true); #data we are getting is JSON but we want to decode it to turn the results into an array
	if ($dataArray[0] == "Status: OK")
	{
		#TODO: log later
		echo "Session was successfully closed!\r\n";
		echo "SID: $sid\r\n";
		echo "Close session execution time is $execution_time\r\n";
		
	}
	else #error happened closing session
	{
		#output errors
		#TODO: log this info later
		echo $dataArray[0];
		echo "\r\n";
		echo $dataArray[1];
		echo "\r\n";
		echo $dataArray[2];
		echo "\r\n";
	}
}

#an error occurred - session was not successfully created
else
{
	#output errors
	#TODO: log this info later
	echo $dataArray[0];
	echo "\r\n";
	echo $dataArray[1];
	echo "\r\n";
	echo $dataArray[2];
	echo "\r\n";
	
}

?>