<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssKeyFramesDefinitionHandler.php
// @date: 20240211 00:51:27
namespace igk\bcssParser\System\IO;

use IGK\System\Html\Css\CssParser;

///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssKeyFramesDefinitionHandler extends BcssPageDefinitionHandler{

    /**
    * auto generate doc.
    * @param mixed $theme
    * @param mixed $key
    * @param mixed $cl
    */
    protected function storeData($theme, $key, $cl){
        $g = $theme->reg_media('@keyframes '.$key);  
        $g->load_data(['def'=>self::ConvertDefinition($cl)]);
     }
}