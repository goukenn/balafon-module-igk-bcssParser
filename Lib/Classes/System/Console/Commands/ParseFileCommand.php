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
* 
* @package igk\bcssParser\System\Console\Commands
* @author C.A.D. BONDJE DOUE
*/
class ParseFileCommand extends AppExecCommand{
	var $command="--bcss:parse";
	var $desc="parse bcss to css";
	var $category="bcss";
	var $options=[
		"--merge-def"=>"enable media screen merge"
	];
	var $usage='filename [option]';
	public function exec($command, ?string $filename=null) { 
		$d = file_get_contents($filename);
		$g = BcssParser::ParseFromContent($d, dirname($filename));
		Logger::info("parsing : ".$filename);
		$g->autoMerge = property_exists($command->options, "--merge-def");
		Logger::print($g->render(true, true));
	}
}