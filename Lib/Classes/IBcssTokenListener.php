<?php
// @author: C.A.D. BONDJE DOUE
// @file: IBcssTokenListener.php
// @date: 20240112 16:42:17
namespace igk\bcssParser;
///<summary></summary>
/**
* 
* @package igk\bcssParser
* @author C.A.D. BONDJE DOUE
*/
interface IBcssTokenListener{
    /**
     * handle token info
     * @param array $token_info 
     * @return mixed 
     */
    function handle(array $token_info);
}