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

    public function do_validate_parameter(string $name, $value, array $params = array())
    {
        return $this->validate_parameter($name, $value, $params);
    }
}

class BaseControllerTest extends UnitTest
{
    public function setUp(): void
    {
        $this->controller = new BaseControllerWrapper();
    }

    public function test_strip_external_php_tags()
    {
        $html = $this->controller->strip_tags('<p>Simple paragraph</p>');
        $this->assertEquals(
            <<<EOT
?>
<p>Simple paragraph</p>
<?php

EOT
            ,
            $html
        );

        $html = $this->controller->strip_tags(
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

    public function test_validate_parameter_int_required_valid()
    {
        $this->assertEquals(
            123,
            $this->controller->do_validate_parameter('id', '123', array(
                'type' => 'int',
                'required' => true
            ))
        );
    }

    public function test_validate_parameter_int_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage("[BaseControllerWrapper::validate_parameter] Missing required int parameter 'id'");

        $this->controller->do_validate_parameter('id', '', array(
            'type' => 'int',
            'required' => true
        ));
    }

    public function test_validate_parameter_int_array_required_valid()
    {
        $this->assertEquals(
            array(1, 2, 3),
            $this->controller->do_validate_parameter(
                'id',
                array('1', '2', '3'),
                array(
                    'type' => 'int[]',
                    'required' => true
                )
            )
        );
    }

    public function test_validate_parameter_int_array_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage("[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'id'. Expected 'int[]', but found: array (
  0 => 'a',
  1 => 'b',
  2 => 'c',
)");

        $this->controller->do_validate_parameter(
            'id',
            array('a', 'b', 'c'),
            array(
                'type' => 'int[]',
                'required' => true
            )
        );
    }

    public function test_validate_parameter_string_required_valid()
    {
        $this->assertEquals(
            'abc',
            $this->controller->do_validate_parameter('query', 'abc', array(
                'type' => 'string',
                'required' => true
            ))
        );
    }

    public function test_validate_parameter_string_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage(
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'query'. Expected 'string', but found: 123"
        );

        $this->controller->do_validate_parameter('query', 123, array(
            'type' => 'string',
            'required' => true
        ));
    }

    public function test_validate_parameter_string_array_required_valid()
    {
        $this->assertEquals(
            array('a', 'b', 'c'),
            $this->controller->do_validate_parameter(
                'query',
                array('a', 'b', 'c'),
                array(
                    'type' => 'string[]',
                    'required' => true
                )
            )
        );
    }

    public function test_validate_parameter_string_array_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage("[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'query'. Expected 'string[]', but found: array (
  0 => 1,
  1 => 2,
  2 => 3,
)");

        $this->controller->do_validate_parameter(
            'query',
            array(1, 2, 3),
            array(
                'type' => 'string[]',
                'required' => true
            )
        );
    }

    public function test_validate_parameter_float_required_valid()
    {
        $this->assertEquals(
            0.123,
            $this->controller->do_validate_parameter('ratio', '0.123', array(
                'type' => 'float',
                'required' => true
            ))
        );
    }

    public function test_validate_parameter_float_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage(
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'ratio'. Expected 'float', but found: 'abc'"
        );

        $this->controller->do_validate_parameter('ratio', 'abc', array(
            'type' => 'float',
            'required' => true
        ));
    }

    public function test_validate_parameter_float_array_required_valid()
    {
        $this->assertEquals(
            array(1.1, 2.2, 3.3),
            $this->controller->do_validate_parameter(
                'ratio',
                array('1.1', '2.2', '3.3'),
                array(
                    'type' => 'float[]',
                    'required' => true
                )
            )
        );
    }

    public function test_validate_parameter_float_array_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage(
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'ratio'. Expected 'float[]', but found: array (
  0 => 'a',
  1 => 'b',
  2 => '1.23',
)"
        );

        $this->controller->do_validate_parameter(
            'ratio',
            array('a', 'b', '1.23'),
            array(
                'type' => 'float[]',
                'required' => true
            )
        );
    }

    public function test_validate_parameter_enum_required_valid()
    {
        $this->assertEquals(
            'apples',
            $this->controller->do_validate_parameter('fruit', 'apples', array(
                'type' => 'enum',
                'values' => array('oranges', 'apples', 'nectarines', 'kiwis'),
                'required' => true
            ))
        );
    }

    public function test_validate_parameter_enum_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage(
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'fruit'. Expected 'enum', but found: 'foobar'"
        );

        $this->controller->do_validate_parameter('fruit', 'foobar', array(
            'type' => 'enum',
            'values' => array('oranges', 'apples', 'nectarines', 'kiwis'),
            'required' => true
        ));
    }

    public function test_validate_parameter_enum_array_required_valid()
    {
        $this->assertEquals(
            array('apples', 'kiwis'),
            $this->controller->do_validate_parameter(
                'fruit',
                array('apples', 'kiwis'),
                array(
                    'type' => 'enum[]',
                    'values' => array('oranges', 'apples', 'nectarines', 'kiwis'),
                    'required' => true
                )
            )
        );
    }

    public function test_validate_parameter_enum_array_required_invalid()
    {
        $this->expectError();
        $this->expectErrorMessage("[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'fruit'. Expected 'enum[]', but found: array (
  0 => 1,
  1 => 2,
  2 => 3,
)");

        $this->controller->do_validate_parameter(
            'fruit',
            array(1, 2, 3),
            array(
                'type' => 'enum[]',
                'values' => array('oranges', 'apples', 'nectarines', 'kiwis'),
                'required' => true
            )
        );
    }
}
?>
