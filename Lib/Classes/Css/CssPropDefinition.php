<?php
// @author: C.A.D. BONDJE DOUE
// @file: CssPropDefinition.php
// @date: 20260309 13:59:34
namespace igk\bcssParser\Css;


/**
* 
* @package igk\bcssParser\Css
* @author C.A.D. BONDJE DOUE
*/
class CssPropDefinition{
    private $m_def;
    public function __construct($def)
    {
        $this->m_def = $def ?? igk_die('required definition');
    }
    public function render(){
        $td = [];
        foreach($this->m_def as $k=>$v){
            $td[] = sprintf('%s{%s}', $k, $v);
        }
        return implode('', $td); 
    }
}