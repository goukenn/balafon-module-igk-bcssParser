<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssSupportsDefinitionHandler.php
// @date: 20240211 00:52:53
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
 * 
 * @package igk\bcssParser\System\IO
 * @author C.A.D. BONDJE DOUE
 */
class BcssSupportsDefinitionHandler  extends BcssPageDefinitionHandler
{
    protected function storeData($theme, $key, $cl)
    {
        $g = $theme->reg_media('@supports ' . $key);
        $g->load_data(["def" => self::ConvertDefinition($cl)]);
    }
}
