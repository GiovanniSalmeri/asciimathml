# AsciiMathMl

Convert AsciiMath to MathML.

## Introduction

This PHP class converts [AsciiMath](http://asciimath.org/) expressions in MathML. Since January 2023, MathML works across the [latest devices and major browser versions](https://caniuse.com/mathml).

PHP >= 7.0 is required.

## Class synopsis

```php
class AsciiMathMl {

    /* Methods */
    public __construct(string $decimal = ".", bool $isAnnotated = true)
    public parseMath(string $str, bool $isDisplay = true): string
	
}
```

## Example

```php
require "asciimathml.php";

$parser = new AsciiMathMl();
$asciimath = "x=(-b+-sqrt(b^2-4ac))/(2a)";
$mathml = $parser->parseMath($asciimath);
```

## Acknowledgements

This class is a port of the original [asciimathml](https://github.com/asciimath/asciimathml) 2.2 by Peter Jipsen and other contributors.

## Developer

Giovanni Salmeri.
