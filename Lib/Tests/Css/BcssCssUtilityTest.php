<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssCssUtilityTest.php
// @date: 20260309 09:23:48
namespace igk\bcssParser\Tests\Css;

use igk\bcssParser\Css\BcssCssUtility;
use IGK\Tests\Controllers\ModuleBaseTestCase;

/**
 * 
 * @package igk\bcssParser\Tests\Css
 * @author C.A.D. BONDJE DOUE
 */
class BcssCssUtilityTest extends ModuleBaseTestCase
{
    public function test_bcsscssu_split()
    {
        $r = BcssCssUtility::SplitLitteral('Bonjour, Tous le monde', [7]);
        $this->assertTrue(['Bonjour', ' Tous le monde'] == $r);
        $r = BcssCssUtility::SplitLitteral('a,b,c', [1,3]);
        $this->assertTrue(['a','b','c'] == $r);
        $r = BcssCssUtility::SplitLitteral('a,b,c', [3,2]);
        $this->assertTrue(['a,b'] == $r);
    }
    public function test_bcsscssu_range_split()
    {
        $s='Bonjour, Tous le monde';
        $tr = [strpos($s, 'Tous')];
        $tr[] = $tr[0]+ 4;
        $r = BcssCssUtility::SplitLitteral($s, [$tr]);
        $this->assertTrue(['Bonjour, ', ' le monde'] == $r);
    }
}
