<?php


use Codfrm\DzMarkdown\ParsedownExt;
use Michelf\MarkdownExtra;

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

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
        include_once template('codfrm_markdown:module');

        // 判断编辑还是新增
        if ($_GET['action'] === 'edit' && $_GET['tid']) {
            $message = C::t('forum_post')->fetch('tid:' . $_GET['tid'], $_GET['pid'], true)['message'];
            if (strlen($message) < 9) {
                return tpl_post_attribute_extra_body();
            }
            $prefix = '';
            $mdpos = strpos($message, '[md]');
            if ($mdpos === false) {
                return tpl_post_attribute_extra_body('dz');
            }
            // 处理"本帖最后由"前缀
            if (strpos($message, '[i=s] 本帖最后由') === 0) {
                $prefix = '> ' . substr($message, 6, strpos($message, '[/i]') - 6) . "\n\n";
                $message = substr($message, $mdpos);
            } else if (strpos($message, '[quote]') === 0 &&
                preg_match('/url=(.*?)\].*?#999999\](.*?)\[.*?\[\/size\]\s*(.*?)\[\/quote\]/',
                    $message, $matche)) {
                $prefix = "> [{$matche[2]}]({$matche[1]})\n> > $matche[3]" . "\n\n";
                $message = substr($message, $mdpos);
            }
            if (substr($message, 0, 4) === '[md]') {
                return tpl_post_attribute_extra_body('md', htmlspecialchars($prefix . $this->parseMarkdown($message)));
            }
            return tpl_post_attribute_extra_body('dz');
        }
        return tpl_post_attribute_extra_body();
    }

    function viewthread_title_extra()
    {
        global $_G;
        return "<script src=\"{$_G['siteurl']}source/plugin/codfrm_markdown/dist/viewer.js\"></script>";
    }

    function viewthread_posttop_output()
    {
        global $postlist;
        require_once 'vendor/erusev/parsedown/Parsedown.php';
        require_once 'vendor/erusev/parsedown-extra/ParsedownExtra.php';
        require_once 'src/ParsedownExt.php';
        $Parsedown = new ParsedownExt();
        $Parsedown->setSafeMode(true);
        foreach ($postlist as $k => $post) {
            $message = C::t('forum_post')->fetch('tid:' . $post['tid'], $post['pid'], true)['message'];
            if (strlen($message) < 9) {
                continue;
            }
            $prefix = '';
            $mdpos = strpos($message, '[md]');
            if ($mdpos === false) {
                continue;
            }
            // 处理"本帖最后由"前缀
            // 处理"回复"前缀
            if (strpos($message, '[i=s] 本帖最后由') === 0) {
                $prefix = '> ' . substr($message, 6, strpos($message, '[/i]') - 6) . "\n\n";
                $message = substr($message, $mdpos);
            } else if (strpos($message, '[quote]') === 0 &&
                preg_match('/url=(.*?)\].*?#999999\](.*?)\[.*?\[\/size\]\s*(.*?)\[\/quote\]/',
                    $message, $matche)) {
                $prefix = "> [{$matche[2]}]({$matche[1]})\n> > $matche[3]" . "\n\n";
                $message = substr($message, $mdpos);
            }
            if (substr($message, 0, 4) === '[md]') {
                // 处理markdown
                $Parsedown->setImagecallback(function ($img) use ($k, &$postlist) {
                    foreach ($postlist[$k]['attachments'] as $k2 => $v) {
                        if (strpos($img['element']['attributes']['src'], $v['attachment']) !== false) {
                            unset($postlist[$k]['attachments'][$k2]);
                        }
                    }
                });
                $postlist[$k]['message'] = "<div class=\"markdown-body\">" .
                    $Parsedown->text($prefix . $this->parseMarkdown($message)) . "</div>";
            }
        }
    }

}

class mobileplugin_codfrm_markdown
{

}

class mobileplugin_codfrm_markdown_forum extends plugin_codfrm_markdown_forum
{
    public function viewthread_title_mobile_extra()
    {
        return parent::viewthread_title_extra();
    }

    public function viewthread_bottom_mobile_output()
    {
        return parent::viewthread_posttop_output();
    }
}
