<?php
$private_token=$_GET["token"];

$keys = ["name","username","state","created_at","location","last_sign_in_at","confirmed_at","last_activity_at","last_activity_on","current_sign_in_at","email"];

$row  =  "";
for($x = 0; $x < count($keys); $x++) {
	$row .=  FormatItem($keys[$x] );
}
$row .= "clarity_name";
echo $row;

$jsonString = CallAPI("GET","https://jl.githost.io/api/v4/users?private_token=$private_token&active=true&per_page=100&page=1");
$jsonObject = json_decode($jsonString);
WriteData($jsonObject, $keys);
$jsonString = CallAPI("GET","https://jl.githost.io/api/v4/users?private_token=$private_token&active=true&per_page=100&page=2");
$jsonObject = json_decode($jsonString);
WriteData($jsonObject, $keys);
$jsonString = CallAPI("GET","https://jl.githost.io/api/v4/users?private_token=$private_token&active=true&per_page=100&page=3");
$jsonObject = json_decode($jsonString);
WriteData($jsonObject, $keys);
$jsonString = CallAPI("GET","https://jl.githost.io/api/v4/users?private_token=$private_token&active=true&per_page=100&page=4");
$jsonObject = json_decode($jsonString);
WriteData($jsonObject, $keys);

function WriteData($jsonObject, $keys) {

	foreach($jsonObject as $userObject) {
		$row  =  "<br>";
		for($x = 0; $x < count($keys); $x++) {
			$item = $userObject -> $keys[$x];
			$row .= FormatItem($item);
			
			if ($keys[$x] == "email" ) {
				$row .= FormatItem( NameInEmailAddress($item) );
			}
			
		}
		echo rtrim($row,",");
	}
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
function FormatItem($item) {
    return  chr(34).$item.chr(34).","  ;
}
function NameInEmailAddress($email) {
//takes an email in format aaaa.bbbb.cccc@xyz and returns  "cccc, aaaa". Underscores or periods can be used to delimit user name. 
    $email1 = str_replace("_",".",$email);
    $emailParts = explode("@",$email1);
    $nameParts = explode(".",$emailParts[0]);
    $firstName = $nameParts[0];
    $lastName = $nameParts[count($nameParts)-1];
    return $lastName.", ".$firstName ;
}
?>
