<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssMergeMediaTest.php
// @date: 20250318 19:50:11
namespace igk\bcssParser\Tests;
use igk\bcssParser\System\IO\BcssParser;
use IGK\Tests\Controllers\ModuleBaseTestCase;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Tests
* @author C.A.D. BONDJE DOUE
*/
class BcssMergeMediaTest extends ModuleBaseTestCase{
    public function test_bcssreader_merge_media(){
        $src = implode("\n", [
            '@def, @xsm-screen{',
            'div{color: red;}',
            '}'
        ]);
        $f = BcssParser::ParseFromContent($src);
        $this->assertEquals('/* <!-- Attributes --> */div{color:red;}/* <!-- end:Attributes --> */@media (max-width:320px){div{color:red;}}', $f->render(true));
    }
}