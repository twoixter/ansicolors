#!/usr/bin/env php
<?php

    require_once __DIR__."/vendor/autoload.php";

    echo ansi::White("ansi::colors example").PHP_EOL;
    echo PHP_EOL;
    echo "This is ".ansi::blue("blue")." and this is ".ansi::green("green").PHP_EOL;
    echo "Capitalize color name to output brighter colors: ";
    echo ansi::Blue("Blue")." and ".ansi::Green("Green").PHP_EOL;
    echo PHP_EOL;
    echo "Calling colors without arguments yields only the color code,".PHP_EOL;
    echo "which remains active until you output another color or use".PHP_EOL;
    echo "ansi::restore, or ansi::reset.".PHP_EOL;
    echo PHP_EOL;

    $simple_colors = array(
        ansi::red(), ansi::green(), ansi::yellow(), ansi::blue(),
        ansi::magenta(), ansi::cyan(), ansi::white()
    );
    foreach ($simple_colors as $c) {
        echo $c."Simple color simple color simple color simple color".PHP_EOL;
    }
    echo ansi::reset();

    $bright_colors = array(
        ansi::RED(), ansi::GREEN(), ansi::YELLOW(), ansi::BLUE(),
        ansi::MAGENTA(), ansi::CYAN(), ansi::WHITE()
    );
    foreach ($bright_colors as $c) {
        echo $c."Bright color bright color bright color bright color".PHP_EOL;
    }
    echo ansi::reset();

    $composite_colors = array(
        ansi::White_on_red(), ansi::White_on_green(), ansi::Black_on_yellow(),
        ansi::White_on_blue(), ansi::Blue_on_magenta(), ansi::Black_on_cyan(),
        ansi::red_on_white()
    );
    foreach ($composite_colors as $c) {
        echo $c."Background colors background colors background color".ansi::reset().PHP_EOL;
    }
    echo ansi::reset();
    echo PHP_EOL;

    echo "Redefine custom colors for a more semantic usage: ".PHP_EOL;
    echo ansi::White("ansi::define('error', 'White_on_red')")." ====> ";
    echo ansi::White("ansi::error('Yup! Something was wrong!')").PHP_EOL;

    echo PHP_EOL;
    echo ansi::Green("Colors are automatically stripped when piped!!").PHP_EOL;
    echo "Use '".ansi::White("php examples.php | less")."' to test.".PHP_EOL;

    echo PHP_EOL;
    echo "See documentation at ".ansi::BLUE("http://github.com/twoixter/ansicolors").PHP_EOL;
    echo "for more information.".PHP_EOL;
    echo PHP_EOL;


