<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssPageDefinitionHandler.php
// @date: 20240211 00:36:16
namespace igk\bcssParser\System\IO;

use IGK\System\Html\Css\CssDefintionPropertyLoader;
use IGK\System\Html\Css\CssParser;
use IGKMedia;

///<summary></summary>
/**
* 
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssPageDefinitionHandler implements IBcssDirectiveHandler{
    public static function ConvertDefinition($cl){
      $def = [];
      $out = [];
      foreach($cl as $k=>$v){
           if (!isset($def[$k])){
               $def[$k] = new CssDefintionPropertyLoader;
           }
           $def[$k]->load($v);
           $out[$k] = $def[$k].'';
      }
      ksort($out); 
      return $out;
    }
    public function handle(BcssParser $parser, string $content, &$pos) {
        $cps = strpos($content, "{", $pos);
        $key = trim(substr($content, $pos, $cps-$pos));  
        $pos = $cps; 
        $m = trim(igk_str_read_brank($content, $pos, "}", "{"), '{}');
        $p = CssParser::Parse($m);
        $g = null; 
        $cl =  array_merge($g??[], $p->to_array());
      
        $this->storeData($parser->getTheme(), $key, $cl);
       
     }
     protected function storeData($theme, $key, $cl){
        $g = $theme->reg_media('@page '.$key);  
        $g->load_data(['props'=> $cl]);
     }

}