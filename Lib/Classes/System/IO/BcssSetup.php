<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssSetup.php
// @date: 20240210 22:00:29
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
* use to store setup directive
* @package igk\bcssParser\System\IO
* @author C.A.D. BONDJE DOUE
*/
class BcssSetup{
    private $m_configData = [];

    public function __set(string $name, $value){
        $this->m_configData[$name] = $value;
    }
    public function __get($name)
    {
        return igk_getv($this->m_configData, $name); 
    }
    public function getRoots(){
        return array_filter($this->m_configData, function($n, $c){
            if (preg_match('/^--/', $c)){
                return true;
            }
            return false;
        }, 1);
    }
}