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
	
	$lid = [23186709,"01369472","21780349","40126839","92031786","23084967","12703849","84793126","41876392","28469731","29103764","91734602","19670832","21096387","13748600","01376842","48627139","90864231","87946031","02961734","68749300","72463908","98263704","47132089","10768329","43678901","27391086","36820049","87094213","60012783","18296403","64210039","40019638","67932048","12847936","46087213","91274036","96104782","27934160","34278106","69370812","24371968","49610238","82716039","47102396","29340817","03179284","61347892","46397180","23407168","93271604","21789406","93068147","81274300","89346102","84690123","62713490","98401672","06748932","74269013","46730298","63274098","19862743","80276391","62170948","82910367","97264138","23678491","41038279","81400293","26804913","81673024","92386410","98207463","73180946","48261970","61730942","69280034","47830126","10283469","94286701","76390481","34920067","93618472","38700291","64001798","82471960","98213640","31672400","26708491","76924803","80714962","24163079","93241806","18467930","82906417","79620834","32698701","64123870","98076413","94376218","63849071","79061428","48296100","17082693","32640987","21308679","48160029","63871029","19340628","69234781","86120347","09741326","36729418","28341906","10293487","81963402","20897134","00961437","19274360","30249671","08239174","17349062","32986014","70684392","83207194","86714003","79384002","39816042","14628900","08679132","89340167","87362049"];
	
//Recieve files
echo "Recieving files:";

foreach($lid as $value)	{
	//$data="sid=$sid&uid=$username&lid=$lid";
	$ch=curl_init('https://cs4743.professorvaladez.com/api/request_file?sid=' . $sid . '&uid=fso799&lid=' . $lid[$value]);
	curl_setopt($ch, CURLOPT_POST, 1); #opening curl connection post
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); #transfer variables and dont spit them out to terminal
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencode', 
		'content-length: ' . strlen($data))); #sending length of content and content type everytime
	curl_close($ch); #close CURL session
	$cinfo = json_decode($result, true); #data we are getting is JSON but we want to decode it to turn the results 
	echo $cinfo;
	
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
