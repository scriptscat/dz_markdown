<?php


namespace Codfrm\DzMarkdown;

use InvalidArgumentException;
use ParsedownExtra;

// thank https://github.com/BenjaminHoegh/ParsedownExtended/blob/main/src/ParsedownExtended.php

class ParsedownExt extends ParsedownExtra
{

    private $settings = [];
    // Standard settings
    private $defaultSettings = [
        'math' => [
            'enabled' => false,
            'inline' => [
                'enabled' => true,
                'delimiters' => [
                    ['left' => '\\(', 'right' => '\\)'],
                ],
            ],
            'block' => [
                'enabled' => true,
                'delimiters' => [
                    ['left' => '$$', 'right' => '$$'],
                    ['left' => '\\begin{equation}', 'right' => '\\end{equation}'],
                    ['left' => '\\begin{align}', 'right' => '\\end{align}'],
                    ['left' => '\\begin{alignat}', 'right' => '\\end{alignat}'],
                    ['left' => '\\begin{gather}', 'right' => '\\end{gather}'],
                    ['left' => '\\begin{CD}', 'right' => '\\end{CD}'],
                    ['left' => '\\[', 'right' => '\\]'],
                ],
            ],
        ],
    ];


    public function __construct()
    {
//        array_unshift($this->BlockTypes['<'], 'HtmlTag');
//        array_unshift($this->voidElements,'div');
        $this->settings = $this->defaultSettings;
        $this->addBlockType(['\\', '$'], 'MathNotation');
    }


    private function addBlockType(array $markers, string $funcName): void
    {
        foreach ($markers as $marker) {
            if (!isset($this->BlockTypes[$marker])) {
                $this->BlockTypes[$marker] = [];
            }

            // add to specialcharecters array
            if (!in_array($marker, $this->specialCharacters)) {
                $this->specialCharacters[] = $marker;
            }

            // add to the beginning of the array so it has priority
            $this->BlockTypes[$marker][] = $funcName;
        }
    }


    protected function blockMathNotation($Line)
    {
        foreach ($this->settings['math']['block']['delimiters'] as $config) {

            $leftMarker = preg_quote($config['left'], '/');
            $rightMarker = preg_quote($config['right'], '/');
            $regex = '/^(?<!\\\\)(' . $leftMarker . ')(.*?)(?=(?<!\\\\)' . $rightMarker . '|$)/';

            if (preg_match($regex, $Line['text'], $matches)) {
                return [
                    'element' => [
                        'text' => $matches[2],
                    ],
                    'start' => $config['left'], // Store the start marker
                    'end' => $config['right'], // Store the end marker
                ];
            }
        }

        return;
    }


    protected function blockMathNotationContinue($Line, $Block)
    {
        if (isset($Block['complete'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['text'] .= str_repeat("\n", $Block['interrupted']);
            unset($Block['interrupted']);
        }

        // Double escape the backslashes for regex pattern
        $rightMarker = preg_quote($Block['end'], '/');
        $regex = '/^(?<!\\\\)(' . $rightMarker . ')(.*)/';

        if (preg_match($regex, $Line['text'], $matches)) {
            $Block['complete'] = true;
            $Block['math'] = true;
            $Block['element']['text'] = $Block['start'] . $Block['element']['text'] . $Block['end'] . $matches[2];


            return $Block;
        }

        $Block['element']['text'] .= "\n" . $Line['body'];

        return $Block;
    }


    protected function blockMathNotationComplete($Block)
    {
        $Block["element"]["name"] = "div";
        return $Block;
    }


    public function isEnabled(string $keyPath): bool
    {
        $keys = explode('.', $keyPath);
        $current = $this->settings;

        // Navigate through the settings hierarchy
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                $backtrace = debug_backtrace();
                $caller = $backtrace[0];
                $errorMessage = "The setting '$keyPath' does not exist. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
                throw new InvalidArgumentException($errorMessage);
            }
            // Move to the next level in the settings array
            $current = $current[$key];
        }

        // if key "enabled" exists, return its value
        if (isset($current['enabled'])) {
            return $current['enabled'];
        } elseif (is_bool($current)) {
            return $current;
        } else {
            $backtrace = debug_backtrace();
            $caller = $backtrace[0];
            $errorMessage = "The setting '$keyPath' does not have an boolean value. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
            throw new InvalidArgumentException($errorMessage);
        }
    }


    public function getSetting(string $key)
    {
        $keys = explode('.', $key);
        $current = $this->settings;

        foreach ($keys as $part) {
            if (isset($current[$part])) {
                $current = $current[$part];
            } else {
                $backtrace = debug_backtrace();
                $caller = $backtrace[0]; // Gets the immediate caller. Adjust the index for more depth.

                $errorMessage = "Setting '$key' does not exist. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
                throw new InvalidArgumentException($errorMessage);
            }
        }

        return $current;
    }


    public function getSettings(): array
    {
        return $this->settings;
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

        $ret['element']['text'] = "<a id=\"user-content-" . urlencode(ltrim($ret['element']['text'], '#')) .
            "\" class=\"anchor\" aria-hidden=\"true\" href=\"#" .
            urlencode(ltrim($ret['element']['text'], '#')) . "\" >" .
            "<span class=\"octicon octicon-link\"></span></a>" . $ret['element']['text'];

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


    protected function element(array $Element)
    {
        if ($this->safeMode) {
            $Element = $this->sanitiseElement($Element);
        }

        $markup = '<' . $Element['name'];

        if (isset($Element['attributes'])) {
            foreach ($Element['attributes'] as $name => $value) {
                if ($value === null) {
                    continue;
                }

                $markup .= ' ' . $name . '="' . self::escape($value) . '"';
            }
        }

        $permitRawHtml = false;

        if (isset($Element['text'])) {
            $text = $Element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($Element['rawHtml'])) {
            $text = $Element['rawHtml'];
            $allowRawHtmlInSafeMode = isset($Element['allowRawHtmlInSafeMode']) && $Element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        if (isset($text)) {
            $markup .= '>';

            if (!isset($Element['nonNestables'])) {
                $Element['nonNestables'] = array();
            }

            if (isset($Element['handler'])) {
                $markup .= $this->{$Element['handler']}($text, $Element['nonNestables']);
            } elseif (!$permitRawHtml) {
                $markup .= self::escape($text, true);
            } else {
                $markup .= $text;
            }

            $markup .= '</' . $Element['name'] . '>';
        } else {
            $markup .= ' />';
        }
        return $markup;
    }

    protected static function escape($text, $allowQuotes = false)
    {
        global $_G;
        if ($_G['charset'] == "gbk") {
            return $text;
        }
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

}
