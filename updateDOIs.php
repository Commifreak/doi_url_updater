<?php
if(php_sapi_name() != "cli")
	die();
?>

Welcome to DOI-Changer!



<?php


############################ Config
// Run with Prod = true if you are sure all is working!
$prod = false;

// DataCite Username ans password
$USER = "username";
$PASS = "password";

// Proxy. set to null if you are not using it!
// Format: IP/Hostname:Port
$PROXY = null;

// Whats the old and new domain?
$OLDDOMAIN = "http://my.old.domain";
$NEWDOMAIN = "https://my.new.domain.also.with.https";

// Should I ignore some DOIs? comma separated.
// Set to null if no DOIs should be ignored.
$IGNOREDOI = array('12.3456/DOIUPDATER');


############################# Main


$c = curl_init();
if(!is_null($PROXY)) {
	curl_setopt($c, CURLOPT_PROXY, $PROXY);
}
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_USERPWD, "$USER:$PASS");
curl_setopt($c, CURLOPT_URL, "https://mds.datacite.org/doi");

$out = curl_exec($c);
$info = curl_getinfo($c);
curl_close($c);

if(empty($out))
	die("Got no list!");

echo "Got List!".PHP_EOL;


$doiList = explode(PHP_EOL, $out);

echo "Got ".count($doiList)." DOI's!".PHP_EOL;

$c = curl_init();
$i=0;
foreach($doiList as $doi) {
	$i++;
	
	echo $i." / ".count($doiList).": =====================================> ".$doi." <===================================== ".PHP_EOL.PHP_EOL;
	
	$skipThisDOI = false;
	if(!is_null($IGNOREDOI)) {
		foreach($IGNOREDOI as $ignore) {
			if(strpos($doi, $ignore) !== false) {
				echo "This DOI (or a part of it) should be ignored! (".$doi." <=> ".$ignore."), skipping...".PHP_EOL;
				#sleep(5);
				$skipThisDOI = true;
			}
		}
	}
	if($skipThisDOI)
		continue;
	
	echo "Getting info ...   ";
	
	
	if(!is_null($PROXY)) {
		curl_setopt($c, CURLOPT_PROXY, $PROXY);
	}
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_USERPWD, "$USER:$PASS");
	curl_setopt($c, CURLOPT_URL, "https://mds.datacite.org/doi/$doi");

	$out = curl_exec($c);
	

	if(empty($out))
		die("Got no data!");

	echo "Got data!".PHP_EOL;
	
	if(strpos($out, $OLDDOMAIN) === false) {
		echo "This DOI has not the wanted OLD domain! (".$out.") Ignoring...".PHP_EOL;
		#sleep(5);
		continue;
	} else {
		
		echo "This DOI is candidate for updating the URL!".PHP_EOL;
		$doiNewUrl = str_replace($OLDDOMAIN, $NEWDOMAIN, $out);
		echo "* Updating the URL from ".$out." to ".$doiNewUrl." ...    ";
		
		if($prod) {
			$newDOIData = array(
				"doi" => $doi,
				"url" => $doiNewUrl
			);
			
			
			$c2 = curl_init();
			if(!is_null($PROXY)) {
				curl_setopt($c, CURLOPT_PROXY, $PROXY);
			}
			curl_setopt($c2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c2, CURLOPT_USERPWD, "$USER:$PASS");
			curl_setopt($c2, CURLOPT_POST, true);
			curl_setopt($c2, CURLOPT_POSTFIELDS, $newDOIData);
			curl_setopt($c2, CURLOPT_URL, "https://mds.datacite.org/doi");

			$out2 = curl_exec($c2);
			$info = curl_getinfo($c2);
			curl_close($c2);

			if($info["http_code"] != 201) {
				echo "Something went wrong! HTTP-Code is not 201!".PHP_EOL."===> ".$out2;
			} else {
				echo "Success!".PHP_EOL;
			}

		} else {
			echo PHP_EOL."TESTMODE! Not doing anything!".PHP_EOL;
		}
		
	}
	
	#var_dump($out.PHP_EOL);
	
	#if($i >= 2)
		#break;
	
		#echo PHP_EOL."=================================".PHP_EOL;
		echo PHP_EOL;
	
	
}
curl_close($c);

echo "End!".PHP_EOL;



?>
