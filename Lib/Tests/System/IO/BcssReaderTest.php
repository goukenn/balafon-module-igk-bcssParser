<?php
// @author: C.A.D. BONDJE DOUE
// @file: BcssReaderTest.php
// @date: 20240112 15:44:43
namespace igk\bcssParser\Tests\System\IO;

use igk\bcssParser\System\IO\BcssParser;
use IGK\Tests\Controllers\ModuleBaseTestCase;

///<summary></summary>
/**
 * 
 * @package igk\bcssParser\Tests\System\IO
 * @author C.A.D. BONDJE DOUE
 */
class BcssReaderTest extends ModuleBaseTestCase
{
    public function test_read_directive()
    {
        $d = <<<'BCSS'
@def{
    .igk-body{ /* require non empty value definition */
        background-color:red; /* property - value;*/
        info{
        }
    }
}
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-body{background-color:red;}',
            $g->render(true, true)
        );
    }

    public function test_read_composite_a()
    {
        $d = <<<'BCSS'
@def{
    .igk-body{ /* require non empty value definition */
        background-color:red; /* property - value;*/
        position:absolute;
        info{
            display:inline-block;
        }
        display:block;
    }
}
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-body{background-color:red;display:block;position:absolute;}.igk-body info{display:inline-block;}',
            $g->render(true, true)
        );
    }

    public function test_read_composite_with_3()
    {
        $d = <<<'BCSS'
@def{
    .igk-body{ /* require non empty value definition */
        background-color:red; /* property - value;*/
        position:absolute;
        info{
            display:inline-block;
        }
        display:block;
    }
    .igk-a{
        text-decoration:none;
    }
}
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-body{background-color:red;display:block;position:absolute;}.igk-body info{display:inline-block;}.igk-a{text-decoration:none;}',
            $g->render(true, true)
        );
    }


    public function test_read_css_media()
    {
        $d = <<<'BCSS'
@def{   
    .igk-a{
        text-decoration:none;
    }
}
@sm-screen{
    .igk-a{
        text-decoration:underline;
    }
}
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{text-decoration:none;}@media (min-width:321px) and (max-width:710px){.igk-a{text-decoration:underline;}}',
            $g->render(true, true)
        );
    }

    public function test_read_color()
    {
        $d = <<<'BCSS'
@def{   
    .igk-a{ 
       [fcl:--indigo];
    }
}
@colors{ // define errors 
    --indigo: #454654;
}
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{color:#454654;}:root{--indigo:#454654}',
            $g->render(true, true)
        );
    }

    public function _test_read_root_definition()
    {
        $d = <<<'BCSS'
@def{   
    .igk-a{ 
       [fcl:--indigo];
       background-color: [cl:igk-red]
    }
}
@root{
    igk-red: yellow;
    --empty-content: '45';
}
@colors{
    --indigo:#456554;
    igk-red: yellow;
}
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{background-color:#4547846;color:#456554;}:root{igk-red:yellow; --empty-content:\'45\';}',
            $g->render(true, true)
        );
    }
    public function test_apply_directive()
    {
        $d = <<<'BCSS'
@def{   
            .igk-a{ 
                background-color:red;
                @global .fitw, .fith;
                @apply .fith > div + p, fitw;
    }
    .fitw{
        width: 40%;
    }
    .fith{
        height: 30%;
    }
    .fith > div + p{
        color:red;
    }
} 
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{background-color:red;color:red;height:100%;width:100%;}.fitw{width:40%;}.fith{height:30%;}.fith > div + p{color:red;}',
            $g->render(true, true)
        );
    }

    public function test_apply_directive_with_combination()
    {
        $d = <<<'BCSS'
@def{   
    .igk-a{  
        @apply '.fith > div + p, fitw';
    }
    .fitw{
        width: 40%;
    }
    .fith{
        height: 30%;
    }
    .fith > div + p, fitw{
        color:red;
    }
} 
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{color:red;}.fitw{width:40%;}.fith{height:30%;}.fith > div + p, fitw{color:red;}',
            $g->render(true, true)
        );
    }

    public function test_parse_speudo_class()
    {
        $d = <<<'BCSS'
@def{   
    .igk-a{  
        @apply .fith:hover;
    } 
    .fith:hover{
        color:yellow;
    }
} 
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{color:yellow;}.fith:hover{color:yellow;}',
            $g->render(true, true)
        );
    }

    public function test_parse_speudo_class_escaped()
    {
        $d = <<<'BCSS'
@def{  
    .hover\:list{
        color:yellow;
    }
    .hover\:list:hover{
        color:blue;
    }
} 
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.hover\:list{color:yellow;}.hover\:list:hover{color:blue;}',
            $g->render(true, true)
        );
    }
    public function test_parse_directive_speudo_class_escaped()
    {
        $d = <<<'BCSS'
@def{  
    .igk-a{
        @apply .hover\:list
    }
    .hover\:list{
        color:yellow;
    }
    .hover\:list:hover{
        color:blue;
    }
} 
BCSS;
        $g = BcssParser::ParseFromContent($d);
        $this->assertEquals(
            '.igk-a{color:yellow;}.hover\:list{color:yellow;}.hover\:list:hover{color:blue;}',
            $g->render(true, true)
        );
    }
}

