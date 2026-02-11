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

function kebab_case_to_screaming_snake_case($text)
{
    $text = preg_replace('/-/', '_', $text);
    return strtoupper($text);
}

class ANSIColorWriter
{
    const ANSI_RESET = "\033[0m";

    const ANSI_BOLD = "\033[1m";
    const ANSI_ITALICS = "\033[3m";
    const ANSI_UNDERLINE = "\033[4m";
    const ANSI_INVERSE = "\033[7m";
    const ANSI_STRIKETHROUGH = "\033[9m";

    const ANSI_BOLD_OFF = "\033[22m";
    const ANSI_ITALICS_OFF = "\033[23m";
    const ANSI_UNDERLINE_OFF = "\033[24m";
    const ANSI_INVERSE_OFF = "\033[27m";
    const ANSI_STRIKETHROUGH_OFF = "\033[29m";

    const ANSI_BLACK = "\033[30m";
    const ANSI_RED = "\033[31m";
    const ANSI_GREEN = "\033[32m";
    const ANSI_YELLOW = "\033[33m";
    const ANSI_BLUE = "\033[34m";
    const ANSI_PURPLE = "\033[35m";
    const ANSI_CYAN = "\033[36m";
    const ANSI_WHITE = "\033[37m";

    const ANSI_BRIGHT_BLACK = "\033[90m";
    const ANSI_BRIGHT_RED = "\033[91m";
    const ANSI_BRIGHT_GREEN = "\033[92m";
    const ANSI_BRIGHT_YELLOW = "\033[93m";
    const ANSI_BRIGHT_BLUE = "\033[94m";
    const ANSI_BRIGHT_PURPLE = "\033[95m";
    const ANSI_BRIGHT_CYAN = "\033[96m";
    const ANSI_BRIGHT_WHITE = "\033[97m";

    const FOREGROUNDS = [
        self::ANSI_BLACK,
        self::ANSI_RED,
        self::ANSI_GREEN,
        self::ANSI_YELLOW,
        self::ANSI_BLUE,
        self::ANSI_PURPLE,
        self::ANSI_CYAN,
        self::ANSI_WHITE,
        self::ANSI_BRIGHT_BLACK,
        self::ANSI_BRIGHT_RED,
        self::ANSI_BRIGHT_GREEN,
        self::ANSI_BRIGHT_YELLOW,
        self::ANSI_BRIGHT_BLUE,
        self::ANSI_BRIGHT_PURPLE,
        self::ANSI_BRIGHT_CYAN,
        self::ANSI_BRIGHT_WHITE
    ];

    const ANSI_BG_BLACK = "\033[40m";
    const ANSI_BG_RED = "\033[41m";
    const ANSI_BG_GREEN = "\033[42m";
    const ANSI_BG_YELLOW = "\033[43m";
    const ANSI_BG_BLUE = "\033[44m";
    const ANSI_BG_PURPLE = "\033[45m";
    const ANSI_BG_CYAN = "\033[46m";
    const ANSI_BG_WHITE = "\033[47m";

    const ANSI_BRIGHT_BG_BLACK = "\033[100m";
    const ANSI_BRIGHT_BG_RED = "\033[101m";
    const ANSI_BRIGHT_BG_GREEN = "\033[102m";
    const ANSI_BRIGHT_BG_YELLOW = "\033[103m";
    const ANSI_BRIGHT_BG_BLUE = "\033[104m";
    const ANSI_BRIGHT_BG_PURPLE = "\033[105m";
    const ANSI_BRIGHT_BG_CYAN = "\033[106m";
    const ANSI_BRIGHT_BG_WHITE = "\033[107m";

    const BACKGROUNDS = [
        self::ANSI_BG_BLACK,
        self::ANSI_BG_RED,
        self::ANSI_BG_GREEN,
        self::ANSI_BG_YELLOW,
        self::ANSI_BG_BLUE,
        self::ANSI_BG_PURPLE,
        self::ANSI_BG_CYAN,
        self::ANSI_BG_WHITE,
        self::ANSI_BRIGHT_BG_BLACK,
        self::ANSI_BRIGHT_BG_RED,
        self::ANSI_BRIGHT_BG_GREEN,
        self::ANSI_BRIGHT_BG_YELLOW,
        self::ANSI_BRIGHT_BG_BLUE,
        self::ANSI_BRIGHT_BG_PURPLE,
        self::ANSI_BRIGHT_BG_CYAN,
        self::ANSI_BRIGHT_BG_WHITE
    ];

    static function printf($text, $colors, ...$args)
    {
        printf(self::colorize($text, $colors), ...$args);
    }

    static function print($text, $colors)
    {
        self::printf($text, $colors);
    }

    static function colorize($text, $colors)
    {
        $cls = new ReflectionClass(self::class);
        $ansi_colors = implode(
            '',
            array_map(function ($color) use ($cls) {
                $const_name = 'ANSI_' . kebab_case_to_screaming_snake_case($color);
                return $cls->getConstant($const_name);
            }, explode(',', $colors))
        );
        return $ansi_colors . $text . self::ANSI_RESET;
    }

    static function bold($text)
    {
        return self::ANSI_BOLD . $text . self::ANSI_BOLD_OFF;
    }

    static function demo()
    {
        printf("\n  Default text\n");

        foreach (self::FOREGROUNDS as $fg) {
            foreach (self::BACKGROUNDS as $bg) {
                printf($fg . $bg . '  TEST  ');
            }
            printf(self::ANSI_RESET . "\n");
        }

        printf(self::ANSI_RESET . "\n  Back to default.\n");
    }
}
