<?php
header("Content-Type: text/plain");
$private_token=$_GET["token"];
$pages=isset($_GET['pages']) ? $_GET['pages'] : 4 ;
$keys = ["name","username","state","created_at","location","bio","last_sign_in_at","confirmed_at","last_activity_on","current_sign_in_at","email","two_factor_enabled"];
$row  =  "";
$row .= ConvertArrayToCSV($keys); 
$row .= "clarity_name";
echo $row;
$url = "https://jl.githost.io/api/v4/users?private_token=$private_token&per_page=100&page=";
//json returned from Gitlab is in format:
//{[{key1:value1, key2:value2,...keyN:valueN},...{key1:value1, key2:value2,...keyN:valueN}]}
// $keys[] contains the key for the key/value pairs we're interested in.
for($page = 1; $page <= $pages; $page++) {
	$jsonString = CallAPI("GET",$url.$page);
	$jsonObject = json_decode($jsonString);
	if (count($jsonObject)==0) {
		echo "<br/>Did not retrieve data from page ".$page." onwards.";break;
	}
	WriteData($jsonObject, $keys);
}
function WriteData($jsonObject, $keys) {
	foreach($jsonObject as $userObject) {
		$row  =  "\n";
		$row .= ConvertObjectToCSV($userObject, $keys);
		$email = $userObject -> email;
		$row .= FormatItem( GetNameInEmailAddress($email) ); 
		echo rtrim($row,",");
	}
	
} 
function ConvertArrayToCSV($keys) {
	$csvText  =  "";
	for($x = 0; $x < count($keys); $x++) {
		$csvText .=  FormatItem($keys[$x] );
	}
	return $csvText;
}
function ConvertObjectToCSV($object, $keys) {
	$csvText="";
	for($x = 0; $x < count($keys); $x++) {
		$item = $object -> {$keys[$x]};
		$csvText .= FormatItem($item);
	}
	return $csvText;
}
function FormatItem($item) {
    return  "\"$item\","  ;
}
function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
function GetNameInEmailAddress($email) {
//takes an email in format aaaa.bbbb.cccc@xyz and returns  "cccc, aaaa". Underscores or periods can be used to delimit user name. 
    $email1 = str_replace("_",".",$email);
    $emailParts = explode("@",$email1);
    $nameParts = explode(".",$emailParts[0]);
    $firstName = $nameParts[0];
    $lastName = $nameParts[count($nameParts)-1];
    return $lastName.", ".$firstName ;
}
?>
