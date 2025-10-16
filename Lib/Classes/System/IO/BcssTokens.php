<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssTokens.php
// @date: 20240112 15:31:05
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
* 
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssTokens{
    const TOKEN_DIRECTIVE = 0x1;
    const TOKEN_SELECTOR = 0x2;
    const TOKEN_NAME = 0x3;
    const TOKEN_FUNC = 0x4;
    const TOKEN_BRANK = 0x5;
    const TOKEN_COMMENT = 0x6;
    const TOKEN_LITTERAL = 0x7;
    const TOKEN_VALUE= 0x8;
    const TOKEN_END_SELECTOR =0x9;
    const TOKEN_END_VALUE = 0xa;
    const TOKEN_ARRAY_EXPRESS = 0xb;
    const TOKEN_THEME_DEF = 0xc;

}