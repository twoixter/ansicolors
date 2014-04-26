# ansi::[colors] for PHP CLI

**ansi::[colors]** is a simple library for PHP CLI applications. Made for simplicity, it avoids complicated methods for ANSI code output.

By complicated methods I mean something like:

```php
<?php

    $color = new \MyNamespace\ANSISuperClass::color_factory(\MyNamespace::ANSI_RED_COLOR);
    echo $color->generate_ansi_codes("E");

?>
```

You get the point. I want to avoid a "FactorySingletonGenerator" to just print a red "E".

----

## Installation

Just use Composer. Add this to your `composer.json`:

```json
{
    ...
    "require" : {
        ....
        "twoixter/ansicolors" : "~1.0"
    }
}
```

Or you just can download the `lib/ansi.php` file to your own source directory. Require it and you're ready to go.

## Usage

**ansi:[colors]** uses a global Class. You **don't** need to instantiate it, just use it's static methods. If the name `ansi` in lower case collides with some other global class of your own, please rename your class. :-)

Example usage without arguments:

```php
<?php echo ansi::red()."this is red".ansi::reset(); ?>
```

Note that you need to `ansi::reset()` if you don't use arguments or your text will be red forever, even when you exit the PHP script.

Alternatively, you can put some strings inside the method:

```php
<?php echo ansi::red("this is red");  # No need to reset ?>
```

The string `this is red` will be printed in red and automatically reset to the previous console color.

### Available colors

Available colors are the usual suspects: `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan` and `white`.

Use then as static methods to the `ansi` class:

```
ansi::black(...)
ansi::red(...)
```

...and so on. They return a string containing the ANSI escape sequences, you must output it yourself, nothing is automatically echoed to the console.

### Uppercase and lowercase methods

The above eight color names are lowercase. It is on purpose. Lower case name colors are dull, the brighter ones are **UPPERCASE** or **CamelCased**. Example:

```
ansi::White(...)    # Bright white. Alternate: ansi::WHITE()
ansi::Red(...)      # Bright red. Alternate: ansi::RED()
```

### Background colors

You can not change the background color on its own, you must add also a foreground color using the following form:

```php
<? echo ansi::Red_on_white("Yep!"); ?>
```

The string `Yep!` will use a bright red foreground color over a white background. Background colors are all dull, `Red_on_White` has no effect, even when using all uppercase.

You can use all combinations of colors for foreground and background. Examples:

```
ansi::Red_on_white(), ansi::White_on_blue(), ansi::Black_on_green()...
```

### Named colors

**ansi::[colors]** supports color aliasing as named colors. Use `ansi::define("name", "color");` to create a new named color.

Example:
```php
<?php

	# Define some new color names
    ansi::define("error", "Red_on_white");
    ansi::define("success", "Green");

	# Definitions can be recursive
    ansi::define("default", "success");

	# Use the new named colors
	echo ansi::success("The file has been copied successfully!");
    echo ansi::error("Watch out! Something was wrong...");

?>
```

### Support for pipes

**ansi:[colors]** is smart enough to disable itself when piped. So you can do things like:

```
$ php myscript.php | less
$ php myscript.php > output_file.txt
```

And you can be sure no ANSI codes will mangle your output. Perfect for logging to file, for exmple, or using `less` to paginate.

## License

Licensed under the MIT license -- http://opensource.org/licenses/MIT

If you like **ansi::[colors]**, please send some cheers to my Twitter at @twoixter. If you find some bugs, please fork and send me a pull request, I'm open to suggestions except changing the class name `ansi` to uppercase `Ansi`... *(Just kidding)* :-)
