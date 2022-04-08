<?php


namespace Codfrm\DzMarkdown;

use ParsedownExtra;


class ParsedownExt extends ParsedownExtra
{
    public function __construct()
    {
        array_unshift($this->BlockTypes['<'], 'HtmlTag');
    }

    public function inlineUrl($Excerpt)
    {
        $ret = parent::inlineUrl($Excerpt);
        if ($ret) {
            $ret['element']['attributes']['target'] = '_blank';
        }
        return $ret;
    }

    protected $callback = false;

    public function setImagecallback($callback)
    {
        $this->callback = $callback;
    }

    protected function inlineImage($Excerpt)
    {
        $ret = parent::inlineImage($Excerpt);
        if ($ret && $this->callback) {
            call_user_func($this->callback, $ret);
        }
        return $ret;
    }

    protected function blockListComplete(array $Block)
    {
        $list = parent::blockListComplete($Block);

        if (!isset($list)) {
            return $list;
        }

        if (!($list['element']['name'] == 'ul' || $list['element']['name'] == 'ol')) {
            return $list;
        }

        foreach ($list['element']['text'] as $key => $listItem) {
            $args = $listItem['text'];
            if (isset($args[0])) {
                $firstThree = mb_substr($args[0], 0, 3);
                $rest = trim(mb_substr($args[0], 3));
                if ($firstThree === '[x]' || $firstThree === '[ ]') {
                    $checked = $firstThree === '[x]' ? ' checked' : '';
                    $list['element']['text'][$key] = [
                        'name' => 'li',
                        'handler' => 'checkbox',
                        'text' => [
                            'checked' => $checked,
                            'text' => $rest
                        ],
                    ];
                }
            }
        }

        return $list;
    }

    public function checkbox($text)
    {
        return '<input type="checkbox" disabled ' . ($text['checked'] ? 'checked' : '') . ' /> ' . $text['text'];
    }

    // 简单写了,支持div和h1-h6标签的属性,有需要再看看
    public function blockHtmlTag($Line)
    {
        switch ($Line['body']) {
            case '</div>':
            case '</h1>':
            case '</h2>':
            case '</h3>':
            case '</h4>':
            case '</h5>':
            case '</h6>':
            case '</font>':
                return [
                    'handler' => 'echoHtml',
                    'markup' => $Line['body'],
                    'text' => $Line['body']
                ];
        }
        // 支持div和h1-h6
        $endTag = '';// 结束的tag,用来判断是否不html编码
        $tag = mb_substr($Line['body'], 0, 5);
        if ($tag !== '<div ') {
            $tag = mb_substr($Line['body'], 0, 4);
            switch ($tag) {
                case '<h1 ':
                case '<h2 ':
                case '<h3 ':
                case '<h4 ':
                case '<h5 ':
                case '<h6 ':
                    $endTag = mb_substr($Line['body'], mb_strlen($Line['body']) - 5);
                    switch ($endTag) {
                        case '</h1>':
                        case '</h2>':
                        case '</h3>':
                        case '</h4>':
                        case '</h5>':
                        case '</h6>':
                            break;
                        default:
                            $endTag = '';
                    }
                    break;
                default:
                    $tag = mb_substr($Line['body'], 0, 6);
                    if ($tag !== '<font ') {
                        return;
                    } else {
                        $endTag = mb_substr($Line['body'], mb_strlen($Line['body']) - 7);
                        if ($endTag !== '</font>') {
                            $endTag = '';
                        }
                    }
            }
        } else {
            $endTag = mb_substr($Line['body'], mb_strlen($Line['body']) - 6);
            if ($endTag !== '</div>') {
                $endTag = '';
            }
        }
        $start = mb_strpos($Line['body'], '>');
        if ($start === false) {
            return;
        }
        // 取出align和color
        preg_match_all('/([a-z]+?)="([a-z]+?)"/', $Line['body'], $matches, PREG_SET_ORDER);
        $attr = '';
        foreach ($matches as $k => $v) {
            switch ($v[1]) {
                case 'align':
                case 'color':
                    $attr .= $v[1] . '="' . parent::escape($v[2]) . '" ';
            }
        }
        return [
            'element' => [
                'name' => 'h',
                'handler' => 'echoHtml',
                'text' => $tag . $attr . '>' . parent::escape(mb_substr($Line['body'],
                        $start + 1, mb_strlen($Line['body']) - mb_strlen($endTag) - $start - 1)) . $endTag
            ],
        ];
    }

    public function echoHtml($text)
    {
        return $text;
    }
}
