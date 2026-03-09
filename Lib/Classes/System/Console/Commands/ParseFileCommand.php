<?php
// @author: C.A.D. BONDJE DOUE
// @file: ParseFileCommand.php
// @date: 20240210 20:31:40
namespace igk\bcssParser\System\Console\Commands;

use igk\bcssParser\System\IO\BcssParser;
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
 

///<summary></summary>

/**
* auto generate doc.
* @package igk\bcssParser\System\Console\Commands
* @author C.A.D. BONDJE DOUE
*/
class ParseFileCommand extends AppExecCommand{

    /**
    * auto generate doc.
    * @var mixed
    */
    var $command="--bcss:parse";

    /**
    * auto generate doc.
    * @var mixed
    */
    var $desc="parse bcss to css";

    /**
    * auto generate doc.
    * @var mixed
    */
    var $category="bcss";

    /**
    * auto generate doc.
    * @var mixed
    */
    var $options=[
		"--merge-def"=>"enable media screen merge"
	];

    /**
    * auto generate doc.
    * @var mixed
    */
    var $usage='filename [option]';

    /**
    * auto generate doc.
    * @param mixed $command
    * @param null|string $filename
    */
    public function exec($command, ?string $filename=null) { 
		($filename && file_exists($filename)) || igk_die('missing filename');
		$d = file_get_contents($filename);
		$g = BcssParser::ParseFromContent($d, dirname($filename));
		Logger::info("parsing : ".$filename);
		$g->autoMerge = property_exists($command->options, "--merge-def");
		Logger::print($g->render(true, true));
	}
}