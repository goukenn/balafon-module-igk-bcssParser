<?php
// @author: C.A.D. BONDJE DOUE
// @filename: BcssStoreCharsetHandler.php
// @date: 20240212 10:20:13
// @desc: 

namespace igk\bcssParser\System\IO;

use IGK\Css\CssThemeOptions;
use IGK\Helper\StringUtility;

class BcssStoreCharsetHandler implements IBcssDirectiveHandler{

    public function handle(BcssParser $parser, string $content, &$pos) {
        $s = trim(StringUtility::ReadLine($content, $pos),' ;'); 
     
        $theme = $parser->getTheme();
        $theme->setCharset($s); 
     }

}