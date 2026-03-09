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
* auto generate doc.
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class MediaDirective extends BcssDirectiveFactory{

    /**
    * auto generate doc.
    * @var mixed
    */
    var $type = '@media';

    /**
    * auto generate doc.
    * @param BcssParser $parser
    * @param string $content
    * @param mixed & $pos
    */
    public function handle(BcssParser $parser, string $content, &$pos) { 
        empty($this->type) ?? igk_die("type is empty");
        list($key, $cl) = BcssParsingUtility::ReadBcssBlock($content, $pos, $parser->directory);
        $m = $parser->getTheme()->reg_media($key);
        foreach($cl as $k=>$v){ 
            $m[$k] = CssUtils::GlueArrayDefinition($v); // implode('', );
        } 
    }
}