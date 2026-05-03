<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
 *
 * @format
 */

require_once __DIR__ . '/../base_test.php';
require_once __DIR__ . '/../../../helpers/component_parser.php';

use Emeraldion\EmeRails\Exceptions\ComponentParserException;
use Emeraldion\EmeRails\Controllers\BaseController;

class TestController extends BaseController
{
    // @override no-op
    public function render_component($params) {}
}

class ComponentParserTest extends UnitTestBase
{
    public function setUp(): void
    {
        $this->controller = new TestController();
    }

    public function test_parse_no_components()
    {
        $this->assertEquals(
            <<<EOT
            <div>Hello!</div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div>Hello!</div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_single_string_attribute()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'greeting',
                'props' => [
            \t'_name' => 'hello',
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:greeting name="hello" /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_single_expression_attributes()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'result_counter_sticky',
                'props' => [
            \t'count' => \$this->shops ? count(\$this->shops) : -1,
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:result_counter_sticky count={\$this->shops ? count(\$this->shops) : -1} /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_multiple_expression_attributes()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'result_counter_sticky',
                'props' => [
            \t'count' => \$this->shops ? count(\$this->shops) : -1,
            \t'results_start' => \$this->results_start,
            \t'total_results' => \$this->total_results,
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:result_counter_sticky count={\$this->shops ? count(\$this->shops) : -1} results_start={\$this->results_start} total_results={\$this->total_results} /></div>
                EOT
            )
        );
    }

    public function test_parse_namespaced_singleton_component_multiple_expression_attributes()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'cart',
                'action' => 'content',
                'props' => [
            \t'user' => \$this->user,
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:cart:content user={\$this->user} /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_single_standalone_attribute()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'button',
                'props' => [
            \t'disabled' => true,
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:button disabled /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_string_attribute_standalone_attribute()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'button',
                'props' => [
            \t'label' => 'Submit',
            \t'disabled' => true,
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:button label="Submit" disabled /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_expression_attribute_standalone_attribute()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'scroller',
                'props' => [
            \t'amount' => 120,
            \t'vertical' => true,
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:scroller amount={120} vertical /></div>
                EOT
            )
        );
    }

    public function test_parse_namespaced_singleton_component_expression_attribute_standalone_attribute_string_attribute()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'challenge',
                'action' => 'question',
                'props' => [
            \t'actions' => ['ok', 'cancel'],
            \t'active' => true,
            \t'title' => 'Are you sure?',
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:challenge:question actions={['ok', 'cancel']} active title="Are you sure?" /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_multiline_expression_attribute_multiline()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'dropdown_button',
                'props' => [
            \t'button_label' => l('actions-button-label'),
            \t'menu_items' => [
                [
                    \$this->link_to(l('menu-item-button-label1'), [
                        'controller' => 'controller1',
                        'action' => 'action1',
                        'id' => 'id1',
                        'return' => true
                    ]),
                    \$this->link_to(l('menu-item-button-label2'), [
                        'controller' => 'controller2',
                        'action' => 'action2',
                        'id' => 'id2',
                        'return' => true
                    ]),
                    \$this->link_to(l('menu-item-button-label3'), [
                        'controller' => 'controller3',
                        'action' => 'action3',
                        'id' => 'id3',
                        'return' => true
                    ])
                ],
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:dropdown-button button-label={l('actions-button-label')} menu-items={[
                    [
                        \$this->link_to(l('menu-item-button-label1'), [
                            'controller' => 'controller1',
                            'action' => 'action1',
                            'id' => 'id1',
                            'return' => true
                        ]),
                        \$this->link_to(l('menu-item-button-label2'), [
                            'controller' => 'controller2',
                            'action' => 'action2',
                            'id' => 'id2',
                            'return' => true
                        ]),
                        \$this->link_to(l('menu-item-button-label3'), [
                            'controller' => 'controller3',
                            'action' => 'action3',
                            'id' => 'id3',
                            'return' => true
                        ])
                    ]} /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_expression_attribute_tag_newline()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'dropdown_button',
                'props' => [
            \t'button_label' => l('actions-button-label'),
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:dropdown-button
                    button-label={l('actions-button-label')} /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_multiline_expression_attribute_tag_newline()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'dropdown_button',
                'props' => [
            \t'button_label' => l('actions-button-label'),
            \t'menu_items' => [
                [
                    \$this->link_to(l('menu-item-button-label1'), [
                        'controller' => 'controller1',
                        'action' => 'action1',
                        'id' => 'id1',
                        'return' => true
                    ]),
                    \$this->link_to(l('menu-item-button-label2'), [
                        'controller' => 'controller2',
                        'action' => 'action2',
                        'id' => 'id2',
                        'return' => true
                    ]),
                    \$this->link_to(l('menu-item-button-label3'), [
                        'controller' => 'controller3',
                        'action' => 'action3',
                        'id' => 'id3',
                        'return' => true
                    ]),
                    \$this->link_to(l('menu-item-button-label4'), [
                        'controller' => 'controller4',
                        'action' => 'action4',
                        'id' => 'id4',
                        'return' => true
                    ])
                ],
            ]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:dropdown-button
                    button-label={l('actions-button-label')}
                    menu-items={[
                    [
                        \$this->link_to(l('menu-item-button-label1'), [
                            'controller' => 'controller1',
                            'action' => 'action1',
                            'id' => 'id1',
                            'return' => true
                        ]),
                        \$this->link_to(l('menu-item-button-label2'), [
                            'controller' => 'controller2',
                            'action' => 'action2',
                            'id' => 'id2',
                            'return' => true
                        ]),
                        \$this->link_to(l('menu-item-button-label3'), [
                            'controller' => 'controller3',
                            'action' => 'action3',
                            'id' => 'id3',
                            'return' => true
                        ]),
                        \$this->link_to(l('menu-item-button-label4'), [
                            'controller' => 'controller4',
                            'action' => 'action4',
                            'id' => 'id4',
                            'return' => true
                        ])
                    ]} /></div>
                EOT
            )
        );
    }

    public function test_parse_singleton_component_multiline_expression_exceeding_max_size()
    {
        $content = var_export(
            array_map(function ($i) {
                return sprintf(
                    <<<EOT
                                \$this->link_to(l('menu-item-button-label%d'), [
                                    'controller' => 'controller%d',
                                    'action' => 'action%d',
                                    'id' => 'id%d',
                                    'return' => true
                                ])
                    EOT
                    ,
                    $i,
                    $i,
                    $i,
                    $i
                );
            }, range(1, 20)),
            true
        );
        $this->assertEquals(
            <<<EOT
            <div><div class="msg error"><h3>Component parse error</h3>

            <p>Component <strong>dropdown-button</strong> exceeds the maximum allowed size of 4096 characters. Consider increasing the value of the MAX_COMPONENT_LENGTH config setting.</p>
            </div>
            > true
                        ])',
              16 => '            \$this->link_to(l(\'menu-item-button-label17\'), [
                            \'controller\' => \'controller17\',
                            \'action\' => \'action17\',
                            \'id\' => \'id17\',
                            \'return\' => true
                        ])',
              17 => '            \$this->link_to(l(\'menu-item-button-label18\'), [
                            \'controller\' => \'controller18\',
                            \'action\' => \'action18\',
                            \'id\' => \'id18\',
                            \'return\' => true
                        ])',
              18 => '            \$this->link_to(l(\'menu-item-button-label19\'), [
                            \'controller\' => \'controller19\',
                            \'action\' => \'action19\',
                            \'id\' => \'id19\',
                            \'return\' => true
                        ])',
              19 => '            \$this->link_to(l(\'menu-item-button-label20\'), [
                            \'controller\' => \'controller20\',
                            \'action\' => \'action20\',
                            \'id\' => \'id20\',
                            \'return\' => true
                        ])',
            )]} /></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:dropdown-button
                    button-label={l('actions-button-label')}
                    menu-items={[{$content}]} /></div>
                EOT
            )
        );
    }

    public function test_parse_container_component_no_attributes()
    {
        $this->assertEquals(
            <<<EOQ
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'container',
                'props' => [
            \t'children' => <<<'EOA'
            <p>Hello</p>

            EOA
            ,]

            ]);
            ?></div>
            EOQ
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:container><p>Hello</p></x:container></div>
                EOT
            )
        );
    }

    public function test_parse_container_component_single_string_attributes()
    {
        $this->assertEquals(
            <<<EOT
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'container',
                'props' => [
            \t'foo' => 'bar',
            \t'children' => <<<'EOA'
            <p>Hello</p>

            EOA
            ,]

            ]);
            ?></div>
            EOT
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:container foo="bar"><p>Hello</p></x:container></div>
                EOT
            )
        );
    }

    public function test_parse_container_component_single_expression_attribute()
    {
        $this->assertEquals(
            <<<EOQ
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'container',
                'props' => [
            \t'foo' => 123,
            \t'children' => <<<'EOA'
            <p>Hello</p>

            EOA
            ,]

            ]);
            ?></div>
            EOQ
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:container foo={123}><p>Hello</p></x:container></div>
                EOT
            )
        );
    }

    public function test_parse_container_component_multiline()
    {
        $this->assertEquals(
            <<<EOQ
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'container',
                'props' => [
            \t'children' => <<<'EOA'

                <h2>Hello</h2>
                <p>Here is some text</p>


            EOA
            ,]

            ]);
            ?></div>
            EOQ
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:container>
                    <h2>Hello</h2>
                    <p>Here is some text</p>
                </x:container></div>
                EOT
            )
        );
    }

    public function test_parse_nested_components()
    {
        $this->assertEquals(
            <<<EOQ
            <div><?php
            \$this->render_component([
                'controller' => 'common',
                'action' => 'container',
                'props' => [
            \t'children' => <<<'EOA'

                    <p>Here is some text</p>


            EOA
            ,]

            ]);
            ?></div>
            EOQ
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div><x:container>
                    <x:header>Hello</x:header>
                    <p>Here is some text</p>
                </x:container></div>
                EOT
            )
        );
    }

    public function test_parse_nested_components_mixed_attributes_different_namespaces()
    {
        $this->assertEquals(
            <<<EOP
            <div>
                <?php
            \$this->render_component([
                'controller' => 'foo',
                'action' => 'container',
                'props' => [
            \t'size' => 123,
            \t'children' => <<<'EOA'

                            <p>Here is some text</p>

            EOA
            ,]

            ]);
            ?>
            </div>
            EOP
            ,
            ComponentParser::parse_contents(
                $this->controller,
                <<<EOT
                <div>
                    <x:foo:container size={123}>
                        <x:bar:header font="Roboto">Hello</x:bar:header>
                        <p>Here is some text</p></x:foo:container>
                </div>
                EOT
            )
        );
    }
}
