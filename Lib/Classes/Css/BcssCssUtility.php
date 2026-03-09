<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssCssUtility.php
// @date: 20260309 09:20:29
namespace igk\bcssParser\Css;


/**
 * 
 * @package igk\bcssParser\Css
 * @author C.A.D. BONDJE DOUE
 */
abstract class BcssCssUtility
{
    /**
     * split litteral with range 
     * @param string $haystack 
     * @param mixed $range pass a sorted range index
     * @return array 
     */
    public static function SplitLitteral(string $haystack, array $range): array
    {
        $v_offset = 0;
        $v_t = [];
        while (count($range) > 0) {
            $q = array_shift($range);
            if (is_array($q)) {
                $to = $q[1];
                $q = $q[0];
            } else {
                $to = $q + 1;
            }
            if ($q < $v_offset) {
                return $v_t;
            }
            $s = substr($haystack, $v_offset, $q - $v_offset);
            $v_offset = $to;
            $v_t[] = $s;
        }
        if (!empty(trim($s = substr($haystack, $v_offset)))) {
            $v_t[] = $s;
        }
        return array_filter($v_t);
    }
}
