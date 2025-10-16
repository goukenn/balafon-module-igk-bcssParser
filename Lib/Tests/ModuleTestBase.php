<?php
// @author: C.A.D. BONDJE DOUE
// @date: 20240112 15:20:05
namespace igk\bcssParser\Tests;
use IGK\Tests\BaseTestCase;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Tests
* @author C.A.D. BONDJE DOUE
*/
abstract class ModuleTestBase extends BaseTestCase{
	public static function setUpBeforeClass(): void{
	   igk_require_module(\igk\bcssParser::class);
	}
}