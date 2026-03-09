<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssCssEntry.php
// @date: 20260308 16:34:40
namespace igk\bcssParser\Css;


/**
* 
* @package igk\bcssParser\Css
* @author C.A.D. BONDJE DOUE
*/
class BcssCssEntry{
    private $m_def;
    private $m_childs;
    private $m_parent;

    public function __construct()
    {
        $this->m_def = [];
        $this->m_childs = [];
    }
    /**
     * get children
     * @return mixed 
     */
    public function getChildren(){
        return $this->m_childs;
    }
    /**
     * get parent from 
     * @return mixed 
     */
    public function getParent(){
        return $this->m_parent;
    }
    public function add(BcssCssEntry $child,string $key) {
        ($child===$this) && igk_die('not allowed');
        $this->m_childs[$key] = $child;
        $child->m_parent = $this;
    }


    /**
     * render css entry
     * @return string 
     */
    public function render():string{
        $tab = [];
        $c = $this->m_def;
        sort($c);
        array_filter($this->m_def, function($a, $k)use(& $tab){
            $tab[] = sprintf('%s:%s', $k, $a);
        }, 1);
        if ($childs = $this->m_childs){
            foreach($childs as $k=>$v){
                $tab[] = sprintf('%s{%s}', $k, $v->render());
            }
        }
        return implode(';', $tab);
    }
    /**
     * set the property
     * @param string $property 
     * @param string $value 
     * @return void 
     */
    public function set(string $property, string $value){
        $this->m_def[$property] = $value;
    }
    public function __toString()
    {
        return $this->render();
    }
}