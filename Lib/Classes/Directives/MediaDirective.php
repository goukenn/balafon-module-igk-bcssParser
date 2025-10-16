<?php
// @author: C.A.D. BONDJE DOUE
// @file: MediaDirective.php
// @date: 20240214 08:26:58
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
use IGK\System\Html\Css\CssUtils;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class MediaDirective extends BcssDirectiveFactory{
    var $type = '@media';
    public function handle(BcssParser $parser, string $content, &$pos) { 
        empty($this->type) ?? igk_die("type is empty");
        list($key, $cl) = BcssParsingUtility::ReadBcssBlock($content, $pos, $parser->directory);
        $m = $parser->getTheme()->reg_media($key);
        foreach($cl as $k=>$v){ 
            $m[$k] = CssUtils::GlueArrayDefinition($v); // implode('', );
        } 
    }
}