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

use Emeraldion\EmeRails\Controllers\BaseController;

class BaseControllerWrapper extends BaseController
{
    public function do_validate_parameter(string $name, $value, array $params = [])
    {
        return $this->validate_parameter($name, $value, $params);
    }
}

class SimpleController extends BaseController
{
    public $is_simple_action;
    public $is_simple_index;
    public $is_simple;

    public function action()
    {
        $this->is_simple_action = $this->is('simple', 'action');
        $this->is_simple_index = $this->is('simple', 'index');
        $this->is_simple = $this->is('simple');
    }
}

class BaseControllerTest extends UnitTestBase
{
    public function setUp(): void
    {
        $this->controller = new BaseControllerWrapper();
    }

    public function test_validate_parameter_int_valid()
    {
        $this->assertEquals(
            123,
            $this->controller->do_validate_parameter('id', '123', [
                'type' => 'int'
            ])
        );
    }

    public function test_validate_parameter_int_null_valid()
    {
        $this->assertEquals(
            null,
            $this->controller->do_validate_parameter('id', null, [
                'type' => 'int'
            ])
        );
    }

    public function test_validate_parameter_int_default_null_valid()
    {
        $this->assertEquals(
            456,
            $this->controller->do_validate_parameter('id', null, [
                'type' => 'int',
                'default' => 456
            ])
        );
    }

    public function test_validate_parameter_int_required_null_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('id', null, [
                'type' => 'int',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required int parameter 'id'");
    }

    public function test_validate_parameter_int_required_valid()
    {
        $this->assertEquals(
            123,
            $this->controller->do_validate_parameter('id', '123', [
                'type' => 'int',
                'required' => true
            ])
        );
    }

    public function test_validate_parameter_int_required_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('id', '', [
                'type' => 'int',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required int parameter 'id'");
    }

    public function test_validate_parameter_int_required_string_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('id', 'abc', [
                'type' => 'int',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'id'. Expected 'int', but found: 'abc'");
    }

    public function test_validate_parameter_int_array_valid()
    {
        $this->assertEquals(
            [1, 2, 3],
            $this->controller->do_validate_parameter(
                'id',
                ['1', '2', '3'],
                [
                    'type' => 'int[]'
                ]
            )
        );
    }

    public function test_validate_parameter_int_array_default_valid()
    {
        $this->assertEquals(
            [3, 4, 5],
            $this->controller->do_validate_parameter('id', null, [
                'type' => 'int[]',
                'default' => [3, 4, 5]
            ])
        );
    }

    public function test_validate_parameter_int_array_required_valid()
    {
        $this->assertEquals(
            [1, 2, 3],
            $this->controller->do_validate_parameter(
                'id',
                ['1', '2', '3'],
                [
                    'type' => 'int[]',
                    'required' => true
                ]
            )
        );
    }

    public function test_validate_parameter_int_array_required_invalid()
    {
        $this->assertErrorWithMessage(
            function () {
                $this->controller->do_validate_parameter(
                    'id',
                    ['a', 'b', 'c'],
                    [
                        'type' => 'int[]',
                        'required' => true
                    ]
                );
            },
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'id'. Expected 'int[]', but found: array (
  0 => 'a',
  1 => 'b',
  2 => 'c',
)"
        );
    }

    public function test_validate_parameter_bool_valid()
    {
        $this->assertTrue(
            $this->controller->do_validate_parameter('enabled', 'true', [
                'type' => 'bool'
            ])
        );
    }

    public function test_validate_parameter_bool_null_valid()
    {
        $this->assertEquals(
            null,
            $this->controller->do_validate_parameter('enabled', null, [
                'type' => 'bool'
            ])
        );
    }

    public function test_validate_parameter_bool_default_null_valid()
    {
        $this->assertFalse(
            $this->controller->do_validate_parameter('enabled', null, [
                'type' => 'bool',
                'default' => false
            ])
        );
    }

    public function test_validate_parameter_bool_required_null_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('enabled', null, [
                'type' => 'bool',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'enabled'. Expected 'bool', but found: NULL");
    }

    public function test_validate_parameter_bool_required_valid()
    {
        $this->assertFalse(
            $this->controller->do_validate_parameter('enabled', 'false', [
                'type' => 'bool',
                'required' => true
            ])
        );
    }

    public function test_validate_parameter_bool_required_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('enabled', 'bogus', [
                'type' => 'bool',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'enabled'. Expected 'bool', but found: 'bogus'");
    }

    public function test_validate_parameter_bool_array_valid()
    {
        $this->assertEquals(
            [true, true, true],
            $this->controller->do_validate_parameter(
                'enabled',
                ['1', 'on', 'true'],
                [
                    'type' => 'bool[]'
                ]
            )
        );
    }

    public function test_validate_parameter_bool_array_default_valid()
    {
        $this->assertEquals(
            [false, true, false],
            $this->controller->do_validate_parameter('enabled', null, [
                'type' => 'bool[]',
                'default' => [false, true, false]
            ])
        );
    }

    public function test_validate_parameter_bool_array_required_valid()
    {
        $this->assertEquals(
            [false, true, false],
            $this->controller->do_validate_parameter(
                'enabled',
                ['0', '1', 'false'],
                [
                    'type' => 'bool[]',
                    'required' => true
                ]
            )
        );
    }

    public function test_validate_parameter_bool_array_required_invalid()
    {
        $this->assertErrorWithMessage(
            function () {
                $this->controller->do_validate_parameter(
                    'enabled',
                    ['a', 'b', 'c'],
                    [
                        'type' => 'bool[]',
                        'required' => true
                    ]
                );
            },
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'enabled'. Expected 'bool[]', but found: array (
  0 => 'a',
  1 => 'b',
  2 => 'c',
)"
        );
    }

    public function test_validate_parameter_string_valid()
    {
        $this->assertEquals(
            'abc',
            $this->controller->do_validate_parameter('query', 'abc', [
                'type' => 'string'
            ])
        );
    }

    public function test_validate_parameter_string_null_valid()
    {
        $this->assertNull(
            $this->controller->do_validate_parameter('query', null, [
                'type' => 'string'
            ])
        );
    }

    public function test_validate_parameter_string_default_null_valid()
    {
        $this->assertEquals(
            '*',
            $this->controller->do_validate_parameter('query', null, [
                'type' => 'string',
                'default' => '*'
            ])
        );
    }

    public function test_validate_parameter_string_required_null_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('query', null, [
                'type' => 'string',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required string parameter 'query'");
    }

    public function test_validate_parameter_string_required_valid()
    {
        $this->assertEquals(
            'abc',
            $this->controller->do_validate_parameter('query', 'abc', [
                'type' => 'string',
                'required' => true
            ])
        );
    }

    public function test_validate_parameter_string_required_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('query', '', [
                'type' => 'string',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required string parameter 'query'");
    }

    public function test_validate_parameter_string_required_int_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('query', 123, [
                'type' => 'string',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'query'. Expected 'string', but found: 123");
    }

    public function test_validate_parameter_string_array_valid()
    {
        $this->assertEquals(
            ['a', 'b', 'c'],
            $this->controller->do_validate_parameter(
                'query',
                ['a', 'b', 'c'],
                [
                    'type' => 'string[]'
                ]
            )
        );
    }

    public function test_validate_parameter_string_array_default_valid()
    {
        $this->assertEquals(
            ['a', 'b', 'c'],
            $this->controller->do_validate_parameter('query', null, [
                'type' => 'string[]',
                'default' => ['a', 'b', 'c']
            ])
        );
    }

    public function test_validate_parameter_string_array_required_valid()
    {
        $this->assertEquals(
            ['a', 'b', 'c'],
            $this->controller->do_validate_parameter(
                'query',
                ['a', 'b', 'c'],
                [
                    'type' => 'string[]',
                    'required' => true
                ]
            )
        );
    }

    public function test_validate_parameter_string_array_required_invalid()
    {
        $this->assertErrorWithMessage(
            function () {
                $this->controller->do_validate_parameter(
                    'query',
                    [1, 2, 3],
                    [
                        'type' => 'string[]',
                        'required' => true
                    ]
                );
            },
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'query'. Expected 'string[]', but found: array (
  0 => 1,
  1 => 2,
  2 => 3,
)"
        );
    }

    public function test_validate_parameter_float_valid()
    {
        $this->assertEquals(
            0.123,
            $this->controller->do_validate_parameter('ratio', '0.123', [
                'type' => 'float'
            ])
        );
    }

    public function test_validate_parameter_float_null_valid()
    {
        $this->assertNull(
            $this->controller->do_validate_parameter('ratio', null, [
                'type' => 'float'
            ])
        );
    }

    public function test_validate_parameter_float_default_null_valid()
    {
        $this->assertEquals(
            1.234,
            $this->controller->do_validate_parameter('ratio', null, [
                'type' => 'float',
                'default' => 1.234
            ])
        );
    }

    public function test_validate_parameter_float_required_null_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('ratio', null, [
                'type' => 'float',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required float parameter 'ratio'");
    }

    public function test_validate_parameter_float_required_valid()
    {
        $this->assertEquals(
            0.123,
            $this->controller->do_validate_parameter('ratio', '0.123', [
                'type' => 'float',
                'required' => true
            ])
        );
    }

    public function test_validate_parameter_float_required_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('ratio', '', [
                'type' => 'float',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required float parameter 'ratio'");
    }

    public function test_validate_parameter_float_required_string_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('ratio', 'abc', [
                'type' => 'float',
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'ratio'. Expected 'float', but found: 'abc'");
    }

    public function test_validate_parameter_float_array_valid()
    {
        $this->assertEquals(
            [1.1, 2.2, 3.3],
            $this->controller->do_validate_parameter(
                'ratio',
                ['1.1', '2.2', '3.3'],
                [
                    'type' => 'float[]'
                ]
            )
        );
    }

    public function test_validate_parameter_float_array_default_valid()
    {
        $this->assertEquals(
            [1.1, 2.2, 3.3],
            $this->controller->do_validate_parameter('ratio', null, [
                'type' => 'float[]',
                'default' => ['1.1', '2.2', '3.3']
            ])
        );
    }

    public function test_validate_parameter_float_array_required_valid()
    {
        $this->assertEquals(
            [1.1, 2.2, 3.3],
            $this->controller->do_validate_parameter(
                'ratio',
                ['1.1', '2.2', '3.3'],
                [
                    'type' => 'float[]',
                    'required' => true
                ]
            )
        );
    }

    public function test_validate_parameter_float_array_required_invalid()
    {
        $this->assertErrorWithMessage(
            function () {
                $this->controller->do_validate_parameter(
                    'ratio',
                    ['a', 'b', '1.23'],
                    [
                        'type' => 'float[]',
                        'required' => true
                    ]
                );
            },
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'ratio'. Expected 'float[]', but found: array (
  0 => 'a',
  1 => 'b',
  2 => '1.23',
)"
        );
    }

    public function test_validate_parameter_enum_valid()
    {
        $this->assertEquals(
            'apples',
            $this->controller->do_validate_parameter('fruit', 'apples', [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis']
            ])
        );
    }

    public function test_validate_parameter_enum_null_valid()
    {
        $this->assertNull(
            $this->controller->do_validate_parameter('fruit', null, [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis']
            ])
        );
    }

    public function test_validate_parameter_enum_default_null_valid()
    {
        $this->assertEquals(
            'nectarines',
            $this->controller->do_validate_parameter('fruit', null, [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                'default' => 'nectarines'
            ])
        );
    }

    public function test_validate_parameter_enum_required_null_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('fruit', null, [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required enum parameter 'fruit'");
    }

    public function test_validate_parameter_enum_required_valid()
    {
        $this->assertEquals(
            'apples',
            $this->controller->do_validate_parameter('fruit', 'apples', [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                'required' => true
            ])
        );
    }

    public function test_validate_parameter_enum_required_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('fruit', '', [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Missing required enum parameter 'fruit'");
    }

    public function test_validate_parameter_enum_required_string_invalid()
    {
        $this->assertErrorWithMessage(function () {
            $this->controller->do_validate_parameter('fruit', 'foobar', [
                'type' => 'enum',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                'required' => true
            ]);
        }, "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'fruit'. Expected 'enum', but found: 'foobar'");
    }

    public function test_validate_parameter_enum_array_valid()
    {
        $this->assertEquals(
            ['apples', 'kiwis'],
            $this->controller->do_validate_parameter(
                'fruit',
                ['apples', 'kiwis'],
                [
                    'type' => 'enum[]',
                    'values' => ['oranges', 'apples', 'nectarines', 'kiwis']
                ]
            )
        );
    }

    public function test_validate_parameter_enum_array_default_valid()
    {
        $this->assertEquals(
            ['nectarines', 'kiwis'],
            $this->controller->do_validate_parameter('fruit', null, [
                'type' => 'enum[]',
                'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                'default' => ['nectarines', 'kiwis']
            ])
        );
    }

    public function test_validate_parameter_enum_array_required_valid()
    {
        $this->assertEquals(
            ['apples', 'kiwis'],
            $this->controller->do_validate_parameter(
                'fruit',
                ['apples', 'kiwis'],
                [
                    'type' => 'enum[]',
                    'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                    'required' => true
                ]
            )
        );
    }

    public function test_validate_parameter_enum_array_required_invalid()
    {
        $this->assertErrorWithMessage(
            function () {
                $this->controller->do_validate_parameter(
                    'fruit',
                    [1, 2, 3],
                    [
                        'type' => 'enum[]',
                        'values' => ['oranges', 'apples', 'nectarines', 'kiwis'],
                        'required' => true
                    ]
                );
            },
            "[BaseControllerWrapper::validate_parameter] Type mismatch for parameter 'fruit'. Expected 'enum[]', but found: array (
  0 => 1,
  1 => 2,
  2 => 3,
)"
        );
    }

    public function test_is()
    {
        // FIXME: Hack
        $old_action = @$_REQUEST['action'];
        $_REQUEST['action'] = 'action';
        $controller = new SimpleController();
        $controller->action();
        $_REQUEST['action'] = $old_action;

        $this->assertTrue($controller->is_simple);
        $this->assertTrue($controller->is_simple_action);
        $this->assertFalse($controller->is_simple_index);
    }
}
