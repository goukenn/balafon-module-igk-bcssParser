<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssParsingUtility.php
// @date: 20240212 11:40:01
namespace igk\bcssParser\Helper;
use Exception;
use igk\bcssParser\System\IO\BcssParser;
use IGK\System\Html\Css\CssParser;
use IGKException;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Helper
* @author C.A.D. BONDJE DOUE
*/
class BcssParsingUtility{
    /**
     * 
     * @param string $content 
     * @param mixed &$pos 
     * @return (string|array)[] 
     * @throws IGKException 
     * @throws Exception 
     */
    public static function ReadBlock(string $content, & $pos){
        $cps = strpos($content, "{", $pos);
        $key = trim(substr($content, $pos, $cps-$pos));  
        $pos = $cps; 
        $m = trim(igk_str_read_brank($content, $pos, "}", "{"), '{}');
        $p = CssParser::Parse($m);
        $g = null; 
        $cl =  array_merge($g??[], $p->to_array());
        return [$key, $cl];
    }
    /**
     * read block as bcss technique
     */
    public static function ReadBcssBlock(string $content, & $pos, ?string $basedir=null, bool $allow_printf_format=false){
        $cps = strpos($content, "{", $pos);
        $key = trim(substr($content, $pos, $cps-$pos));  
        $pos = $cps; 
        $m = trim(igk_str_read_brank($content, $pos, "}", "{"), '{}');
        $p = BcssParser::ParseFromContent($m, $basedir, $allow_printf_format);// CssParser::Parse($m);
        $p = CssParser::Parse($p->render(true, true));
        $g = null; 
        $cl =  array_merge($g??[], $p->to_array());
        return [$key, $cl];
    }
}