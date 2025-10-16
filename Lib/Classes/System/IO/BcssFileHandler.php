<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssFileHandler.php
// @date: 20240115 10:38:32
namespace igk\bcssParser\System\IO;

use IGK\System\IO\FileHandler;

///<summary></summary>
/**
* 
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssFileHandler extends FileHandler{
    /**
     * required options to defined
     * @var mixed
     */
    var $option;

    /**
     * transform context string
     */
    public function transform(string $content) { 
        $bdir = igk_getv($this->option,'baseDir'); 
        $g = BcssParser::ParseFromContent($content, $bdir);
        return $g->render(true, true);
    }

}