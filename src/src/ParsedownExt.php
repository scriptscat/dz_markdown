<?php


namespace Codfrm\DzMarkdown;

use ParsedownExtra;


class ParsedownExt extends ParsedownExtra
{
    public function __construct()
    {
//        array_unshift($this->BlockTypes['<'], 'HtmlTag');
//        array_unshift($this->voidElements,'div');
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


    private $menu = [];

    private $menuStack = [];

    public function Menu(): array
    {
        $menu = [];
        foreach ($this->menu as $k => $v) {
            $menu[] = [
                'level' => $v['level'],
                'title' => $v['title'],
                'next' => $this->MenuNext($v['next'])
            ];
        }
        return $menu;
    }

    private function MenuNext($next): array
    {
        $menu = [];
        foreach ($next as $k => $v) {
            $menu[] = [
                'level' => $v['level'],
                'title' => $v['title'],
                'next' => $this->MenuNext($v['next'])
            ];
        }
        return $menu;
    }

    // 优化锚点与链接
    protected function blockHeader($Line)
    {
        $ret = parent::blockHeader($Line);

        $level = $ret['element']['name'][1];
        $menu =& $this->menu;
        while (true) {
            if (empty($this->menuStack)) {
                break;
            }
            $last = $this->menuStack[count($this->menuStack) - 1];
            // 上一次的菜单比当前等级小
            if ($last['level'] > $level) {
                array_pop($this->menuStack);
            } else if ($last['level'] == $level) {
                if ($level == '1') {
                    break;
                }
                // 相等,继续用上一级菜单
                array_pop($this->menuStack);
                $menu = &$this->menuStack[count($this->menuStack) - 1]['next'];
                break;
            } else if ($last['level'] < $level) {
                // 上一级菜单比当前等级大
                $menu =& $this->menuStack[count($this->menuStack) - 1]['next'];
                break;
            }
        }

        $menu[] = [
            'level' => $level,
            'next' => [],
            'title' => $ret['element']['text']
        ];

        // 入栈
        $this->menuStack[] =& $menu[count($menu) - 1];

        $ret['element']['text'] = "<h2><a id=\"user-content-" . urlencode($ret['element']['text']) .
            "\" class=\"anchor\" aria-hidden=\"true\" href=\"#" .
            urlencode($ret['element']['text']) . "\" >" .
            "<span class=\"octicon octicon-link\"></span></a>" . $ret['element']['text'] . "</h2>";

        return $ret;
    }

    protected function inlineImage($Excerpt)
    {
        $ret = parent::inlineImage($Excerpt);
        if ($ret) {
            $ret['element']['attributes']['class'] = 'md-img';
            if ($this->callback) {
                call_user_func($this->callback, $ret);
            }
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

    protected $htmlTag = '/<(div|h[1-6]|font)(.*?)>/';
    protected $endHtmlTag = '/<\/(div|h[1-6]|font)>/';
    protected $allowAttr = '/(align|color)\s*=\s*"([#:\w]+?)"/';

    protected $hasToc = false;

    public function hasToc(): bool
    {
        return $this->hasToc;
    }


    public function text($text)
    {
        $ret = parent::text($text);
        // 处理toc
        if ($this->hasToc) {
            $ret = str_replace('[TOC]', '', $ret);
        }
        return $ret;
    }

    public function line($text, $nonNestables = array(), &$openTagNum = [], $main = true)
    {
        if ($text === '[TOC]') {
            $this->hasToc = true;
            return $text;
        }
        // 对html标签 div/h1-6 进行处理
        if (preg_match($this->htmlTag, $text, $match, PREG_OFFSET_CAPTURE)) {
            // 处理open tag
            // 替换掉html标签
            preg_match_all($this->allowAttr, $match[0][0], $attrMatches, PREG_SET_ORDER);
            $ret = "<{$match[1][0]} ";
            $openTagNum[$match[1][0]]++;
            foreach ($attrMatches as $k => $attrMatch) {
                $ret .= "{$attrMatch[1]}=\"{$attrMatch[2]}\"";
            }
            $ret = rtrim($ret);
            $ret .= ">" . $this->line(substr($text, $match[0][1] + strlen($match[0][0])), $nonNestables, $openTagNum, false);
            return $this->line(substr($text, 0, $match[0][1]), $nonNestables, $openTagNum, false) . $ret;
        } else if (preg_match($this->endHtmlTag, $text, $match, PREG_OFFSET_CAPTURE)) {
            // 处理close tag
            if (!$openTagNum[$match[1][0]]) {
                // 不存在opentag,直接返回空
                return '';
            }
            // 存在减去并处理
            $openTagNum[$match[1][0]]--;
            $ret = "</{$match[1][0]}>";
            $ret .= $this->line(substr($text, $match[0][1] + strlen($match[0][0])), $nonNestables, $openTagNum, false);
            return $this->line(substr($text, 0, $match[0][1]), $nonNestables, $openTagNum, false) . $ret;
        }
        $ret = parent::line($text, $nonNestables); // TODO: Change the autogenerated stub
        // 处理\n换行
        if ($main) {
            // 闭合标签
            foreach ($openTagNum as $k => $v) {
                $ret .= str_repeat("</{$k}>", $v);
            }
        }
        return str_replace("\n", "<br />", $ret);
    }

}
