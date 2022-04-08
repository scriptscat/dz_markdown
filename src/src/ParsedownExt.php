<?php


namespace Codfrm\DzMarkdown;

use ParsedownExtra;


class ParsedownExt extends ParsedownExtra
{

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
}