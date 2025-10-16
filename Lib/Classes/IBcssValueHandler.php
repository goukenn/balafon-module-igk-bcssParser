<?php
// @author: C.A.D. BONDJE DOUE
// @file: IBcssValueHandler.php
// @date: 20240113 13:57:48
namespace igk\bcssParser;
use igk\bcssParser\System\IO\BcssParser;
use igk\bcssParser\System\IO\BcssStateInfo;
///<summary></summary>
/**
* 
* @package igk\bcssParser
* @author C.A.D. BONDJE DOUE
*/
interface IBcssValueHandler{
    function handle(BcssParser $parser, BcssStateInfo $state):void;
}