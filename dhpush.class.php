<?php

if (!defined('IN_DISCUZ')) {

    exit('Access Denied');
}

function sendPostRequest($url,$jsonData){
    $curl = curl_init($url);
    $request_headers = [
        'Accept:' . "application/json",
        'Content-type:' . "application/json"
    ];
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($jsonData));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    error_log(print_r(json_encode($jsonData), TRUE));
    // set a very low timeout to avoid blocking
    $json_response = curl_exec($curl);
    curl_close($curl);
    error_log(print_r($json_response, TRUE));
}

class plugin_dhpush {}

class plugin_dhpush_forum extends plugin_dhpush
{

    function __construct()
    { //全局函数定义
        global $_G;
        $config = $_G['cache']['plugin']['dhpush'];
        $this->groupid = $_G['groupid'];
        $this->allowPushGroupIds = unserialize($config["allowPushGroups"]);
        $this->pushToken = $config["pushToken"];
        $this->debug = $config["debug"];
    }



    function post_reply_output($params)
    {


        error_log(print_r($params, TRUE));
        // check with post reply succeed
        if ($params["message"] != "post_reply_succeed") {
            return;
        }

        error_log(print_r($_POST["reppid"], TRUE));

        $tid = $params['values']['tid'];
        $pid = $params['values']['pid'];
        $this->send_reply_push_information($tid, $pid);


    }

    function post_message($params){
        //error_log(print_r($params, TRUE));
    }

    function post_mobile_message($params){
        error_log(print_r($params, TRUE));
    }

    function sendreply_variables($variables){
        // will get like:
        //[tid] => 40
        //[pid] => 502

        error_log(print_r($variables, TRUE));
        //
        $tid = $variables['tid'];
        $pid = $variables['pid'];
        $this->send_reply_push_information($tid, $pid);


    }

    function send_reply_push_information($tid,$pid){
        global $_G;
        $SEND_URL_PREFIX = "https://dhpushservice.kidozh.com";
        $SEND_URL_PATH = "/v1/push/reply";
        if ($this->debug) {
            $SEND_URL_PREFIX = "http://localhost:9000";

        }

        $SEND_URL = $SEND_URL_PREFIX.$SEND_URL_PATH;
        $mForumPost = C::t("forum_post");
        $post = $mForumPost->fetch($tid, $pid);
        $senderUid = $post["authorid"];
        $senderName = $post["author"];
        $replyMessage = $post["message"];
        // remove all []
        $removedPaterrn = "/\[.*?\]/";
        $replyMessage = preg_replace($removedPaterrn,"",$replyMessage);

        if(strlen($replyMessage) > 400){
            $replyMessage = mb_substr($replyMessage,0,400);
            $replyMessage .= "...";
        }



        $mForumThread = C::t('forum_thread');

        $thread = $mForumThread->fetch($tid);
        //主题作者ID
        if($_POST["reppid"] != null){
            $repPost = $mForumPost->fetch($tid, $_POST["reppid"]);
            $authorId = $repPost["authorid"];
        }
        else{
            $authorId = $thread['authorid'];
        }


        $userResult = DB::fetch_first("SELECT groupid FROM " . DB::table("common_member") . " WHERE uid=" . $authorId);
        $receiverGroupId = $userResult["groupid"];
        //判断作者是否开启回贴通知
        //$mForumPostNotice = C::t('#post_notice#forum_post_notice');
        //$isNotice = $mForumPostNotice->getNoticeState($authorId);
        // allow notify
        if (true) {
//            $mCommonMember = C::t('common_member');
//            $author = $mCommonMember->fetch($authorId);
//            $email = $author['email'];
            $title = $thread["subject"];
            $siteURL = $_G["siteurl"];
            // check whether group id is not null and in the allowed group
            if ($receiverGroupId && in_array($receiverGroupId, $this->allowPushGroupIds)) {
                // look in the table whether the user in the push database
                $pushInfo = DB::fetch_all("SELECT token,channel,packageId FROM " . DB::table("dhpush_token") . " WHERE uid="
                    . $authorId." AND updateAt > ".strtotime("-1 week")." LIMIT 5");
                // start to push the information to device via firebase

                if($pushInfo != []){
                    $data = [
                        // belong to a reply
                        "site_url" => $siteURL,
                        "type" => "thread_reply",
                        "sender_name" => $senderName,
                        "sender_id" => $senderUid,
                        "message" => $replyMessage,
                        "title" => $title,
                        "tid" => $tid,
                        "pid" => $pid,
                        "uid" => $authorId,
                        "pushInfo"=> $pushInfo,

                    ];

                    $SEND_URL_WITH_PARAMTER = $SEND_URL."?token=".$this->pushToken;

                    sendPostRequest($SEND_URL_WITH_PARAMTER,$data);
                    error_log(print_r($data, TRUE));
                    error_log(print_r($SEND_URL, TRUE));
                }




            } else {
                // the user are not able to get a push
            }
        } else {
            // not to send a information

        }
    }


}

class mobileplugin_dhpush_forum extends plugin_dhpush_forum{
    public function post_message($params)
    {
        parent::post_message($params); // Inherit parents
    }

    public function post_mobile_message($params)
    {
        parent::post_mobile_message($params); // TODO: Change the autogenerated stub
    }
}

// for login notification

class plugin_dhpush_member extends plugin_dhpush{
    function logging_login_output($params){
        error_log(print_r($params, TRUE));
    }

    function logging_member($params){
        error_log(print_r($params, TRUE));
    }
}

class mobileplugin_dhpush_member extends  plugin_dhpush_member {}