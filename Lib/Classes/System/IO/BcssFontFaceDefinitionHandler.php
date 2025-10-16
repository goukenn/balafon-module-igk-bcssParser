<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssFontFaceDefinitionHandler.php
// @date: 20240211 00:53:07
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
* 
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssFontFaceDefinitionHandler  extends BcssPageDefinitionHandler{
    protected function storeData($theme, $key, $cl){
        $g = $theme->reg_media(trim('@font-face '.$key));  
        $g->load_data(['props'=>$cl]); 
    }
}