<?php
// @author: C.A.D. BONDJE DOUE
// @file: BssStateInfo.php
// @date: 20240112 16:37:20
namespace igk\bcssParser\System\IO;


///<summary></summary>
/**
* 
* @package igk\bcssParser
* @author C.A.D. BONDJE DOUE
*/
class BcssStateInfo{

    /**
     * use global flag definition
     */
    var $useGlobalStyleDefinition = false;
    var $depth = 0;
    /**
     * reading mode type \
     * 0 = key reading
     * 1 = value reading
     * @var int
     */
    var $mode = 0; 

    /**
     * speudo selected
     * @var ?bool
     */
    var $speudoSelect;

    /**
     * current directive
     * @var mixed
     */
    var $directive;

    /**
     * current directive sample 
     * @var mixed
     */
    var $scope_directive;

    /**
     * the current read value
     * @var ?string
     */
    var $value;

    /**
     * current read property
     * @var ?string
     */
    var $property;

    private $m_selectors = [];

    var $directive_name;
    var $scope_directive_name;
    var $def; // css definition
    /**
     * store media query names
     * @var array
     */
    var $registerThemes = [];
    /**
     * get selector 
     * @return string 
     */
    public function getSelector():string{
        return implode(' ', $this->m_selectors);
    }
    public function pushSelector(string $value){
        array_push($this->m_selectors, $value);
        $this->value = null;
    }
    public function popSelector(){
        $this->value = null;
        return array_pop($this->m_selectors);
    }

    public function currentSelector():?string{
        if (
            $this->m_selectors
        ){
            return $this->m_selectors[count($this->m_selectors)-1];
        }
        return null;
    }
    /**
     * clear scoped value
     * @return void 
     */
    public function clearScope(){
        $this->scope_directive = null;
        $this->scope_directive_name = null;
    }
    /**
     * 
     */
    public function updateRegistry(){
        if ($def = $this->def) {
            if ($def->themeProperty && !isset($this->m_state->registerThemes[$def->themeProperty])) {
                $this->registerThemes[$def->themeProperty] = $def;
            }
            $this->def = $def->parent;
        } else
            $this->def = null;  
    }
}