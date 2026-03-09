<?php
// @author: C.A.D. BONDJE DOUE
// @file: IBcssDirectiveHandler.php
// @date: 20240113 15:49:33
namespace igk\bcssParser\System\IO;


///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
interface IBcssDirectiveHandler{

    /**
    * auto generate doc.
    * @param BcssParser $parser
    * @param string $content
    * @param mixed & $pos
    */
    function handle(BcssParser $parser, string $content,& $pos);
}