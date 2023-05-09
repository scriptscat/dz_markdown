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
        return preg_replace("/\[media.*?\](.*?)\[\/media\]/", "$1", $message);
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
            $mdpos = strpos($message, '[md]');
            if ($mdpos === false) {
                return tpl_post_attribute_extra_body('dz');
            }
            $message = $this->dealPrefix($message, $mdpos);
            if (substr($message[1], 0, 4) === '[md]') {
                return tpl_post_attribute_extra_body('md', $this->dealHTML(htmlspecialchars($message[0] . $this->parseMarkdown($message[1]))));
            }
            return tpl_post_attribute_extra_body('dz');
        }
        return tpl_post_attribute_extra_body();
    }

    // 过滤xss
    function dealHTML($html)
    {
        require_once "vendor/autoload.php";
        $config = \HTMLPurifier_HTML5Config::createDefault();
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Forms', true);
        $config->set("Attr.AllowedInputTypes", array('checkbox'));
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    function viewthread_title_extra()
    {
        global $_G;
        return "<script src=\"{$_G['siteurl']}source/plugin/codfrm_markdown/dist/viewer.js\"></script>";
    }

    function dealPrefix($message, $mdpos)
    {
//        var_dump($message);
        // 处理"本帖最后由"前缀
        $prefix = '';
        if (strpos($message, '[i=s] 本帖最后由') === 0) {
            $prefix = '> ' . substr($message, 6, strpos($message, '[/i]') - 6) . "\n\n";
            $message = substr($message, $mdpos);
        } else if (strpos($message, '[quote]') === 0 &&
            preg_match('/url=(.*?)\].*?#999999\](.*?)\[.*?\[\/size\]\s*([\s\S]*?)\[\/quote\]/',
                $message, $matche)) {
            $prefix = "> [{$matche[2]}]({$matche[1]})\n> > $matche[3]" . "\n\n";
            // 取[quote]后面的[md]内容
            $pos = strpos($message, '[/quote]');
            $mdpos = strpos($message, '[md]', $pos);
            if ($mdpos === false) {
                $message = substr($message, $pos + 8);
            } else {
                $message = substr($message, $mdpos);
            }
        }
        return [$prefix, $message];
    }

    function viewthread_posttop_output()
    {
        global $postlist;
        require_once 'vendor/erusev/parsedown/Parsedown.php';
        require_once 'vendor/erusev/parsedown-extra/ParsedownExtra.php';
        require_once 'src/ParsedownExt.php';
        $Parsedown = new ParsedownExt();
        $Parsedown->setSafeMode(false);
        foreach ($postlist as $k => $post) {
            $message = C::t('forum_post')->fetch('tid:' . $post['tid'], $post['pid'], true)['message'];
            if (strlen($message) < 9) {
                continue;
            }
            $mdpos = strpos($message, '[md]');
            if ($mdpos === false) {
                continue;
            }
            $message = $this->dealPrefix($message, $mdpos);
            if (substr($message[1], 0, 4) === '[md]') {
                // 处理markdown
                $Parsedown->setImagecallback(function ($img) use ($k, &$postlist) {
                    foreach ($postlist[$k]['attachments'] as $k2 => $v) {
                        if (strpos($img['element']['attributes']['src'], $v['attachment']) !== false) {
                            unset($postlist[$k]['attachments'][$k2]);
                        }
                    }
                });
                // 正则匹配img标签进行处理
                /*                preg_match_all('/<img[\s\S]*?src=["\'](.*?)[\'"][\s\S]*?>/', $message[1], $matches);*/
//                foreach ($matches[1] as $url) {
//                    foreach ($postlist[$k]['attachments'] as $k2 => $v) {
//                        if (strpos($url, $v['attachment']) !== false) {
//                            unset($postlist[$k]['attachments'][$k2]);
//                        }
//                    }
//                }
                // 如果是markdown移除全部附件图片
                $postlist[$k]['attachments'] = [];
                $postlist[$k]['message'] = "<div class=\"markdown-body\" data-prismjs-copy=\"copy\"
	data-prismjs-copy-error=\"复制错误，请手动处理\"
	data-prismjs-copy-success=\"copy suucess\">" .
                    $this->dealHTML($Parsedown->text($message[0] . $this->parseMarkdown($message[1])))
                    . "</div>";
            }
        }
    }

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
