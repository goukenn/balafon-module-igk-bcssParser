<?php
// @author: C.A.D. BONDJE DOUE
// @file: FontFeatureValuesDirective.php
// @date: 20240213 15:54:58
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directive
* @author C.A.D. BONDJE DOUE
*/
class FontFeatureValuesDirective extends BcssDirectiveFactory{
    public function handle(BcssParser $parser, string $content, &$pos) { 
        list($key, $cl) = BcssParsingUtility::ReadBlock($content, $pos);
        $m = $parser->getTheme()->reg_media('@font-feature-values '.$key);
        $m->load_data(['props'=>$cl]);
    }
}