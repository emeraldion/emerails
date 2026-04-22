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

class ComponentParserTest extends UnitTestBase
{
    public function test_parse_no_components()
    {
        $this->assertEquals(
            <<<EOT
            <div>Hello!</div>
            EOT
            ,
            ComponentParser::parse_contents(
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
        $this->expectException(ComponentParserException::class);
        $this->expectExceptionMessage('Component exceeds maximum size of 4096 characters');

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
        ComponentParser::parse_contents(
            <<<EOT
            <div><x:dropdown-button
                button-label={l('actions-button-label')}
                menu-items={[{$content}]} /></div>
            EOT
        );
    }
}
