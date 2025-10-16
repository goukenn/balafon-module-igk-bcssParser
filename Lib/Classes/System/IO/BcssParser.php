<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssParser.php
// @date: 20240112 15:23:06
namespace igk\bcssParser\System\IO;

use Error;
use Exception;
use igk\bcssParser\BcssMultiDirective;
use igk\bcssParser\IBcssValueHandler;
use igk\bcssParser\Directives\BcssDirectiveFactory;
use IGK\Helper\StringUtility;
use IGK\System\Exceptions\ArgumentTypeNotValidException;
use IGK\System\Html\Dom\HtmlDocTheme;
use IGK\System\IO\Path;
use IGKException;
use ReflectionException;

///<summary></summary>
/**
 * 
 * @package igk\bcssParser\System\IO
 * @author C.A.D. BONDJE DOUE
 */
class BcssParser
{
    const TOKENS = '_:-.0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const TOKENS_DIRECTIVE = '-.0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const TOKENS_SELECTOR = ' >+:-.,0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . "\n\t\r";
    const DEFAULT_MEDIA_KEY = '@def';
    const CSS_PROPS_REGEX = "/^(--)?[\\w-]+\\b$/";

    private $m_directive_definitions;
    private $m_setup;
    private $m_supportThemes = [];

    /**
     * auto merging screen
     * @var boolean
     */
    var $autoMerge = false;

    /**
     * allow printf format 
     * @var ?bool
     */
    private static $sm_allow_printf_format;
    /**
     * get or set the default media
     * @var string
     */
    var $default_media = self::DEFAULT_MEDIA_KEY;

    /**
     * get or set the default theme in use
     * @var ?string
     */
    var $default_theme;
    /**
     * base directory
     * @var ?string
     */
    var $directory;
    /**
     * 
     * @var ?IBcssTokenListener
     */
    private $m_listener;

    /**
     * 
     * @var mixed
     */
    private $m_source;
    /**
     * 
     * @var ?array
     */
    private $m_definitions;
    /**
     * 
     * @var HtmlDocTheme
     */
    private $m_theme;
    /**
     * store the state information 
     * @var ?BcssStateInfo
     */
    private $m_state;

    /**
     * 
     * @return HtmlDocTheme 
     */
    public function getTheme()
    {
        return $this->m_theme;
    }
    /**
     * check if this is a supported theme
     * @param string $themename 
     * @return bool 
     */
    public function isSupportTheme(string $themename)
    {
        return in_array($themename, $this->m_supportThemes);
    }
    private function __construct()
    {
        $this->m_theme = new HtmlDocTheme(null, 'bcssTheme');
        $this->m_state = new BcssStateInfo;
        $this->m_setup = new BcssSetup;
        $this->m_definitions = [];
    }
    public static function ParseFormFile(string $filename)
    {
        return self::ParseFromContent(file_get_contents($filename), dirname($filename));
    }
    /**
     * parse from content
     * @param string $content 
     * @return static 
     * @throws IGKException 
     */
    public static function ParseFromContent(string $content, ?string $basedir = null, bool $allow_print_format = false)
    {
        $s = new static;
        $s->directory = $basedir;
        self::$sm_allow_printf_format = $allow_print_format;
        $s->load($content);
        self::$sm_allow_printf_format = false;
        return $s;
    }
    public function clear()
    {
        $this->m_definitions = [];
    }
    /**
     * render css definition
     * @return string
     */
    public function render(bool $minfile = false, bool $themeexport = false): string
    {
        if ($this->m_state->useGlobalStyleDefinition) {
            $systheme = igk_app()->getDoc()->getSysTheme();
            $systheme->initGlobalDefinition();
        }
        $this->m_theme->setDefaultTheme($this->default_theme);
        $_name = $this->default_media;
        if ($_name != self::DEFAULT_MEDIA_KEY) {
            // merge rendering data to target definition 
            $tab = $this->m_theme->getdef()->to_array();
            $this->updateThemeProperties(substr($_name, 1), $tab);
            if ($this->autoMerge)
                $this->m_theme->getdef()->clear();
            // igk_die("missing merging default data");
        }
        return $this->m_theme->get_css_def($minfile, $themeexport);
    }
    /**
     * get current source 
     * @return mixed 
     */
    public function getCurrentSource()
    {
        return $this->m_source;
    }
    /**
     * get token definition
     * @return null|array 
     */
    public function getTokens()
    {
        return $this->m_definitions;
    }
    /**
     * load describe directive
     * @return void 
     */
    private function _loadDescribeDirective(string $p)
    {
        list($key, $value) = explode(" ", trim($p), 2);
        $setups = igk_getv([
            "default-media" => 'default_media',
            "default-theme" => 'default_theme'
        ], $key, $key);
        $value = trim($value);
        switch ($setups) {
            case 'default_media': {
                    if ($value == "lg") {
                        $value = 'def';
                    }
                    $g = igk_str_startwith($value, '@') ? $value : '@' . $value;
                    if (!in_array($g, [self::DEFAULT_MEDIA_KEY, '@ptr'])) {
                        if (!igk_str_endwith($g, "-screen")) {
                            $g .= '-screen';
                        }
                    }
                    if (preg_match('/@(ptr|(def|xsm|sm|lg|xlg|xxlg)-screen)/', $g))
                        $this->default_media = $g;
                }
                break;
            case 'default_theme':
                $this->default_theme = $value;
                break;
            default:

                $value = str_replace('-', '_', $value);

                $this->m_setup->$setups = $value;
                break;
        }
    }
    /**
     * read line utility class 
     * @param string $content 
     * @param int $pos 
     * @return string 
     */
    private static function _ReadLine(string $content, int &$pos)
    {
        return StringUtility::ReadLine($content, $pos);
    }
    private static function _TreatLitteral(string $content, &$g, &$pos)
    {

        if (($nv = strpos($content, '{', $pos)) !== false) {
            $g .= substr($content, $pos, $nv - $pos);
            $t = [];
            foreach (explode(',', $g) as $q) {
                $q = trim($q);
                if (self::IsMediaDirective($q)) {
                    $t[$q] = $q;
                    continue;
                }
                throw new Exception('not a directive');
            }
            sort($t);
            $g = implode(',', $t);
            $pos = $nv;
        }
    }
    public static function IsMediaDirective(string $l)
    {
        return in_array(ltrim($l, '@'), explode('|', 'def|xsm-screen|sm-screen|lg-screen|xlg-screen|xxlg-screen|xsm|sm|lg|xlg|xxlg'));
    }
    /**
     * load content
     * @param string $content 
     * @return void 
     */
    public function load(string $content)
    {
        $ln = strlen($content);
        $pos = 0;
        $this->m_source = $content;
        $v_tokens = &$this->m_definitions; //[];
        $v_v = '';
        while ($pos < $ln) {
            $ch = $content[$pos];
            $v_ltoken = null;
            if ($this->m_state->depth == 0) {
                if ($this->m_state->directive) {

                    if ($this->m_state->directive instanceof IBcssDirectiveHandler) {
                        $this->m_state->directive->handle($this, $content, $pos);
                        $this->m_state->directive = null;
                        $pos++;
                        continue;
                    }
                    if (is_array($this->m_state->directive)) {
                        $n = $this->m_state->directive[1];
                        if ($n == 'import') {
                            $v_data = $this->_readLine($content, $pos);
                            $v_ltoken = [BcssTokens::TOKEN_VALUE, trim($v_data)];
                            $ch = '';
                        }
                    }
                }
            } else if ($v_directive = $this->m_state->scope_directive) {
                if (is_array($v_directive) && (igk_getv($v_directive, 2) == 'scoped')) {
                    $v = self::_ReadScoped($content, $pos);
                    if ($v && !empty($v = trim($v))) {
                        $v_ltoken = [BcssTokens::TOKEN_VALUE, $v];
                    } else {
                        igk_die('missing scoped directive value');
                    }
                }
                $ch = '';
            }
            switch ($ch) {
                case '@':
                    $pos++;
                    $g = $ch . self::_ReadName($content, $pos);
                    // +| if the first directive a bcss media directive then read until '{' and join with 
                    if (self::IsMediaDirective($g)) {
                        self::_TreatLitteral($content, $g, $pos);
                    }
                    $m_value = $g;
                    $v_ltoken = [BcssTokens::TOKEN_DIRECTIVE, $g];
                    $pos--;
                    break;
                case "#":
                    if (empty($m_value) && ($pos + 1 < $ln) && ($content[$pos + 1] == ' ')) {
                        // + | --------------------------------------------------------
                        // + | line directive comment
                        // + | describe comment 
                        // + | --------------------------------------------------------
                        if (($line = strpos($content, "\n", $pos)) !== false) {
                            // load directive 
                            $g = substr($content, $pos + 1, $line - $pos + 1);
                            $this->_loadDescribeDirective(trim($g));
                            $pos = $line;
                        } else {
                            $pos = $ln;
                        }
                        $ch = '';
                    }
                    break;
                case '/':
                    if ($pos + 1 < $ln) {
                        $tch = $content[$pos + 1];
                        if ($tch == '*') {
                            // start read comment
                            if (($p = strpos($content, '*/', $pos + 1)) !== false) {
                                $m_value = $ch . '*' . trim(substr($content, $pos + 2, $p - $pos));
                                $pos = $p + 2;
                                $v_ltoken = [BcssTokens::TOKEN_COMMENT, $m_value];
                            } else {
                                igk_die('invalid comment definition');
                            }
                        } else if ($tch == "/") {
                            // + | single line comment
                            $g = strpos($content, "\n", $pos + 1);
                            if (false === $g) {
                                $pos = $ln;
                            } else {
                                $m = substr($content, $pos, $g - $pos + 1);
                                $v_ltoken = [BcssTokens::TOKEN_COMMENT, $m];

                                $pos = $g + 1;
                            }
                            $ch = "";
                        }
                    }
                    break;
                case '{':
                case '}':
                    //+| detect litteral expression 
                    if (($ch == '{') && ($this->m_state->depth > 0) && empty(trim($v_v)) && preg_match("/\{\s*(?P<name>\b[a-z\-0-9]+\b)\s*:\s*(?P<value>[^\}]+)\}/i", $content, $exp, 0, $pos)) {
                        $g = igk_str_read_brank($content, $pos, '}', '{');
                        $v_ltoken = [BcssTokens::TOKEN_THEME_DEF, sprintf('{%s}', $exp['name'] . ':' . $exp['value'])];
                    } else {
                        if (($ch == '}') && !empty((trim($v_v)))) {
                            // + |---------------------------------
                            // + | send close token next token back 
                            if (strpos($v_v, ':') !== false) {
                                list($n, $v) = explode(":", $v_v, 2);
                                $this->m_state->property = trim($n);
                                $this->m_state->mode = 1;
                                $v_v = trim($v);
                            } else {
                                $v_v = trim($v_v);
                            }
                        }
                        $v_ltoken = [BcssTokens::TOKEN_BRANK, $ch];
                    }
                    break;
                case '(':
                case ')':
                    $func = igk_str_read_brank($content, $pos, ')', '(');
                    $v_v .= $func;
                    if ($this->m_state->mode != 1) {
                        $v_ltoken = [BcssTokens::TOKEN_FUNC, $v_v];
                        $this->m_state->speudoSelect = true;
                        $v_v = '';
                    }
                    $ch = '';
                    break;
                case '[':
                    $v_v .= igk_str_read_brank($content, $pos, ']', '[');
                    if ($this->m_state->mode == 0) {
                        $v_ltoken = [BcssTokens::TOKEN_ARRAY_EXPRESS, trim($v_v)];
                        $this->m_state->speudoSelect = true;
                        $v_v = '';
                    }
                    $ch = '';
                    break;
                case ':': // mark for end selector 
                    if ($this->m_state->mode == 0) {
                        $v_ltoken = [BcssTokens::TOKEN_END_SELECTOR, $ch];
                        $ch = '';
                    }
                    break;
                case ';': // on value stop 
                    $v_ltoken = [BcssTokens::TOKEN_END_VALUE, $ch];
                    break;
                case '"':
                case '\'':
                    $pos++;
                    $v = $ch . igk_str_read_brank($content, $pos, $ch, $ch);
                    $v_ltoken = [BcssTokens::TOKEN_LITTERAL, $v];
                    break;

                default:
                    if ($this->m_state->mode == 0) {
                        if (!empty(trim($ch))) {
                            if ($ch == '!') {
                                // just read !important
                                $pos++;
                                $v_n = self::_ReadName($content, $pos);
                                $v_v .= ' ' . $ch . $v_n;
                            } else {
                                $v_v .= self::_ReadSelector($content, $pos, $this->m_state->mode);
                            }
                            $ch = '';
                        }
                    }
                    break;
            }

            if ($v_ltoken) {
                if (!empty($v_v) && (!empty($v_v = trim($v_v))) || ($v_v === '0')) {
                    // detect for selector or litteral
                    // igk_wln('handle: '.$v_v);
                    $this->_handleToken($v_tokens,  [BcssTokens::TOKEN_VALUE, $v_v]);
                }
                $this->_handleToken($v_tokens, $v_ltoken);
                $v_v = '';
            } else {
                $v_v .= $ch;
            }
            $pos++;
        }
    }
    private function _handleToken(&$v_token_list, array $token)
    {
        $v_token_list[] = $token;
        $this->handleToken($token);
    }
    private function _pushSelector(string $value)
    {
        $this->m_state->pushSelector($value);
    }
    private function _popSelector()
    {
        $this->m_state->popSelector();
    }
    public function handleToken($token)
    {
        $e = $token;
        switch ($e[0]) {
            case BcssTokens::TOKEN_BRANK:
                if ($e[1] == '{') {
                    $this->m_state->depth++;
                    if ($this->m_state->value) {
                        $this->_pushSelector($this->m_state->value);
                    }
                    // + start new definition section 
                    if ($this->m_state->def) {
                        $this->m_state->def = $this->_initStateDefinition($this->m_state->registerThemes);
                    }
                } else {
                    $this->m_state->depth--;
                    $this->_storeCss();
                    if ($this->m_state->depth <= 0) {
                        self::_CleanState($this->m_state);
                    }
                }
                $this->m_state->mode = 0;
                break;
            case BcssTokens::TOKEN_DIRECTIVE:
                $directive =  $this->getDirectiveCallback($e[1]) ?? igk_die(sprintf("missing directive [%s]", $e[1]));
                if ($this->m_state->depth == 0) {
                    $this->m_state->directive = $directive;
                    $this->m_state->directive_name = $e[1];
                    $this->m_state->clearScope();
                    $this->m_state->def = null;
                    $this->m_state->getSelector() && igk_die('selector contains items');
                } else {
                    $this->m_state->scope_directive = $directive;
                    $this->m_state->scope_directive_name = $e[1];
                }

                break;
            case BcssTokens::TOKEN_ARRAY_EXPRESS:
                if ($this->m_state->mode == 0) {
                    $this->m_state->property = null;
                    $this->m_state->value = $e[1];
                    $this->_updateStateDirective();
                }
                break;
            case BcssTokens::TOKEN_THEME_DEF:
                $this->m_state->property = null;
                $this->m_state->value = $e[1];
                $this->m_state->useGlobalStyleDefinition = true;
                $this->_updateStateDirective();
                break;
            case BcssTokens::TOKEN_FUNC: // handle func definition 
                $this->m_state->value = $e[1];
                break;
            case BcssTokens::TOKEN_VALUE:
            case BcssTokens::TOKEN_LITTERAL:
                $this->m_state->value = $e[1];

                if ($this->m_state->scope_directive && self::IsScopedDirective($this->m_state->scope_directive)) {
                    $fc = $this->m_state->scope_directive;
                    $v = $this->m_state->value;
                    if (is_array($fc)) {
                        $callback = array_slice($fc, 0, 2);
                        call_user_func_array($callback, [$v, $this, $this->m_state]);
                    } else {
                        $fc->loadScope($v, $this, $this->m_state);
                    }
                    $this->m_state->clearScope();
                    $this->m_state->value = null;
                    break;
                }
                if ($this->m_state->directive && ($this->m_state->mode == 0)) {
                    $this->_handleGlobalStateDirective();
                    break;
                }
                if ($this->m_state->mode == 1) {
                    // store property value 
                    $this->_updateStateDirective();
                }
                break;
            case BcssTokens::TOKEN_END_SELECTOR:
                if ($this->m_state->mode == 0) {
                    // + | store value - start
                    $v_tp = $this->m_state->value;
                    $this->m_state->mode = 1;
                    // +| passing value to property read
                    $this->_clearStateValue();
                    $this->m_state->property = $v_tp;
                } else {
                    igk_die('end selected not allowed');
                }
                break;
            case BcssTokens::TOKEN_END_VALUE:
                $this->m_state->mode = 0;
                if ($fc = $this->m_state->scope_directive) {
                    $fc($this->m_state->value);
                    $this->m_state->value = null;
                } else {
                    $this->m_state->value .= $this->m_state->value . $e[1];
                }
                break;
        }
        if ($this->m_listener) {
            $this->m_listener->handle($token);
        }
    }
    private static function _MergePList(&$list, array $data)
    {
        foreach ($data as $k => $v) {
            if (isset($list[$k])) {
                $list[$k] .= $v;
            } else {
                $list[$k] = $v;
            }
        }
    }
    private function _handleGlobalStateDirective()
    {
        if (is_array($this->m_state->directive)) {
            $n = $this->m_state->directive[1];
            if ($n == 'import') {
                $this->_updateStateDirective();
                self::_CleanState($this->m_state);
            }
        }
    }
    private static function _CleanState($state)
    {
        $state->directive = null;
        $state->directive_name = null;
        $state->value = null;
        $state->property = null;
    }
    private function _updateStateDirective()
    {
        // update lobal directive
        $v_dir = $this->m_state->directive;
        if ($v_dir) {
            if (is_object($v_dir) && ($v_dir instanceof IBcssValueHandler)) {
                $v_dir->handle($this, $this->m_state);
            } else if (is_array($v_dir)) {
                $fc = array_slice($v_dir, 0, 2);
                $n = $v_dir[1];
                $args = [$this, $this->m_state];
                if ($n == 'import') {
                    $args = [$this->m_state->value];
                }
                call_user_func_array($fc, $args);
                $this->m_state->mode = 0;
            } else if (is_callable($v_dir)){
                $v_dir($this, $this->m_state);
            }
        } else {
            // + | update state to current default directive media
            $bck = $this->m_state->directive_name;
            $this->m_state->directive_name = $this->default_media;
            $this->storeDefinition($this, $this->m_state);
            $this->m_state->directive_name = $bck;
        }
    }
    private function _clearStateValue()
    {
        $this->m_state->value = null;
    }
    public static function IsScopedDirective($directive): bool
    {
        return $directive && is_array($directive) && (igk_getv($directive, 2) == 'scoped');
    }
    /**
     * read scope value til end
     */
    private static function _ReadScoped(string $content, int &$pos): ?string
    {
        $v_sb = '';
        $ln = strlen($content);
        $end = false;
        $v_litteral = false;
        $v_tv = [];
        while ((!$end) && ($pos < $ln)) {
            $ch = $content[$pos];
            switch ($ch) {
                case '\'':
                case '"':
                    $v_litteral = true;
                    $v_sb .= igk_str_read_brank($content, $pos, $ch, $ch);
                    $ch = '';
                    $v_tv[] = $v_sb;
                    $v_sb = '';
                    break;
                case '}': // end scope 
                    $pos--;
                    $end = true;
                    continue 2;
                case ';':
                case '{':
                    if ($ch == '{')
                        $pos--;
                    $end = true;
                    continue 2;
                case ',':
                    if (!empty($v_sb =  trim($v_sb))) {
                        $v_tv[] = $v_sb;
                        $v_sb = '';
                        $ch = '';
                    }
                    break;
            }
            $pos++;
            $v_sb .= $ch;
        }
        if (!empty($g = trim($v_sb))) {
            $v_tv[] = $g;
        }
        if ($v_litteral || (count($v_tv) > 0)) {
            $v_sb = implode(',', $v_tv);
        }
        $v_sb = trim($v_sb);
        return $v_sb;
    }
    private static function _ReadName(string $content, &$pos)
    {
        $v = '';
        $ln = strlen($content);
        while ($pos < $ln) {
            $ch = $content[$pos];
            if (strpos(self::TOKENS, $ch) === false) {
                break;
            }
            $v .= $ch;
            $pos++;
        }
        if (empty($v)) {
            igk_die("not a valid name");
        }
        return $v;
    }
    /**
     * read selectory/property
     * @param string $content 
     * @param mixed &$pos 
     * @return string read property 
     * @throws Exception 
     */
    private static function _ReadSelector(string $content, &$pos)
    {
        $v = '';
        $ln = strlen($content);
        $end = false;
        $v_continue = false;
        $v_root = $pos;
        $v_pmark = ':';
        $trim = false;
        while ($pos < $ln) {
            $ch = $content[$pos];
            $v_continue = false;
            if (empty(trim($ch))) {
                if ($trim) {
                    $ch = '';
                } else
                    $trim = true;
            } else {
                $trim = false;
            }
            if ($ch == ';') {
                if (!empty($v)) {
                    if (($npos = strpos($v, $v_pmark)) === false)
                        igk_die('Read Selector : missing property definition. ' . $v);
                    $pos = $pos - (strlen($v) - $npos) - 1;
                    $v = substr($v, 0, $npos);
                    break;
                }
            }

            if ($ch == $v_pmark) {
                $gt = ($pos + 1) < $ln;
                $v_continue = ($pos > 0) && ($content[$pos - 1] === '\\') // escaped - \: 
                    || ($gt && ($content[$pos + 1] == $v_pmark)) // speudo-selector expected
                    || ($gt && (($gc = trim($content[$pos + 1])) != '') &&
                        !is_numeric($gc)
                        && preg_match('/[a-z]/i', $gc)
                    ) // speudo-selector expected
                ;
                if (!$v_continue) {
                    $pos--;
                    break;
                }
            }

            if ($ch == '(') {
                // + | in speudo selector ref func brank
                $func = igk_str_read_brank($content, $pos, ')', '(');
                $v .= $func;
                $ch = '';
            }
            if ($ch == '[') {
                $func = igk_str_read_brank($content, $pos, ']', '[');
                $v .= $func;
                $ch = '';
            }

            if (($ch == '%') && self::$sm_allow_printf_format) {
                // % escapted for list rendering 
                $v_continue = ($pos > 0) && ($content[$pos - 1] === '\\') // escaped - \% 
                ;
                if (!$v_continue) {
                    $pos--;
                    break;
                }
            }
            if (!$v_continue && ($ch != '\\')) { // escaped litteral
                if ((($ch == $v_pmark) || ($ch == '{')) && ($end)) {
                    $pos--;
                    break;
                }
                if ($ch && strpos(self::TOKENS_SELECTOR, $ch) === false) {
                    $pos--;
                    break;
                }
            }
            $end = empty(trim($ch)); //==' ';
            $v .= $ch;
            $pos++;
        }
        $v = trim($v);
        if (empty($v)) {
            igk_die("not a valid selector/property : " . $v);
        }
        $rm_sub = 0;
        if (false !== strrpos($v, $v_pmark, -1)) {
            if (igk_str_endwith($v, $v_pmark)) {
                while (false !== strrpos($v, $v_pmark, -1)) {
                    $v = substr($v, 0, -1);
                    $pos--;
                    $rm_sub = 1;
                }
            }
            // else {
            //     $v = substr($v, 0, $cpos);
            //     $pos = $v_root + $cpos - 1;
            // }
        }
        $pos -= $rm_sub;
        return $v;
    }
    /**
     * get directive callback
     * @param mixed $name 
     * @return array|object|IBcssDirectiveHandler
     * @throws IGKException 
     */
    public function getDirectiveCallback($name)
    {
        $definition = &$this->m_directive_definitions;
        if (is_null($definition)) {
            $definition = $this->initDirectiveDefinition();
        }
        if (false !== strpos($name, ',')) {
            // multi bcss directive definition 
            $tq = explode(',', $name);
            $muti_definition_directive = new BcssMultiDirective;
            while (count($tq) > 0) {
                $q = array_shift($tq);
                $s = igk_getv($definition, $q);
                $muti_definition_directive->add($q, $s);
            }
            return $muti_definition_directive;
        }

        return igk_getv($definition, $name) ?? BcssDirectiveFactory::Create($name);
    }
    protected function initDirectiveDefinition(): array
    {
        return [
            self::DEFAULT_MEDIA_KEY => [$this, 'storeDefinition'],
            '@cl' => [$this, 'storeColorDefinition'],
            '@colors' => new BcssColorDefinitionHandler,
            '@sm-screen' => [$this, 'storeDefinition'],
            '@xsm-screen' => [$this, 'storeDefinition'],
            '@lg-screen' => [$this, 'storeDefinition'],
            '@xlg-screen' => [$this, 'storeDefinition'],
            '@xxlg-screen' => [$this, 'storeDefinition'],
            '@sm' => [$this, 'storeDefinition'],
            '@xsm' => [$this, 'storeDefinition'],
            '@lg' => [$this, 'storeDefinition'],
            '@xlg' => [$this, 'storeDefinition'],
            '@xxlg' => [$this, 'storeDefinition'],
            '@root' => [$this, 'storeRootDefinition'],
            '@apply' => [$this, 'applyValue', 'scoped'],
            '@global' => [$this, 'applyGlobalValue', 'scoped'],
            '@import' => [$this, 'import', 'global|scoped'],
            "@charset" => new BcssStoreCharsetHandler,
            "@page" => new BcssPageDefinitionHandler,
            "@document" => new BcssDocumentDefinitionHandler, //[$this, 'storeDocument','global'],
            "@font-face" => new BcssFontFaceDefinitionHandler, // [$this, 'storeFontFace','global'],
            "@keyframes" => new  BcssKeyFramesDefinitionHandler,
            "@supports" => new BcssSupportsDefinitionHandler, // [$this, 'storeSupports','global'],
        ];
    }


    /**
     * imported scoped file
     * @param string $file 
     * @return void 
     * @throws Error 
     * @throws IGKException 
     * @throws ArgumentTypeNotValidException 
     * @throws ReflectionException 
     */
    public function import(string $file)
    {
        if (!$this->directory) {
            igk_die('missing directory');
        }
        if (file_exists($f = Path::Combine($this->directory, $file))) {
            $g = static::ParseFormFile($f);
            //+ | merge definition
            $this->m_definitions = array_merge($this->m_definitions, $g->m_definitions);
            $this->m_theme->load_data($g->m_theme->to_array());
        }
    }
    public function storeColorDefinition($k, $v)
    {
        $this->m_theme->cl[$k] = $v;
    }

    public function storeDefinition(BcssParser $reader, BcssStateInfo $state)
    {
        if (!$state->def) {
            $state->def = $reader->_initStateDefinition($state->registerThemes);
        }
        $v_p = $state->property;
        $r = [];
        if (is_null($v_p)) {
            $r[] = $state->value;
        } else {
            if (empty($v_p) || !preg_match(self::CSS_PROPS_REGEX, $v_p)) {
                igk_die('property is empty/or invalid');
            }
            $r[$v_p] = $state->value;
        }
        //+ |
        $state->def->def = array_merge($state->def->def ?? [], $r);
        // + | clear property 
        $state->property = null;
        $state->value = null;
    }
    /**
     * init state definition 
     * @return BcssDefInfo 
     * @throws Exception 
     */
    private function _initStateDefinition(?array $registerThemes = null)
    {
        $key = $this->m_state->directive_name ?? $this->default_media;
        if (empty($key)) {
            igk_die("missing directive name.");
        }
        $property = substr($key, 1);
        $v_selector = $this->m_state->getSelector();
        if (empty($v_selector)) {
            igk_die('selector is empty.');
        }
        $p = $this->m_state->def;
        $key_n = igk_getv([
            'sm' => 'sm-screen',
            'xsm' => 'xsm-screen',
            'lg' => 'lg-screen',
            'xlg' => 'xlg-screen',
            'xxlg' => 'xxlg-screen',
        ], $property, $property);
        if ($registerThemes && ($gr = igk_getv($registerThemes, $key_n))) {
            // + get from themings 
            $def = $gr;
            if ($def->selector != $v_selector) {
                $gr->definitions[$def->selector] = $gr;
                $def = igk_getv($def->definitions, $v_selector);
                if (!$def) {
                    $def = new BcssDefInfo;
                    $def->themeProperty = $key_n;
                    $def->definitions = &$gr->definitions;
                } else {
                    $def = new BcssDefInfo;
                    $def->themeProperty = 'def';
                }
            }
        } else {
            $def = new BcssDefInfo;
            $def->themeProperty = $key_n;
        }
        $def->parent = $p;
        $def->selector = $v_selector;
        return $def;
    }
    /**
     * store root definition 
     * @param mixed $k 
     * @param mixed $v 
     * @return void 
     */
    public function storeRootDefinition(BcssParser $reader, BcssStateInfo $v)
    {
        $k = $v->property;
        $vv = $v->value;
        $root = &$reader->m_theme->getRootReference();
        $root[$k] = $vv;
    }

    public function applyGlobalValue($v, BcssParser $parser = null, BcssStateInfo $state = null)
    {
        $state->useGlobalStyleDefinition = true;
        $this->_bindStateValue($v, $parser, $state, 'sys');
    }
    public function applyValue($v, BcssParser $parser = null, BcssStateInfo $state = null)
    {
        $this->_bindStateValue($v, $parser, $state, 'th');
    }

    /**
     * bind state value
     * 
     * @param mixed $v 
     * @param BcssParser|null $parser 
     * @param BcssStateInfo|null $state 
     * @param string $type 
     * @return void 
     * @throws Exception 
     */
    protected function _bindStateValue($v, BcssParser $parser = null, BcssStateInfo $state = null, $type = 'sys')
    {
        if (!$state->def) {
            $state->def = $parser->_initStateDefinition();
        }
        // convert to expression to array
        $v_tab = self::_ArrayDefinition($v);

        $tab = array_map(function ($i) use ($type) {
            return sprintf('(%s:%s)', $type, trim($i));
        }, $v_tab);

        $state->def->def = array_merge($state->def->def, $tab); //[sprintf('(th:%s)', $v)]);
    }
    /**
     * parse scope data and return array definition args
     */
    private function _ArrayDefinition(string $v): array
    {
        $ln = strlen($v);
        $tab = [];
        $pos = 0;
        $v_v = '';
        while ($pos < $ln) {
            $ch = $v[$pos];

            switch ($ch) {
                case ',':
                    if (!empty($v_v = trim($v_v))) {
                        $tab[] = $v_v;
                    }
                    $v_v = '';
                    $ch = '';
                    break;
                case '"':
                case '\'':
                    $v_c = igk_str_read_brank($v, $pos, $ch, $ch);
                    $v_v .= igk_str_remove_quote($v_c);
                    $ch = '';
                    break;
            }
            $v_v .= $ch;
            $pos++;
        }
        if (!empty($v_v = trim($v_v))) {
            $tab[] = $v_v;
        }
        return $tab;
    }
    /**
     * register theme 
     * @return void 
     */
    private function _moveTop()
    {
        $this->m_state->updateRegistry();
        // if ($def = $this->m_state->def) {
        //     if ($def->themeProperty && !isset($this->m_state->registerThemes[$def->themeProperty])) {
        //         $this->m_state->registerThemes[$def->themeProperty] = $def;
        //     }
        //     $this->m_state->def = $def->parent;
        // } else
        //     $this->m_state->def = null;  
        $this->_popSelector();
    }
    /**
     * store temp style
     * @return void 
     */
    private function _storeCss()
    {
        $fc = function($def){
            ksort($def->def);
            $css = '';
            foreach ($def->def as $k => $v) {
                if (!is_numeric($k))
                    $css .= sprintf('%s:%s;', $k, $v);
                else {
                    $css .= sprintf('%s;', $v);
                }
            }
            if (is_null($def->parent)) {
                $pname = str_replace('-', '_', $def->themeProperty);
                $g = $this->m_theme->{$pname};
                $g[$def->selector] = $css;
                foreach ($def->keylist as $k => $v) {
                    $g[$k] = $v;
                }
            } else {
                $def->keylist = array_merge([$def->selector => $css], $def->keylist);
                self::_MergePList($def->parent->keylist, $def->keylist);
            }
        };
        if ( ($v_dir = $this->m_state->directive) instanceof BcssMultiDirective){
            $v_dir->storeCssTheme($this, $fc, function(){
                $this->_moveTop();
            });
            $this->_moveTop();
            return;
        }
        $def = $this->m_state->def;
        if (!$def || empty($def->def)) {
            $this->_moveTop();
            return;
        }
        $fc($def);
        // ksort($def->def);
        // $css = '';
        // foreach ($def->def as $k => $v) {
        //     if (!is_numeric($k))
        //         $css .= sprintf('%s:%s;', $k, $v);
        //     else {
        //         $css .= sprintf('%s;', $v);
        //     }
        // }
        // if (is_null($def->parent)) {
        //     $pname = str_replace('-', '_', $def->themeProperty);
        //     $g = $this->m_theme->{$pname};
        //     $g[$def->selector] = $css;
        //     foreach ($def->keylist as $k => $v) {
        //         $g[$k] = $v;
        //     }
        // } else {
        //     $def->keylist = array_merge([$def->selector => $css], $def->keylist);
        //     self::_MergePList($def->parent->keylist, $def->keylist);
        // }
        $this->_moveTop();
    }
    /**
     * 
     * @param mixed $name 
     * @param mixed $styles 
     * @return void 
     */
    function updateThemeProperties($name, $styles)
    {
        $pname = str_replace('-', '_', $name);
        $g = $this->m_theme->{$pname};
        foreach ($styles as $k => $v) {
            foreach ($v as $i => $j) {
                $g[$i] = $j;
            }
        }
    }
}
