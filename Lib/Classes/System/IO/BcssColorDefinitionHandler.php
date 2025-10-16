<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssColorDefinitionHandler.php
// @date: 20240113 15:48:54
namespace igk\bcssParser\System\IO;

use IGK\System\Html\Css\CssParser;
use function igk_resources_gets as __;
///<summary></summary>
/**
* 
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssColorDefinitionHandler implements IBcssDirectiveHandler{

    public function handle(BcssParser $parser, string $content, &$pos) { 
        $v_tpos = strpos($content, "{", $pos);
        $v_theme = null;
        if (false !== $v_tpos){
            $v_theme = trim(substr($content, $pos, $v_tpos-$pos));
            $pos = $v_tpos;
            if (!empty($v_theme)&& ((!in_array($v_theme, ['dark','light']) && !$parser->isSupportTheme($v_theme)))){
                igk_die(sprintf(__('[%s] not a supported theme'), $v_theme ));
            }

        }
        $m = trim(igk_str_read_brank($content, $pos, "}", "{"), '{}');
        $p = CssParser::Parse($m);
        $v_t = $parser->getTheme();
        $g = & $v_t->getRootReference();
        $cl =  array_merge($g??[], $p->to_array());
        if (empty($v_theme)){
            $v_t->setColors($cl);
        }else{
            $v_t->bindThemeColor($v_theme, $cl);
        }
    }

}