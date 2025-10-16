<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssMultiDirective.php
// @date: 20250318 20:17:28
namespace igk\bcssParser;
use igk\bcssParser\System\IO\BcssParser;
use igk\bcssParser\System\IO\IBcssDirectiveHandler;
///<summary></summary>
/**
* 
* @package igk\bcssParser
* @author C.A.D. BONDJE DOUE
*/
class BcssMultiDirective{
    private $m_list = [];
    private $m_defs = [];
    public function add(string $name, $definition){
        $this->m_list[$name] = $definition;
    }
    public function __invoke($parser, $state)
    {
        $v_p = $state->property;
        $v_v = $state->value;
        $v_dirname = $state->directive_name;
        foreach($this->m_list as $k=>$v){
            $state->def = null;
            $state->directive_name = $k;
            $state->property = $v_p;
            $state->value = $v_v;
            call_user_func_array($v, func_get_args());
            $this->m_defs[$k] = $state->def;
            $state->updateRegistry();
        }
    }
    public function storeCssTheme(BcssParser $parser, $fc){
        foreach($this->m_defs as $k=>$v){
            $fc($v);
        }
    }
}