<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssTokens.php
// @date: 20240112 15:31:05
namespace igk\bcssParser\System\IO;


///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssTokens{

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_DIRECTIVE = 0x1;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_SELECTOR = 0x2;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_NAME = 0x3;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_FUNC = 0x4;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_BRANK = 0x5;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_COMMENT = 0x6;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_LITTERAL = 0x7;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_VALUE= 0x8;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_END_SELECTOR =0x9;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_END_VALUE = 0xa;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_ARRAY_EXPRESS = 0xb;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_THEME_DEF = 0xc;

    /**
    * auto generate doc.
    * @var mixed
    */
    const TOKEN_PROPERTY = 0xd;

}