<?php

if (!defined('IN_DISCUZ')) {

    exit('Access Denied');
}
header('Content-Type:text/json;charset=utf-8');

// return a correct header for recognizing
//header('Content-Type: application/json');

// ensure global is enabled
global $_G;



if(!$_G["uid"]){
    $result = [
         "result" => "not_logined"
    ];
    echo json_encode($result);
}
else{
    // user is logined
    //read config from cache for faster speed
    $config = $_G['cache']['plugin']['dhpush'];
    $uid = $_G["uid"];
    $userGroupid = $_G['groupid'];
    $allowPushGroupIds = unserialize($config["allowPushGroups"]);
    
    
    // check if user's group in the allowable group list
    if($userGroupid && in_array($userGroupid, $allowPushGroupIds)){
        // group verification successful
        
        $maxDeviceNumber = $config["maxDeviceNumber"];
        if($maxDeviceNumber && is_int($maxDeviceNumber) && $maxDeviceNumber < 6){

        }
        else{
            $maxDeviceNumber = 5;
        }

        switch ($_SERVER["REQUEST_METHOD"]){
            // To upload a token to the server
            case "POST":{
                // add token to database
                // check whether form hash is correct
                $formhash = $_POST["formhash"];
                if($formhash && $formhash == formhash()){
                    // check whether it exist in the database

                    $deviceName = $_POST["deviceName"];
                    $token = addslashes($_POST["token"]);
                    $username = $_G["username"];
                    $channel = addslashes($_POST["channel"]);
                    $packageId = addslashes($_POST["packageId"]);
                    $updateAt = time();
                    $allowPush = true;
                    $insertTokenObj = [
                        "uid" => $uid,
                        "username" => $username,
                        "token" => $token,
                        "deviceName" => $deviceName,
                        "updateAt" => $updateAt,
                        "channel" => $channel,
                        "packageId" => $packageId
                    ];

                    $tokenExistResult = DB::fetch_first("SELECT token FROM ".DB::table("dhpush_token")." WHERE uid=".$uid." AND token='". $token."'");
                    if($tokenExistResult.length == 0){
                        // insert the token information to database, return id and replace
                        DB::insert("dhpush_token", $insertTokenObj, true, true);
                        $result = [
                            "result" => "success",
                            "formhash" => formhash(),
                            "data" => $insertTokenObj
                        ];
                    }

                    // then sending a message via FCM
                    error_log(print_r($result, TRUE));


                }
                else{
                    $result = [
                        "result" => "invalid_token",
                    ];
                }
                echo json_encode($result);


                break;
            }
            // GET to get all tokens
            case "GET":{
                // get all tokens from database only allow 5 tokens
                $tokenResult = DB::fetch_all("SELECT id, uid, username, token, allowPush, deviceName, packageId,channel, updateAt FROM ".DB::table("dhpush_token")." WHERE uid=".$uid." ORDER BY updateAt DESC LIMIT ".$maxDeviceNumber);

                $result = [
                    "result" => "success",
                    "list" => $tokenResult,
                    "maxToken" => $maxDeviceNumber,
                    "formhash" => formhash()
                ];
                echo json_encode($result);

                break;
            }
        }
    }
    else{
        // not allowed group
        $result = [
            "result" => "group_not_allowed",
       ];
       echo json_encode($result);
    }
}