<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssFontFaceDefinitionHandler.php
// @date: 20240211 00:53:07
namespace igk\bcssParser\System\IO;


///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssFontFaceDefinitionHandler  extends BcssPageDefinitionHandler{

    /**
    * auto generate doc.
    * @param mixed $theme
    * @param mixed $key
    * @param mixed $cl
    */
    protected function storeData($theme, $key, $cl){
        $g = $theme->reg_media(trim('@font-face '.$key));  
        $g->load_data(['props'=>$cl]); 
    }
}