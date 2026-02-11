#!/usr/bin/env php
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

require_once __DIR__ . '/base.php';

use splitbrain\phpcli\Options;

use Emeraldion\EmeRails\Helpers\ANSIColorWriter;

class SortDependencies extends ScriptCommand
{
    protected function setup(Options $options)
    {
        $options->setHelp('EmeRails script to sort dependencies');

        $this->register_common_options($options);
    }

    protected function main(Options $options)
    {
        foreach (['helpers', 'controllers', 'models'] as $category) {
            $dirname = sprintf('%s/../%s', __DIR__, $category);
            $dir_handle = opendir($dirname);
            if ($dir_handle) {
                while (($filename = readdir($dir_handle)) !== false) {
                    if ($filename === '.' || $filename === '..') {
                        continue;
                    }

                    $filepath = $dirname . '/' . $filename;

                    $handle = fopen($filepath, 'r');
                    $content = fread($handle, filesize($filepath));
                    fclose($handle);

                    preg_match_all('/\buse\s+.+;/', $content, $matches, PREG_PATTERN_ORDER);
                    $uses = first($matches);
                    sort($uses);

                    preg_match_all('/\brequire_once\s+(?!sprintf).+;/', $content, $matches, PREG_PATTERN_ORDER);
                    $imports = first($matches);
                    sort($imports);

                    $imports = implode("\n", $imports);
                    $imports .= $imports ? "\n" : '';
                    $uses = implode("\n", $uses);
                    $uses .= $uses ? "\n" : '';

                    $separator = $imports && $uses ? "\n" : '';
                    $terminator = $imports || $uses ? "\n" : '';

                    $content = preg_replace('/\buse\s+.+;/', '', $content);
                    $content = preg_replace('/\brequire_once\s+(?!sprintf).+;/', '', $content);

                    $format_pos = strpos($content, '@format') + strlen('@format');
                    $preamble = substr($content, 0, $format_pos);
                    $rest = substr($content, $format_pos);
                    $comment_end_pos = strpos($rest, "*/\n") + strlen("*/\n");
                    $preamble .= substr($rest, 0, $comment_end_pos);

                    $preamble = rtrim($preamble);
                    $rest = ltrim(substr($rest, $comment_end_pos));

                    $formatted = <<<EOT
                    {$preamble}

                    {$imports}{$separator}{$uses}{$terminator}{$rest}
                    EOT;

                    $handle = fopen($filepath, 'w');
                    fwrite($handle, $formatted, strlen($formatted));
                    fclose($handle);
                }
            }
        }
    }
}

$cli = new SortDependencies();
$cli->run();

