<?php
include "url_functions.php";
ini_set("memory_limit","512M");
define("SYS_ADMIN_EMAIL_ADDRESS", "email@domain");


if(!isset($argv[1]) || !isset($argv[2])){
	print "Error - Incorrect arguments, usage example: \nphp crawl.php http://starturl.com/  report_emailed_to [another [another [etc]]]\n";
}

for($i=2; $i < count($argv); $i++){
	$emails[] = $argv[$i];
}

//this is the page to start on and scope for the host
$url = $argv[1];
$temp = parse_url($url);
	if(!isset($temp['path']))
		$url .= "/";

//this is the host that will be used to restrict the search to the current scope
$global_host = $temp['host'];

//this counter will help us keep track of our location in the stack
$stackPointer = 0;
//Set the end of the array as false
$stack[-1]['child'] = false;
//This is where you add the starting url to the stack
$stack[$stackPointer]['parent'] = $url;
$stack[$stackPointer++]['child'] = $url;
$visited = array();
$count = 0;
/**
 *This counter tracks the number of consecutive redirects on a server
 *early bugs found a site that redirected infinitely.  This counter
 *is simply to fix that bug.
 */
$redundancyCounter = 0;
/**
 * links to exclude from being search or reported
 * here they will be added to the visited list
 */
$exclude = fopen("./exclude.txt", "r");
while (!feof($exclude)){
	$visited[trim(fgets($exclude))] = true;
}

/**
 * This is the search and check algorithm
 * It cycle as long as there is an item on the stack
 */

while($url = $stack[--$stackPointer]['child']) {
	$parentUrl = $stack[$stackPointer]['parent'];
	$url = trim($url);
	$urlStatus = get_url_status($url);
	$parsedUrl = parse_url($url);
	//Status 200 is an indication that the site was reachable
	if ($urlStatus['status'] == 200) {
		//The reduncdancy counter is reset because a valid site was reached
		$redundancyCounter = 0;
		//The url is added to the success array
		$success[] = $url;
		//If the URL is not in scope, dont get the links off of the page
		if($global_host != $parsedUrl['host'])
			continue;
		$links = get_page_links($url);

		foreach($links as $link) {
			$link = resolve_url($url, $link);
			//if a url has not been visited, add it to the stack as a <parent,child> pair
			if(!isset($visited[$link])){
				$visited[$link] = true;
				$stack[$stackPointer]['parent'] = $url;
				$stack[$stackPointer++]['child'] = $link;
			}
		}
	} else {
		//If the website did not return a 200 OK status, check for a Location redirect
		$newLocation = $urlStatus['location'];
		if($redundancyCounter++ > 20)//20 is the number of redirects to allow
			$newLocation = false;
		if($newLocation){
			$newLocation = resolve_url($url, $newLocation);
			//add new location to the array
			if(!isset($visited[$newLocation])){
				$visited[$newLocation] = true;
				$stack[$stackPointer]['parent'] = $url;
				$stack[$stackPointer++]['child'] = $newLocation;
			}
		} else {
			$redundancyCounter = 0;
			if(!isset($parsedUrl['host']) && $global_host != $parsedUrl['host'])
				continue;
			$error[$parentUrl][] = $url;
		}
	}
}

$errors = get_reports($error);
print "\nSending emails!";
/**
 *Send Reports via Email
 *
 */
foreach($emails as $email_address) {
	print "\nSending to: $email_address";
	$from = "From: System Admin<".SYS_ADMIN_EMAIL_ADDRESS.">";
	send_report($email_address, $global_host, $errors, $from);
}
print "\nDone!\n";
?>
