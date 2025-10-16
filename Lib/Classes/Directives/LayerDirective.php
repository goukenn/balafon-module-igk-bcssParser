<?php
// @author: C.A.D. BONDJE DOUE
// @file: LayerDirective.php
// @date: 20240213 15:57:18
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class LayerDirective extends BcssDirectiveFactory{
    public function handle(BcssParser $parser, string $content, &$pos) { 
        list($key, $cl) = BcssParsingUtility::ReadBlock($content, $pos);
        $m = $parser->getTheme()->reg_media('@layer '.$key);
        $m->load_data(['props'=>$cl]);
    }
}