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

require_once __DIR__ . '/../../../include/tag_support.inc.php';
require_once __DIR__ . '/../base_test.php';

class TagSupportTest extends UnitTestBase
{
    public function test_strip_external_php_tags_pure_html()
    {
        // Case 1: simple HTML should be wrapped in tags in order to escape from PHP to HTML
        $html = strip_external_php_tags('<p>Simple paragraph</p>');
        $this->assertEquals(
            <<<EOT
            ?>
            <p>Simple paragraph</p>
            <?php

            EOT
            ,
            $html
        );
    }

    public function test_strip_external_php_tags_html_with_php_block()
    {
        // Case 2: an HTML snippet containing a PHP block should be wrapped in tags in
        // order to escape from PHP to HTML
        $html = strip_external_php_tags(
            <<<EOT
            <h1><?php print 'Hello there!'; ?></h1>

            EOT
        );
        $this->assertEquals(
            <<<EOT
            ?>
            <h1><?php print 'Hello there!'; ?></h1>

            <?php

            EOT
            ,
            $html
        );
    }

    public function test_strip_external_php_tags_php_file_with_closing_tag()
    {
        // Case 3: a PHP file beginning with an opening tag and ending with a closing tag
        // should be unwrapped correctly
        $html = strip_external_php_tags(
            <<<EOT
            <?php
              class Cat {
                private \$meow;
              }
            ?>

            EOT
        );
        $this->assertEquals(
            <<<EOT

              class Cat {
                private \$meow;
              }

            EOT
            ,
            $html
        );
    }

    public function test_strip_external_php_tags_php_file_without_closing_tag()
    {
        // Case 4: a PHP file beginning with an opening tag but without a closing tag
        // should be unwrapped correctly
        $html = strip_external_php_tags(
            <<<EOT
            <?php
              class Cat {
                private \$meow;
              }

            EOT
        );
        $this->assertEquals(
            <<<EOT

              class Cat {
                private \$meow;
              }

            EOT
            ,
            $html
        );
    }

    public function test_ensure_external_php_tags_php_file_without_closing_tag()
    {
        $html = ensure_external_php_tags(
            <<<EOT
            <?php
            \$a = 1;

            if (\$a > 0) { ?>
                <div class="msg">
                    Greater than 1
                </div>
            <?php }

            EOT
        );
        $this->assertEquals(
            <<<EOT
            <?php
            \$a = 1;

            if (\$a > 0) { ?>
                <div class="msg">
                    Greater than 1
                </div>
            <?php }

            ?>

            EOT
            ,
            $html
        );
    }

    public function test_ensure_external_php_tags_php_file_with_leading_closing_tag()
    {
        $html = ensure_external_php_tags(
            <<<EOT
            if (\$a > 0) { ?>
                <div class="msg">
                    Greater than 1
                </div>
            <?php } ?>

            EOT
        );
        $this->assertEquals(
            <<<EOT
            <?php
            if (\$a > 0) { ?>
                <div class="msg">
                    Greater than 1
                </div>
            <?php } ?>

            EOT
            ,
            $html
        );
    }

    public function test_ensure_external_php_tags_php_file_with_balanced_tags()
    {
        $html = ensure_external_php_tags(
            <<<EOT
            <?php
            if (\$a > 0) { ?>
                <div class="msg">
                    Greater than 1
                </div>
            <?php } ?>

            EOT
        );
        $this->assertEquals(
            <<<EOT
            <?php
            if (\$a > 0) { ?>
                <div class="msg">
                    Greater than 1
                </div>
            <?php } ?>

            EOT
            ,
            $html
        );
    }

    public function test_ensure_external_php_tags_php_file_without_php_tags()
    {
        $html = ensure_external_php_tags(
            <<<EOT
            <div class="msg">
                No PHP tags!
            </div>

            EOT
        );
        $this->assertEquals(
            <<<EOT
            <div class="msg">
                No PHP tags!
            </div>

            EOT
            ,
            $html
        );
    }
}
