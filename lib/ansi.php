<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Jose Miguel Pérez Ruiz [@twoixter]
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @package    ansicolors
 * @author     Jose Miguel Pérez Ruiz <josemiguel@perezruiz.com>
 * @copyright  2014 Jose Miguel
 * @license    http://opensource.org/licenses/MIT  The MIT License
 */

/**
 * Main "ansi" class. Defined in the root namespace to be able to use it as
 * static class. Just imagine it is a namespace...
 *
 * This class is mainly static. An example of usage follows:
 *
 *  <?
 *      // Outputs the ANSI codes for red background but DOES NOT reset it.
 *      // Color remains red until reset.
 *      echo ansi::red();   
 *
 *      // Reset whatever color was in effect
 *      echo ansi::reset();
 *
 *      // Outputs the string in blue color, auto resets color after the string.
 *      echo ansi::blue("This is colored blue"):
 *
 *  ?>
 *
 * Normal colors vs. bright colors.
 * --------------------------------
 *
 * By default, color names in lowercase are faint, normal, or NOT bright. To
 * output the bright color version use the name in all UPPER or Camel Case.
 *
 * Examples:     ansi::BLUE(), ansi::Blue() ==> Bright blue
 *
 *
 * Backgrounds
 * -----------
 *
 * Concatenate colors by "_on_" or "_in_" like in "Black_on_white". This
 * outputs text in a foreground black color over a white background (reversed).
 *
 *
 */
class ansi
{
    /**
     * Main color table. Index the color names to their binary values
     */
    static private $colors = array(
        "black"     => 0,  "red"       => 1,  "green"     => 2,
        "yellow"    => 3,  "blue"      => 4,  "magenta"   => 5,
        "cyan"      => 6,  "white"     => 7
    );

    /**
     * Same for styles. Most are not yet used, only "bold" for bright
     * foreground colors.
     */
    static private $styles = array(
        "bold"      => 1,   "normal"    => 2,   "underline" => 4,
        "blink"     => 5,   "reverse"   => 7,   "hidden"    => 8
    );

    /**
     * Variable that holds wether we are in a TTY or piped to a stream.
     * If we are piped, no ANSI color codes are returned.
     *
     * Hopefully being static also works as a cache, in order to prevent all
     * those calls to "posix_isatty" everytime we need to output a color.
     */
    static private $piped;

    /**
     * Color aliases. Dictionary containing the new configured color aliases.
     */
    static private $aliases = array();

    /**
     * The color stack. Here we will be updating the color values in a FIFO
     * such that we can restore previous colors.
     */
    static private $color_stack = array();

    /**
     * Static "catch all" method, used for color names and foreground and
     * background combinations.
     *
     * @param string $method        The method to call
     * @param mixed[] $args         Original parameters as array
     * @return string|null          Returns the ANSI codes, or null if not a color
     * @throws BadMethodCallException
     */
    static public function __callStatic($method, $args)
    {
        // Don't output ANSI codes if not a TTY.
        // Using a static var for caching the posix_isatty call.
        static::$piped = !posix_isatty(STDOUT);
        if (static::$piped) return implode(" ", array_map("strval", $args));

        // Manage aliases
        if (array_key_exists($method, static::$aliases)) {
            return static::__callStatic(static::$aliases[$method], $args);
        }

        // Return if this doesn't seem like a color.
        if (($color_code = static::parse_color($method)) === null) {
            throw new BadMethodCallException("Color name '".$method."' not found.");
        }

        // In case of arguments, try to print the passed string, preserving
        // the current color, if any.
        if (count($args)) {
            return implode("", array(
                static::code_to_ansi($color_code),
                implode(" ", array_map("strval", $args)),
                count(static::$color_stack) ? static::code_to_ansi(end(static::$color_stack)) : "\033[0m"
            ));
        }

        array_push(static::$color_stack, $color_code);
        return static::code_to_ansi($color_code);
    }

    /**
     * Non-static "catch all" method, just in case we are called from
     * an instance instead of the static interface.
     *
     * @param string $method        The method to call
     * @param mixed[] $args         Original parameters as array
     * @return string|null          Returns the ANSI codes, or null if not a color
     */
    public function __call($method, $args)
    {
        return static::__callStatic($method, $args);
    }

    /**
     * Restore the previous color, or "reset" if none was previously active.
     *
     * @return  string          The ANSI string to restore de color or reset.
     */
    static function restore()
    {
        // Don't output ANSI codes if not a TTY.
        // Using a static var for caching the posix_isatty call.
        static::$piped = !posix_isatty(STDOUT);
        if (static::$piped) return "";

        // Ok, what I'm going to do is counter intuitive. The color on the
        // top of the stack is the _current_ color, so in order to restore
        // the previous color, we must remove it from the stack.
        array_pop(static::$color_stack);
        return count(static::$color_stack)
                ? static::code_to_ansi(end(static::$color_stack))
                : static::reset();
    }

    /**
     * Resets the colors and restores the color codes stack.
     *
     * @return  string          The ANSI reset string
     */
    static function reset()
    {
        // Don't output ANSI codes if not a TTY.
        // Using a static var for caching the posix_isatty call.
        static::$piped = !posix_isatty(STDOUT);
        if (static::$piped) return "";

        static::$color_stack = array();
        return "\033[0m";
    }

    /**
     * Defines a new named color. Useful to configure new semantic color names
     * for specific combinations. For example:
     *
     *  ansi::define("error", "Red_on_white");
     *
     * This example defines "error" (use like ansi::error(), etc) to be like
     * the call to "ansi::Red_on_white()".
     *
     * Definitions are recursive, that is, aliases can be nested. Example:
     *
     *  ansi::define("error", "Red_on_white");
     *  ansi::define("default", "error");
     *
     * Now, unsing ansi::default() will use "ansi::error", which in turn is
     * "Red_on_white".
     *
     * @param string $name      The name of the new color
     * @param string $color     The color combination to use
     */
    static function define($name, $color)
    {
        static::$aliases[$name] = $color;
    }

    /**
     * Parses a color string, either a single color or a combination of colors
     * with format [color]_in_[color]
     *
     * @param  string $color    The color string to parse.
     * @return integer|null     Returns the color code or null if not a color.
     */
    private static function parse_color($color)
    {
        if (strpos($color, "_") === false) return static::parse_single_color($color);
        if (!preg_match("/^([^_]+)_(?:in|on)_([^_]+)$/i", $color, $m)) return null;

        list($_, $fore, $back) = $m;
        $fore = static::parse_single_color($fore);
        $back = static::parse_single_color($back);

        if ($fore === null || $back === null) return null;

        return ($back * 0x100) + $fore;
    }

    /**
     * Parses a single color, looking into the color array and detecting if
     * the color must be bright.
     *
     * @param string $color     The color to parse. Must be a single color.
     * @return integer|null     Returns the color code or null if not a color.
     */
    private static function parse_single_color($color)
    {
        $isupper = ctype_upper($color[0]);
        $colower = strtolower($color);
        if (array_key_exists($colower, static::$colors)) {
            return static::$colors[$colower] + ($isupper ? 0x10 : 0);
        }
        // Not a color...
        return null;
    }

    /**
     * Converts the color code bits into an ANSI representation.
     * Bits 0-3 are foreground color (0x007)
     * Bit  4 is brightness indicator (0x010)
     * Bits 8-11 are background color (0x700)
     *
     * @param integer $code     The color code to convert to ANSI string
     * @return string           Returns the ANSI string ready to print
     */
    private static function code_to_ansi($code)
    {
        $codes = array();
        if ($code & 0xF00) $codes[] = 40 + (($code & 0xF00) >> 8);
        if ($code & 0x010) $codes[] = static::$styles["bold"];
        $codes[] = 30 + ($code & 0xF);
        return "\033[".implode(";", $codes)."m";
    }

}

