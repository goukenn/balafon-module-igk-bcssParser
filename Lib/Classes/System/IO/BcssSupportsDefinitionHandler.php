<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssSupportsDefinitionHandler.php
// @date: 20240211 00:52:53
namespace igk\bcssParser\System\IO;


///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssSupportsDefinitionHandler  extends BcssPageDefinitionHandler
{

    /**
    * auto generate doc.
    * @param mixed $theme
    * @param mixed $key
    * @param mixed $cl
    */
    protected function storeData($theme, $key, $cl)
    {
        $g = $theme->reg_media('@supports ' . $key);
        $g->load_data(["def" => self::ConvertDefinition($cl)]);
    }
}
