<?php
// @author: C.A.D. BONDJE DOUE
// @file: NamespaceDirective.php
// @date: 20240212 11:24:16
namespace igk\bcssParser\Directives;
use igk\bcssParser\System\IO\BcssParser;
use IGK\Helper\StringUtility;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
class NamespaceDirective extends BcssDirectiveFactory{
    public function handle(BcssParser $parser, string $content, &$pos) {
        $ns = trim(StringUtility::ReadLine($content, $pos));
        $parser->getTheme()->setNamespace($ns);
     } 
}