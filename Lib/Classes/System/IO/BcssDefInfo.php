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
    * auto generate doc.
    * @var ?BcssDefInfo
    */
    var $parent;

    /**
    * auto generate doc.
    * @var mixed
    */
    var $def = [];

    /**
    * auto generate doc.
    * @var mixed
    */
    var $themeProperty;

    /**
    * auto generate doc.
    * @var mixed
    */
    var $selector;

    /**
    * auto generate doc.
    * @var mixed
    */
    var $definitions;
    /**
     * store array key list definition 
     * @var array
     */
    var $keylist = [];
}