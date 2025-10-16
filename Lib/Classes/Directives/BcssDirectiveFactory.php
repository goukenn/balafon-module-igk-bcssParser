<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssDirectiveFactory.php
// @date: 20240212 10:23:11
namespace igk\bcssParser\Directives;
use igk\bcssParser\Helper\BcssParsingUtility;
use igk\bcssParser\System\IO\BcssParser;
use igk\bcssParser\System\IO\IBcssDirectiveHandler;
use IGK\Helper\StringUtility;
///<summary></summary>
/**
* 
* @package igk\bcssParser\Directives
* @author C.A.D. BONDJE DOUE
*/
abstract class BcssDirectiveFactory implements IBcssDirectiveHandler{
    /**
     * ?string
     * @var mixed
     */
    protected $type;
    protected function __construct(){
    }
    /**
     * 
     * @param string $name 
     * @return object|null 
     */
    public static function Create(string $name){
        $name = StringUtility::CamelClassName(ltrim($name, '@'));
        $cl = __NAMESPACE__."\\".ucfirst($name)."Directive";
        if (class_exists($cl) && is_subclass_of($cl, self::class)){
            return new $cl;
        }
        return null;
    }
    public function handle(BcssParser $parser, string $content, &$pos) { 
        empty($this->type) ?? igk_die("type is empty");
        list($key, $cl) = BcssParsingUtility::ReadBlock($content, $pos);
        $m = $parser->getTheme()->reg_media($this->type.' '.$key);
        $m->load_data(['props'=>$cl]);
    }
}