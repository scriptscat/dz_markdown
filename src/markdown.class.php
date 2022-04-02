<?php

use Codfrm\DzMarkdown\ParsedownExt;

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

include_once template('codfrm_markdown:module');

class plugin_codfrm_markdown
{

}

class plugin_codfrm_markdown_forum extends plugin_codfrm_markdown
{

    function parseMarkdown($message)
    {
        $message = substr($message, 4, stripos($message, "[/md]") - 4);

        $message = preg_replace("/\[i=s\](.*?)\[\/i\]/is",
            '$1' . "\r\n",
            $message);

        $message = preg_replace("/\[audio.*?\](.*?)\[\/audio\]/", "$1", $message);
        $message = preg_replace("/\[url.*?\](.*?)\[\/url\]/", "$1", $message);
        $message = preg_replace("/\[video.*?\](.*?)\[\/video\]/", "$1", $message);
        $message = preg_replace("/\[flash.*?\](.*?)\[\/flash\]/", "$1", $message);
        $message = preg_replace("/\[media.*?\](.*?)\[\/media\]/", "$1", $message);

        return $message;
    }

    public function post_middle()
    {
        // 判断编辑还是新增
        if ($_GET['action'] === 'edit' && $_GET['tid']) {
            $message = C::t('forum_post')->fetch('tid:' . $_GET['tid'], $_GET['pid'], true)['message'];
            if (strlen($message) < 9) {
                return tpl_post_attribute_extra_body();
            }
            if (substr($message, 0, 4) === '[md]') {
                return tpl_post_attribute_extra_body('md', $this->parseMarkdown($message));
            }
            return tpl_post_attribute_extra_body('dz');
        }
        return tpl_post_attribute_extra_body();
    }

    function viewthread_title_extra()
    {
        global $_G;
        return "<script src=\"{$_G['siteurl']}source/plugin/codfrm_markdown/dist/css.js\"></script>";
    }

    function viewthread_posttop_output()
    {
        global $postlist;
        require_once 'vendor/erusev/parsedown/Parsedown.php';
        require_once 'src/ParsedownExt.php';
        $Parsedown = new ParsedownExt();
        $Parsedown->setSafeMode(true)
            ->setMarkupEscaped(true);
        foreach ($postlist as $k => $post) {
            $message = C::t('forum_post')->fetch('tid:' . $post['tid'], $post['pid'], true)['message'];
            if (strlen($message) < 9) {
                continue;
            }

            if (substr($message, 0, 4) === '[md]') {
                // 处理markdown
                $postlist[$k]['message'] = "<div class=\"markdown-body\">" . $Parsedown->text($this->parseMarkdown($message)) . "</div>";
            }
        }
    }

}
