<?php
// @author: C.A.D. BONDJE DOUE
// @file: PropertyDirective.php
// @date: 20240213 15:57:10
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class PropertyDirective extends BcssDirectiveFactory{

    /**
    * auto generate doc.
    * @var mixed
    */
    protected $type = '@property';

    /**
    * auto generate doc.
    * @param BcssParser $parser
    * @param string $content
    * @param mixed & $pos
    */
    public function handle(BcssParser $parser, string $content, &$pos) { 
        list($key, $cl) = BcssParsingUtility::ReadBlock($content, $pos);
        $m = $parser->getTheme()->reg_media('@property '.$key);
        $m->load_data(['props'=>$cl]);
    }
}