<?php/** * 查询用户表情包 */if (!defined('IN_DISCUZ')) {    exit('Access Denied');}require_once "markdown.class.php";require_once libfile('table/custom_emoji', 'plugin/codfrm_markdown');class server{    protected $custom;    public function __construct()    {        $this->custom = new table_custom_emoji();    }    public function run()    {        global $_G;        if ($_G['uid'] <= 0) {            echo json_encode(['code' => -1, 'data' => '请先登录']);            return;        }        if (!config::getInstance()->enableEmoji()) {            echo json_encode(['code' => -1, 'data' => '功能未开启']);            return;        }        switch ($_GET['op']) {            case 'recent':                $resp = $this->recent();                break;            case 'custom':                $resp = $this->custom();                break;            case 'add':                $resp = $this->add();                break;            case 'del':                $resp = $this->del();                break;            default:                return;        }        echo json_encode($resp, JSON_UNESCAPED_UNICODE);    }    public function recent()    {        global $_G;        $list = DB::fetch_all("SELECT * FROM " . DB::table('forum_attachment') . " WHERE uid=%d order by aid desc limit 30",            [$_G['uid']]);        $emoji = [];        foreach ($list as $item) {            $suffix = $item['tableid'];            if ($item['tableid'] == 127) {                $suffix = 'unused';            }            $item = DB::fetch_first("SELECT * FROM " . DB::table('forum_attachment_' . $suffix) . " WHERE aid=%d", [$item['aid']]);            $emoji[] = [                'url' => $item['attachment'],            ];        }        return ['code' => 0, 'data' => $emoji];    }    public function custom()    {        global $_G;        $list = $this->custom->fetchByUid($_G['uid']);        return ['code' => 0, 'data' => $list];    }    public function add()    {        global $_G;        $list = $this->custom->fetchByUid($_G['uid']);        if (sizeof($list) > 50) {            return ['code' => -1, 'data' => '最多只能添加50个表情包'];        }        $this->custom->create($_G['uid'], $_POST['url']);        return ['code' => 0, 'data' => 'ok'];    }    public function del()    {        global $_G;        $this->custom->delete($_G['uid'], $_POST['url']);        return ['code' => 0, 'data' => 'ok'];    }}$server = new server();$server->run();