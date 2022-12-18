<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}


class table_dhpush extends discuz_table{

    public function __construct(){
        $this->_table = DB::table("dhpush_token");
        $this->_pk = "";
        parent::__construct();
    }

    public function fetch_all_by_uid($uid) {
        return DB::fetch_all("SELECT * FROM %t WHERE uid=%d  ORDER BY updateAt DESC LIMIT 5", array($this->_table, $uid));
    }

    public function delete_by_uid_and_token($uid, $token) {
        DB::query("DELETE FROM %t WHERE uid=%d AND token=%s", array($this->_table, $uid, $token));
    }
}