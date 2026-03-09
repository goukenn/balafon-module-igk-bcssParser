<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssConverter.php
// @date: 20260309 14:24:26
namespace igk\bcssParser\Css;

use IGK\System\Console\Logger;
use IGK\System\IO\StringBuilder;
use IGK\System\Text\RegexMatcherContainer;

/**
* 
* @package igk\bcssParser\Css
* @author C.A.D. BONDJE DOUE
*/
class BcssConverter
{
    private $m_regex;
    private $m_state;
    private $m_def;
    private $m_roots;
    /**
     * space separator
     * @var string
     */
    var $space = '';
    public function __construct()
    {
        $this->m_def = [];
    }
    /**
     * initialize state
     * @return object 
     */
    protected function _initState(): object
    {
        return igk_createobj([
            'mode' => 0,            // <- store reading mode,
            'selector' => null,
            'property' => null,
            'range' => null,        // <- store range offset to split selector
            'brank_range' => null,  // <- store range offset to split selector
            'brank_lists' => null,   // <- store brank list to update bcss brank list selector selection
            'unsets' => null,
            'init_root' => false,    // <- init root definitions
            'store' => [], // <- store def definition 
        ]);
    }

    /**
     * init macher container 
     * @return RegexMatcherContainer 
     */
    protected function _initRegexContainer(): RegexMatcherContainer
    {
        $regex = new RegexMatcherContainer;
        $comment = $regex->begin('\/\*', '\*\/', 'comment')->last();
        $block = $regex->begin('\{', '}', 'block')->last();
        $css_selector = $regex->begin('(\.|#)?[a-zA-Z_][a-zA-Z_0-9]*', '(?=\{)', 'css-selector')->last();


        $regex->autoStore = false;
        $value = $regex->begin(':', ';|(?=})', 'css-value')->last();
        $rprop = $regex->match('(?P<n>-{2}[a-zA-Z_][a-zA-Z_0-9\-]*)', 'css-root-property')->last();
        $prop = $regex->match('(?P<n>[a-zA-Z_][a-zA-Z_0-9\-]*)', 'css-property')->last();
        $string_litteral = $regex->appendStringDetection('string', true)->last();
        $regex->autoStore = true;

        $css_selector->patterns = $selector_pattern = [
            $string_litteral,
            $regex->createPattern(['match' => '\\\\.', 'tokenID' => 'css-escape']),
            $regex->createPattern(['match' => ',', 'tokenID' => 'css-split-selector']),
            $regex->createPattern(['match' => '\\s*(\+|>|\|~)\\s*', 'tokenID' => 'css-selector-operator']),
            $regex->createPattern(['match' => '\\s+', 'tokenID' => 'css-split-brank-selector']),
        ];

        $subblock = $regex->createPattern([
            'begin' => '\{',
            'end' => '\}',
            'tokenID' => 'css-sublock',
        ]);
        $subselector = $regex->createPattern([
            'begin' => '(\.|#)?[a-zA-Z_][a-zA-Z_0-9]*',
            'end' => '(?=\{)',
            'tokenID' => 'css-subselector',
        ]);
        //$subselector->patterns = $selector_pattern;

        $block->patterns = [
            $comment,
            $value,
            $rprop,
            $prop,
        ];

        $subblock->patterns = [
            $comment,
            $value,
            $rprop,
            $prop,
            $subblock
        ];
        $css_prop_block = $regex->createPattern([
            'begin' => '\{',
            'end' => '\}',
            'tokenID' => 'css-prop-block'
        ]);
        $css_prop_block->patterns = [
            $comment,
            $block,
            $subselector
        ];

        $css_prop = $regex->begin('@(?P<n>[a-zA-Z_][a-zA-Z_0-9\-]*)', '(?<=;|\})', 'css-prop')->last();
        $css_prop->patterns = [
            $comment,
            $string_litteral,
            $css_prop_block,
            //$subselector,
            //$subblock
        ];


        return $regex;
    }
    /**
     * 
     * @param mixed $src 
     * @return void 
     */
    public function treat($src)
    {
        $regex = $this->m_regex ?? ($this->m_regex = $this->_initRegexContainer()); //new RegexMatcherContainer;
        if (is_null($this->m_state)) {
            $this->m_state = $this->_initState();
        }

        $pos = 0;
        // define

        $handler = $this->getHandler();
        $_is_debug = igk_is_debug();
        while ($g = $regex->detect($src, $pos)) {
            if ($e = $regex->end($g, $src, $pos)) {
                $id = $e->tokenID;
                $_is_debug && Logger::info(sprintf('[css-bcss] - %-20s [%s]', $id, json_encode($e->value)));
                if ($fc = igk_getv($handler, $id)) {
                    $fc($e, $pos, $src);
                }
            }
        }
    }
    /**
     * get property key 
     * @param string $v 
     * @return void 
     */
    protected function _getPropKey(string $src): string{
        $regex = new RegexMatcherContainer;
        $regex->match("\\s+", "space");
        $regex->appendStringDetection();
        $regex->match("\{", "end");
        $pos=0;
        // define
        $s = '';
        $offset = 0;
        while($g = $regex->detect($src, $pos)){
            if ($e = $regex->end($g, $src, $pos)){
                $id = $e->tokenID;
                if ($id=='space'){
                    $s.=substr($src, $offset, $e->from-$offset). ' ';
                    $offset = $e->to;
                }
                if ($id=='end'){
                    $s.=substr($src, $offset, $e->from-$offset);
                    $offset = strlen($src);
                    break;
                }
            }
        }
        $s.= substr($src, $offset);
        return trim($s);
    }
    /**
     * callback handler 
     * @return array 
     */
    protected function getHandler(): array
    {
        return [
            'comment' => function ($e) {},
            'css-prop' => function ($e) {
                if ($this->m_state->mode == 1) {
                    $key = $this->_getPropKey($e->value);

                    $def = $this->m_def;
                    $odef = array_pop($this->m_state->store);
                    $odef[$key] = new CssPropDefinition($def);
                    $this->m_def = $odef;
                }else{
                    $s = $this->_treatSelector($e->value);
                    $this->m_def[] = $s;
                }
                $this->m_state->mode = 0;
                $this->m_state = $this->_initState();
            },
            'css-subselector' => function ($e) {
                $this->m_state->mode = 1;
                array_push($this->m_state->store, $this->m_def);
                $this->m_def = [];
                $s = $e->value;
                if ($rg = $this->m_state->range) {
                    foreach ($rg as $k => $v) {
                        $rg[$k] = $v - $e->from;
                    }
                    $s = array_map('trim', BcssCssUtility::SplitLitteral($s, $rg));
                }
                if ($rg = $this->m_state->brank_range) {
                    foreach ($rg as $k => $v) {
                        $rg[$k] = [$v[0] - $e->from, $v[1] - $e->from];
                    }
                    $ts = array_map('trim', BcssCssUtility::SplitLitteral($s, $rg));
                    if (count($ts) > 1)
                        $this->m_state->brank_lists = $ts;
                }
                $s = $this->_glueSelector($s);
                $this->m_state->selector = $s;
                $this->m_state->range = null;
                $this->m_state->brank_range = null;
            },
            'css-selector' => function ($e) {
                list($mode) = igk_extract($this->m_state, 'mode');

                $s = $e->value;
                if ($rg = $this->m_state->range) {
                    foreach ($rg as $k => $v) {
                        $rg[$k] = $v - $e->from;
                    }
                    $s = array_map('trim', BcssCssUtility::SplitLitteral($s, $rg));
                }
                if ($rg = $this->m_state->brank_range) {
                    foreach ($rg as $k => $v) {
                        $rg[$k] = [$v[0] - $e->from, $v[1] - $e->from];
                    }
                    $ts = array_map('trim', BcssCssUtility::SplitLitteral($s, $rg));
                    if (count($ts) > 1)
                        $this->m_state->brank_lists = $ts;
                }
                $s = $this->_glueSelector($s);
                $this->m_state->selector = $s;
                $this->m_state->range = null;
                $this->m_state->brank_range = null;
            },
            'css-property' => function ($e) {
                $this->m_state->property = $e->value;
            },
            'css-root-property' => function ($e) {
                if (!isset($this->m_roots[$e->value])) {
                    $this->m_roots[$e->value] = 1;
                    $this->m_state->init_root = true;
                }
                $this->m_state->property = $e->value;
            },
            'css-value' => function ($e) {
                $es = $e->value;
                $v = trim(substr($e->value, 1, igk_str_endwith($es, ';') ? -1 : strlen($es)));
                $p = $this->m_state->property;
                $s = $this->m_state->selector;
                (empty($s)) && igk_die('missing selection');
                $v_bs = $s;
                if (!is_array($s)) {
                    $s = [$s];
                }
                while (count($s) > 0) {
                    $ts = array_shift($s);
                    if (!isset($this->m_def[$ts])) {
                        $this->m_def[$ts] = new BcssCssEntry;
                    }
                    $this->m_def[$ts]->set($p, $v);
                }
                // + | --------------------------------------------------------------------
                // + | update brank list definition 
                // + |                
                if ($list = $this->m_state->brank_lists) {
                    $rcoot = null;
                    while (count($list) > 0) {
                        $q = array_shift($list);
                        $n = igk_getv($this->m_def, $q) ?? $this->_newEntry($q, $rcoot);

                        $rcoot = $n;
                    }
                    $rcoot && $rcoot->set($p, $v);

                    if (is_string($v_bs)) {
                        $this->m_state->unsets[$v_bs] = 1;
                        // unset($this->m_def[$v_bs]);
                    }
                }
                if ($this->m_state->init_root) {
                    $this->m_roots[$p] = $v;
                    $this->m_state->init_root = false;
                }

                $this->m_state->property = null;
            },
            'css-split-selector' => function ($e) {
                $this->m_state->range[] = $e->from + 1;
            },
            'css-split-brank-selector' => function ($e) {
                $this->m_state->brank_range[] = [$e->from, $e->to];
            },
            'css-selector-operator' => function ($e) {},

            'block' => function ($e) {
                if ($tr = $this->m_state->unsets) {
                    foreach (array_keys($tr) as $k) {
                        unset($this->m_def[$k]);
                    }
                    $this->m_state->unsets = null;
                }
                if ($this->m_state->mode ==0)
                    $this->m_state = $this->_initState();
            }

        ];
    }
    /**
     * format selector 
     * @param string|string[] $s 
     * @return void 
     */
    protected function _glueSelector($s)
    {
        $ts = is_string($s);
        $rt = [];
        if ($ts) {
            $s = [$s];
        }
        while (count($s) > 0) {
            $q = array_shift($s);
            $rt[] = $this->_treatSelector($q);
        }
        return $ts ? implode('', $rt) : $rt;
    }
    public function _treatSelector($src)
    {
        $regex = new RegexMatcherContainer;
        $pos = 0;
        // define
        $regex->appendStringDetection();
        $regex->appendMultilineComment();
        $regex->match('\\s+', 'space');
        $tg = '';
        $moffset = 0;
        while ($g = $regex->detect($src, $pos)) {
            if ($e = $regex->end($g, $src, $pos)) {
                $id = $e->tokenID;
                if ($id == 'space') {
                    $tg .= substr($src, $moffset, $e->from - $moffset) . ' ';
                    $moffset = $e->to;
                }
            }
        }
        $tg .= substr($src, $moffset);
        return trim($tg);
    }
    /**
     * new entry 
     * @param string $selector 
     * @param mixed $rcoot 
     * @return BcssCssEntry 
     */
    protected function _newEntry(string $selector, $rcoot)
    {
        $n = new BcssCssEntry;
        $this->m_def[$selector] = $n;
        if (!is_null($rcoot)) {
            $rcoot->add($n, $selector);
        }
        return $n;
    }
    protected function _header()
    {
        return implode("\n", [
            '// @author: ' . IGK_AUTHOR,
            '// @file: bcss file',
            '// @date: ' . date('Ymd H:i:s'),
        ]);
    }
    /**
     * 
     * @return string 
     */
    public function render(): string
    {
        $s = new StringBuilder;
        $c = $this->m_def;
        ksort($c);
        $ch = '';
        $s->appendLine($this->_header());
        if ($v_root = $this->m_roots) {
            $s->appendLine(implode("\n", array_map(function ($v, $k) {
                return sprintf('# ' . $k . ' %s', $v);
            }, $v_root, array_keys($v_root))));
        }
        foreach ($c as $k => $v) {
            if (is_numeric($k)){
                $s->append($v);
                continue;
            }
            if ($v instanceof CssPropDefinition){
                $s->append(sprintf('%s{%s}', $ch . $k, $v->render()));
                continue;
            }
            if (!is_null($v->getParent()))
                continue;
            $s->append(sprintf('%s{%s}', $ch . $k, $v->render()));
            $ch = $this->space;
        }

        return '' . $s;
    }
    /**
     * 
     * @param string $src 
     * @param mixed $options 
     * @return string 
     */
    public static function ConvertFromCss(string $src, $options = null)
    {
        $g = new static;
        $g->treat($src);
        return $g->render();
    }
}