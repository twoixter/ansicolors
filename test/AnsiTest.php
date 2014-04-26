<?php

class AnsiTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        ansi::reset();
    }

    /**
     * Tests the class is defined. Well, a dummy test if you want.
     * Should be loaded by the PSR-0 class loader anyway.
     */
    public function test_class_defined()
    {
        $ansi = new \ansi();
        $this->assertInstanceOf("\ansi", $ansi);
    }

    /**
     * Test the private color parsing function. Single colors.
     *
     * @depends test_class_defined
     */
    public function test_parse_single_color()
    {
        $m = new \ReflectionMethod("ansi", "parse_single_color");
        $m->setAccessible(true);

        $this->assertEquals(0x00, $m->invoke("ansi", "black"));
        $this->assertEquals(0x01, $m->invoke("ansi", "red"));
        $this->assertEquals(0x02, $m->invoke("ansi", "green"));
        $this->assertEquals(0x03, $m->invoke("ansi", "yellow"));
        $this->assertEquals(0x04, $m->invoke("ansi", "blue"));
        $this->assertEquals(0x05, $m->invoke("ansi", "magenta"));
        $this->assertEquals(0x06, $m->invoke("ansi", "cyan"));
        $this->assertEquals(0x07, $m->invoke("ansi", "white"));

        $this->assertEquals(0x10, $m->invoke("ansi", "BLACK"));
        $this->assertEquals(0x11, $m->invoke("ansi", "RED"));
        $this->assertEquals(0x12, $m->invoke("ansi", "GREEN"));
        $this->assertEquals(0x13, $m->invoke("ansi", "YELLOW"));
        $this->assertEquals(0x14, $m->invoke("ansi", "BLUE"));
        $this->assertEquals(0x15, $m->invoke("ansi", "MAGENTA"));
        $this->assertEquals(0x16, $m->invoke("ansi", "CYAN"));
        $this->assertEquals(0x17, $m->invoke("ansi", "WHITE"));

        $this->assertEquals(0x10, $m->invoke("ansi", "Black"));
        $this->assertEquals(0x11, $m->invoke("ansi", "Red"));
        $this->assertEquals(0x12, $m->invoke("ansi", "Green"));
        $this->assertEquals(0x13, $m->invoke("ansi", "Yellow"));
        $this->assertEquals(0x14, $m->invoke("ansi", "Blue"));
        $this->assertEquals(0x15, $m->invoke("ansi", "Magenta"));
        $this->assertEquals(0x16, $m->invoke("ansi", "Cyan"));
        $this->assertEquals(0x17, $m->invoke("ansi", "White"));
    }


    /**
     * Test the private color parsing function. Combined colors.
     *
     * @depends test_class_defined
     */
    public function test_parse_color_pairs()
    {
        $m = new \ReflectionMethod("ansi", "parse_color");
        $m->setAccessible(true);

        $this->assertEquals(0x0700, $m->invoke("ansi", "black_in_white"));
        $this->assertEquals(0x1710, $m->invoke("ansi", "Black_in_White"));
        $this->assertEquals(0x0417, $m->invoke("ansi", "White_on_blue"));
        $this->assertEquals(0x0117, $m->invoke("ansi", "White_on_red"));
    }

    /**
     * Test the "reset" function. Must return the ANSI clear scape sequence
     *
     * @depends test_class_defined
     */
    public function test_reset_color()
    {
        $str = ansi::reset();
        $this->assertEquals("\033[0m", $str);
    }

    /**
     * Test all foreground colors...
     *
     * @depends test_class_defined
     */
    public function test_foreground_colors()
    {
        $this->assertEquals("\033[30m", ansi::black());
        $this->assertEquals("\033[31m", ansi::red());
        $this->assertEquals("\033[32m", ansi::green());
        $this->assertEquals("\033[33m", ansi::yellow());
        $this->assertEquals("\033[34m", ansi::blue());
        $this->assertEquals("\033[35m", ansi::magenta());
        $this->assertEquals("\033[36m", ansi::cyan());
        $this->assertEquals("\033[37m", ansi::white());
    }

    /**
     * Test all bright colors...
     *
     * @depends test_class_defined
     */
    public function test_foreground_bright_colors()
    {
        $this->assertEquals("\033[1;30m", ansi::Black());
        $this->assertEquals("\033[1;31m", ansi::Red());
        $this->assertEquals("\033[1;32m", ansi::Green());
        $this->assertEquals("\033[1;33m", ansi::Yellow());
        $this->assertEquals("\033[1;34m", ansi::Blue());
        $this->assertEquals("\033[1;35m", ansi::Magenta());
        $this->assertEquals("\033[1;36m", ansi::Cyan());
        $this->assertEquals("\033[1;37m", ansi::White());
    }

    /**
     * Test background with foreground colors...
     *
     * @depends test_class_defined
     */
    public function test_foreground_with_background_colors()
    {
        // Bright foreground camel case
        $this->assertEquals("\033[47;1;30m", ansi::Black_on_white());
        $this->assertEquals("\033[47;1;31m", ansi::Red_on_white());
        $this->assertEquals("\033[43;1;32m", ansi::Green_on_yellow());

        // Bright foreground all caps
        $this->assertEquals("\033[47;1;30m", ansi::BLACK_ON_WHITE());
        $this->assertEquals("\033[47;1;31m", ansi::RED_ON_WHITE());
        $this->assertEquals("\033[43;1;32m", ansi::GREEN_ON_YELLOW());

        // Normal foreground camel case
        $this->assertEquals("\033[47;30m", ansi::black_on_white());
        $this->assertEquals("\033[47;31m", ansi::red_on_white());
        $this->assertEquals("\033[43;32m", ansi::green_on_yellow());
    }

    /**
     * Test colors with strings...
     *
     * @depends test_class_defined
     */
    public function test_color_with_strings()
    {
        // Bright foreground camel case
        $this->assertEquals("\033[31mTWOIXTER\033[0m", ansi::red("TWOIXTER"));
    }

    /**
     * Test unrecognized colors throws exceptions
     *
     * @depends test_class_defined
     * @expectedException BadMethodCallException
     */
    public function test_undefined_color_exception()
    {
        // Hopefully the color "yabadabadoo" is not defined. :-)
        $this->assertEquals("", ansi::yabadabadoo());
    }

    /**
     * Color code stack tests.
     *
     * @depends test_class_defined
     */
    public function test_color_is_saved_and_restored()
    {
        $color_blue = ansi::blue();
        $color_red  = ansi::red();
        $restored   = ansi::restore();
        $reset      = ansi::restore();

        $this->assertEquals("\033[34m", $color_blue);
        $this->assertEquals("\033[31m", $color_red);
        $this->assertEquals("\033[34m", $restored);
        $this->assertEquals("\033[0m", $reset);
    }

    /**
     * Color code definitions / aliases.
     *
     * @depends test_class_defined
     */
    public function test_color_aliases()
    {
        ansi::define("error", "Red_on_white");
        ansi::define("success", "GREEN");
        ansi::define("warning", "Yellow");

        $this->assertEquals("\033[47;1;31mWTF!\033[0m", ansi::error("WTF!"));
        $this->assertEquals("\033[1;32mOK\033[0m", ansi::success("OK"));
        $this->assertEquals("\033[1;33mDOH!\033[0m", ansi::warning("DOH!"));
    }

    /**
     * Test stripping on piped stream
     *
     * @depends test_class_defined
     */
    public function test_stripped_color_codes()
    {
        $tmpname1 = tempnam("/tmp", "ansi_colors_code_");
        $tmpname2 = tempnam("/tmp", "ansi_colors_result_");
        $this->assertTrue(strlen($tmpname1) > 0);
        $this->assertTrue(strlen($tmpname2) > 0);

        $phpfile = realpath(__DIR__."/../lib/ansi.php");
        $phpcode = <<<EOF
<?php
        require("${phpfile}");
        echo ansi::blue("twoixter");
EOF;

        file_put_contents($tmpname1, $phpcode);
        system("php $tmpname1 > $tmpname2");
        $result = file_get_contents($tmpname2);
        unlink($tmpname1);
        unlink($tmpname2);

        $this->assertEquals("twoixter", $result);
    }

}
