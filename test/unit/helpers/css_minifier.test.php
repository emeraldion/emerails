<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2025
 *
 * @format
 */

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Helpers\CSSMinifier;

class CSSMinifierTest extends UnitTestBase
{
    public function test_get_instance()
    {
        $this->assertNotNull(CSSMinifier::get_instance());
    }

    public function test_minify()
    {
        $this->assertEquals(
            <<<EOT
            html{padding:0;margin:0;height:100%;background:#fff url('/assets/images/gradient_azure.png') repeat-x 0 0}
            EOT
            ,
            CSSMinifier::get_instance()->minify(
                <<<EOT
                    html {
                        padding: 0;
                        margin: 0;
                        height: 100%;
                        background: #fff url('/assets/images/gradient_azure.png') repeat-x 0 0;
                    }
                EOT
            )
        );
    }
}
