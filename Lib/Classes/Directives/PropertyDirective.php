<?php
// @author: C.A.D. BONDJE DOUE
// @file: PropertyDirective.php
// @date: 20240213 15:57:10
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class PropertyDirective extends BcssDirectiveFactory{
    protected $type = '@property';
    public function handle(BcssParser $parser, string $content, &$pos) { 
        list($key, $cl) = BcssParsingUtility::ReadBlock($content, $pos);
        $m = $parser->getTheme()->reg_media('@property '.$key);
        $m->load_data(['props'=>$cl]);
    }
}