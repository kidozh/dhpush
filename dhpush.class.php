<?php

if (!defined('IN_DISCUZ')) {

    exit('Access Denied');
}

function sendPostRequest($url,$jsonData){

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($jsonData));
    // set a very low timeout to avoid blocking
    curl_exec($curl);
    curl_close($curl);
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
    }

    function post_reply_output($params)
    {
        global $_G;
        $DEBUG = true;
        $SEND_URL_PREFIX = "https://dhpushservice.kidozh.com/";
        $SEND_URL_PATH = "v1/push/reply/";
        if ($DEBUG) {
            $SEND_URL_PREFIX = "http://localhost:8888/";

        }

        $SEND_URL = $SEND_URL_PREFIX.$SEND_URL_PATH;

        error_log(print_r($params, TRUE));
        // check with post reply succeed
        if ($params["message"] != "post_reply_succeed") {
            return;
        }

        error_log(print_r($_POST["reppid"], TRUE));
        $fid = $params['values']['fid'];
        $tid = $params['values']['tid'];

        $pid = $params['values']['pid'];


        $mForumPost = C::t("forum_post");
        $post = $mForumPost->fetch($tid, $pid);
        $senderUid = $post["authorid"];
        $senderName = $post["author"];
        $replyMessage = $post["message"];

        $replyMessage = mb_substr($replyMessage,0,50);

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
////            $email = $author['email'];
            $title = $thread["subject"];
            $siteURL = $_G["siteurl"];
            // check whether group id is not null and in the allowed group
            if ($receiverGroupId && in_array($receiverGroupId, $this->allowPushGroupIds)) {
                // look in the table whether the user in the push database
                $pushInfo = DB::fetch_all("SELECT token,channel,packageId FROM " . DB::table("dhpush_token") . " WHERE uid=" . $authorId);
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
                        "fid" => $fid,
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

    function post_message($params){
        //error_log(print_r($params, TRUE));
    }

    function post_mobile_message($params){
        error_log(print_r($params, TRUE));
    }
}

class mobileplugin_dhpush_forum extends plugin_dhpush_forum{
    public function post_message($params)
    {
        parent::post_message($params); // Inherit parents
    }
}