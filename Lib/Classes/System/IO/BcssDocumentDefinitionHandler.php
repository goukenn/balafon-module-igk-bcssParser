<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssDocumentDefinitionHandler.php
// @date: 20240211 00:54:20
namespace igk\bcssParser\System\IO;

use IGK\System\Html\Css\CssDefintionPropertyLoader;
use IGK\System\Html\Css\CssMedia;
use IGK\System\Html\Css\CssProperty;

///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssDocumentDefinitionHandler extends BcssPageDefinitionHandler{

    /**
    * auto generate doc.
    * @param mixed $theme
    * @param mixed $key
    * @param mixed $cl
    */
    protected function storeData($theme, $key, $cl)
    {
       $media = $theme->reg_media("@document ".$key); 
      
       $media->load_data(["def"=>self::ConvertDefinition($cl)]);
    }
}