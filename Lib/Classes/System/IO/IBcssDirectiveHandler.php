<?php
// @author: C.A.D. BONDJE DOUE
// @file: IBcssDirectiveHandler.php
// @date: 20240113 15:49:33
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
* 
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
interface IBcssDirectiveHandler{
    function handle(BcssParser $parser, string $content,& $pos);
}