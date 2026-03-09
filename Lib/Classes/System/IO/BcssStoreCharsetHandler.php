<?php
// @author: C.A.D. BONDJE DOUE
// @filename: BcssStoreCharsetHandler.php
// @date: 20240212 10:20:13
// @desc: 

namespace igk\bcssParser\System\IO;

use IGK\Css\CssThemeOptions;
use IGK\Helper\StringUtility;

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
*/
class BcssStoreCharsetHandler implements IBcssDirectiveHandler{

    /**
    * auto generate doc.
    * @param BcssParser $parser
    * @param string $content
    * @param mixed & $pos
    */
    public function handle(BcssParser $parser, string $content, &$pos) {
        $s = trim(StringUtility::ReadLine($content, $pos),' ;'); 
     
        $theme = $parser->getTheme();
        $theme->setCharset($s); 
     }

}