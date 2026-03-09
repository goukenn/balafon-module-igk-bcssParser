<?php
// @author: C.A.D. BONDJE DOUE
// @file: %modules%/igk/bcssParser/.global.php
// @date: 20240112 15:20:05
// + module entry file 

use igk\bcssParser\Css\BcssConverter;

if (false == function_exists('igk_css_convert_to_bcss')) {
    function igk_css_convert_to_bcss(string $src): string
    {
        return BcssConverter::ConvertFromCss($src);
    }
}
