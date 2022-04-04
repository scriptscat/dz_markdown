<?php


namespace Codfrm\DzMarkdown;

use Parsedown;

class ParsedownExt extends Parsedown
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
            call_user_func($this->callback,$ret);
        }
        return $ret;
    }

}