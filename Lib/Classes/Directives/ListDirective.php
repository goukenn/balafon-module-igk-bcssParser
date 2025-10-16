<?php
// @author: C.A.D. BONDJE DOUE
// @file: ListDirective.php
// @date: 20240214 08:52:24
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
use IGK\System\Html\Css\CssUtils;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class ListDirective extends BcssDirectiveFactory{
    protected $type = '@list';
    public function handle(BcssParser $parser, string $content, &$pos)
    {
        //BcssParser::ListRendering(true);
        list($key, $cl) = BcssParsingUtility::ReadBcssBlock($content, $pos, $parser->directory, true);
        if ($key){
            $tab = igk_json_parse($key); json_decode($key);
            if(is_array($tab)){
                $m = $parser->getTheme();
                $tc = 1;
                while(count($tab)>0){
                    $q = array_shift($tab);
                    $c = is_numeric($q) ? intval($q) : $tc;
                    foreach($cl as $k=>$v){ 
                        $k = sprintf($this->_treat($k), $c);
                        $m[$k] = sprintf($this->_treat(CssUtils::GlueArrayDefinition($v)), $q); // implode('', );
                    } 
                    $tc++;
                }
            }
        }
    }
    private function _treat(string $g){
        return str_replace("\\%s",'%s',$g);
    }
}