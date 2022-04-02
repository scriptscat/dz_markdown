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
}