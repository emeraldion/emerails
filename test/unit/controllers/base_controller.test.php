<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
 */

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Controllers\BaseController;

class BaseControllerWrapper extends BaseController
{
    public function strip_tags($php_code)
    {
        return $this->strip_external_php_tags($php_code);
    }
}

class BaseControllerTest extends UnitTest
{
    public function test_strip_external_php_tags()
    {
        $controller = new BaseControllerWrapper();

        $html = $controller->strip_tags('<p>Simple paragraph</p>');
        $this->assertEquals(
            <<<EOT
?>
<p>Simple paragraph</p>
<?php

EOT
            ,
            $html
        );

        $html = $controller->strip_tags(
            <<<EOT
<?php

?>
<h1>Title</h1>

EOT
        );
        $this->assertEquals(
            <<<EOT


?>
<h1>Title</h1>

<?php

EOT
            ,
            $html
        );
    }
}
?>
