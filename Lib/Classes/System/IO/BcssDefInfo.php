<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssDefInfo.php
// @date: 20240113 14:26:31
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
* used to store css read type definition
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssDefInfo{
    /**
     * 
     * @var ?BcssDefInfo
     */
    var $parent;
    var $def = [];
    var $themeProperty;
    var $selector;
    var $definitions;
    /**
     * store array key list definition 
     * @var array
     */
    var $keylist = [];
}