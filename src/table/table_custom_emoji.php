<?php

if (!defined('IN_DISCUZ')) {
    exit('Aecsse Denied');
}

class table_custom_emoji extends discuz_table
{
    public function __construct()
    {

        $this->_table = 'custom_emoji';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'custom_emoji_';

        parent::__construct();
    }

    public function fetchByUid($uid)
    {
        return DB::fetch_all('select * from %t where uid=%d order by createtime desc', array($this->_table, $uid));
    }

    public function create($uid, $url)
    {
        return C::t('#codfrm_markdown#custom_emoji')->insert(array(
            'uid' => $uid,
            'url' => $url,
            'createtime' => time()
        ));
    }

    public function delete($uid, $url)
    {
        return DB::delete($this->_table, [
            'uid' => $uid,
            'url' => $url
        ]);
    }
}

?>