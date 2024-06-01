<?php
class AsciiMathMl {

/*
This class is a PHP port of ASCIIMathML.js 2.2 Mar 3, 2014.
https://github.com/asciimath/asciimathml

This is the copyright notice of the original ASCIIMathML.js:

Copyright (c) 2014 Peter Jipsen and other ASCIIMathML.js contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

    const CONST = 0, UNARY = 1, BINARY = 2, INFIX = 3, LEFTBRACKET = 4,
        RIGHTBRACKET = 5, SPACE = 6, UNDEROVER = 7, DEFINITION = 8,
        LEFTRIGHT = 9, TEXT = 10, BIG = 11, LONG = 12, STRETCHY = 13,
        MATRIX = 14, UNARYUNDEROVER = 15; // token types

    const CAL = [ "\u{1d49c}", "\u{212c}", "\u{1d49e}", "\u{1d49f}", "\u{2130}", "\u{2131}", "\u{1d4a2}", "\u{210b}", "\u{2110}", "\u{1d4a5}", "\u{1d4a6}", "\u{2112}", "\u{2133}", "\u{1d4a9}", "\u{1d4aa}", "\u{1d4ab}", "\u{1d4ac}", "\u{211b}", "\u{1d4ae}", "\u{1d4af}", "\u{1d4b0}", "\u{1d4b1}", "\u{1d4b2}", "\u{1d4b3}", "\u{1d4b4}", "\u{1d4b5}", "\u{1d4b6}", "\u{1d4b7}", "\u{1d4b8}", "\u{1d4b9}", "\u{212f}", "\u{1d4bb}", "\u{210a}", "\u{1d4bd}", "\u{1d4be}", "\u{1d4bf}", "\u{1d4c0}", "\u{1d4c1}", "\u{1d4c2}", "\u{1d4c3}", "\u{2134}", "\u{1d4c5}", "\u{1d4c6}", "\u{1d4c7}", "\u{1d4c8}", "\u{1d4c9}", "\u{1d4ca}", "\u{1d4cb}", "\u{1d4cc}", "\u{1d4cd}", "\u{1d4ce}", "\u{1d4cf}", ];
    const FRK = [ "\u{1d504}", "\u{1d505}", "\u{212d}", "\u{1d507}", "\u{1d508}", "\u{1d509}", "\u{1d50a}", "\u{210c}", "\u{2111}", "\u{1d50d}", "\u{1d50e}", "\u{1d50f}", "\u{1d510}", "\u{1d511}", "\u{1d512}", "\u{1d513}", "\u{1d514}", "\u{211c}", "\u{1d516}", "\u{1d517}", "\u{1d518}", "\u{1d519}", "\u{1d51a}", "\u{1d51b}", "\u{1d51c}", "\u{2128}", "\u{1d51e}", "\u{1d51f}", "\u{1d520}", "\u{1d521}", "\u{1d522}", "\u{1d523}", "\u{1d524}", "\u{1d525}", "\u{1d526}", "\u{1d527}", "\u{1d528}", "\u{1d529}", "\u{1d52a}", "\u{1d52b}", "\u{1d52c}", "\u{1d52d}", "\u{1d52e}", "\u{1d52f}", "\u{1d530}", "\u{1d531}", "\u{1d532}", "\u{1d533}", "\u{1d534}", "\u{1d535}", "\u{1d536}", "\u{1d537}", ];
    const BBB = [ "\u{1d538}", "\u{1d539}", "\u{2102}", "\u{1d53b}", "\u{1d53c}", "\u{1d53d}", "\u{1d53e}", "\u{210d}", "\u{1d540}", "\u{1d541}", "\u{1d542}", "\u{1d543}", "\u{1d544}", "\u{2115}", "\u{1d546}", "\u{2119}", "\u{211a}", "\u{211d}", "\u{1d54a}", "\u{1d54b}", "\u{1d54c}", "\u{1d54d}", "\u{1d54e}", "\u{1d54f}", "\u{1d550}", "\u{2124}", "\u{1d552}", "\u{1d553}", "\u{1d554}", "\u{1d555}", "\u{1d556}", "\u{1d557}", "\u{1d558}", "\u{1d559}", "\u{1d55a}", "\u{1d55b}", "\u{1d55c}", "\u{1d55d}", "\u{1d55e}", "\u{1d55f}", "\u{1d560}", "\u{1d561}", "\u{1d562}", "\u{1d563}", "\u{1d564}", "\u{1d565}", "\u{1d566}", "\u{1d567}", "\u{1d568}", "\u{1d569}", "\u{1d56a}", "\u{1d56b}", ];

    private $decimal; // only for output
    private $isAnnotated;

    private $dom;
    private $symbols, $names;
    private $nestingDepth, $previousSymbol, $currentSymbol;

    public function __construct($decimal = ".", $isAnnotated = true) {
        $this->decimal = $decimal;
        $this->isAnnotated = $isAnnotated;
        $this->symbols = [
            // some greek symbols
            [ "input"=>"alpha",  "tag"=>"mi", "output"=>"\u{03B1}", "ttype"=>self::CONST ],
            [ "input"=>"beta",   "tag"=>"mi", "output"=>"\u{03B2}", "ttype"=>self::CONST ],
            [ "input"=>"chi",    "tag"=>"mi", "output"=>"\u{03C7}", "ttype"=>self::CONST ],
            [ "input"=>"delta",  "tag"=>"mi", "output"=>"\u{03B4}", "ttype"=>self::CONST ],
            [ "input"=>"Delta",  "tag"=>"mo", "output"=>"\u{0394}", "ttype"=>self::CONST ],
            [ "input"=>"epsi",   "tag"=>"mi", "output"=>"\u{03B5}", "tex"=>"epsilon", "ttype"=>self::CONST ],
            [ "input"=>"varepsilon", "tag"=>"mi", "output"=>"\u{025B}", "ttype"=>self::CONST ],
            [ "input"=>"eta",    "tag"=>"mi", "output"=>"\u{03B7}", "ttype"=>self::CONST ],
            [ "input"=>"gamma",  "tag"=>"mi", "output"=>"\u{03B3}", "ttype"=>self::CONST ],
            [ "input"=>"Gamma",  "tag"=>"mo", "output"=>"\u{0393}", "ttype"=>self::CONST ],
            [ "input"=>"iota",   "tag"=>"mi", "output"=>"\u{03B9}", "ttype"=>self::CONST ],
            [ "input"=>"kappa",  "tag"=>"mi", "output"=>"\u{03BA}", "ttype"=>self::CONST ],
            [ "input"=>"lambda", "tag"=>"mi", "output"=>"\u{03BB}", "ttype"=>self::CONST ],
            [ "input"=>"Lambda", "tag"=>"mo", "output"=>"\u{039B}", "ttype"=>self::CONST ],
            [ "input"=>"lamda", "tag"=>"mi", "output"=>"\u{03BB}", "ttype"=>self::CONST ],
            [ "input"=>"Lamda", "tag"=>"mo", "output"=>"\u{039B}", "ttype"=>self::CONST ],
            [ "input"=>"mu",     "tag"=>"mi", "output"=>"\u{03BC}", "ttype"=>self::CONST ],
            [ "input"=>"nu",     "tag"=>"mi", "output"=>"\u{03BD}", "ttype"=>self::CONST ],
            [ "input"=>"omega",  "tag"=>"mi", "output"=>"\u{03C9}", "ttype"=>self::CONST ],
            [ "input"=>"Omega",  "tag"=>"mo", "output"=>"\u{03A9}", "ttype"=>self::CONST ],
            [ "input"=>"phi",    "tag"=>"mi", "output"=>"\u{03D5}", "ttype"=>self::CONST ],
            [ "input"=>"varphi", "tag"=>"mi", "output"=>"\u{03C6}", "ttype"=>self::CONST ],
            [ "input"=>"Phi",    "tag"=>"mo", "output"=>"\u{03A6}", "ttype"=>self::CONST ],
            [ "input"=>"pi",     "tag"=>"mi", "output"=>"\u{03C0}", "ttype"=>self::CONST ],
            [ "input"=>"Pi",     "tag"=>"mo", "output"=>"\u{03A0}", "ttype"=>self::CONST ],
            [ "input"=>"psi",    "tag"=>"mi", "output"=>"\u{03C8}", "ttype"=>self::CONST ],
            [ "input"=>"Psi",    "tag"=>"mi", "output"=>"\u{03A8}", "ttype"=>self::CONST ],
            [ "input"=>"rho",    "tag"=>"mi", "output"=>"\u{03C1}", "ttype"=>self::CONST ],
            [ "input"=>"sigma",  "tag"=>"mi", "output"=>"\u{03C3}", "ttype"=>self::CONST ],
            [ "input"=>"Sigma",  "tag"=>"mo", "output"=>"\u{03A3}", "ttype"=>self::CONST ],
            [ "input"=>"tau",    "tag"=>"mi", "output"=>"\u{03C4}", "ttype"=>self::CONST ],
            [ "input"=>"theta",  "tag"=>"mi", "output"=>"\u{03B8}", "ttype"=>self::CONST ],
            [ "input"=>"vartheta", "tag"=>"mi", "output"=>"\u{03D1}", "ttype"=>self::CONST ],
            [ "input"=>"Theta",  "tag"=>"mo", "output"=>"\u{0398}", "ttype"=>self::CONST ],
            [ "input"=>"upsilon", "tag"=>"mi", "output"=>"\u{03C5}", "ttype"=>self::CONST ],
            [ "input"=>"xi",     "tag"=>"mi", "output"=>"\u{03BE}", "ttype"=>self::CONST ],
            [ "input"=>"Xi",     "tag"=>"mo", "output"=>"\u{039E}", "ttype"=>self::CONST ],
            [ "input"=>"zeta",   "tag"=>"mi", "output"=>"\u{03B6}", "ttype"=>self::CONST ],
            // binary operation symbols
            // [ "input"=>"-",  "tag"=>"mo", "output"=>"\u{0096}", "ttype"=>self::CONST ],
            [ "input"=>"*",  "tag"=>"mo", "output"=>"\u{22C5}", "tex"=>"cdot", "ttype"=>self::CONST ],
            [ "input"=>"**", "tag"=>"mo", "output"=>"\u{2217}", "tex"=>"ast", "ttype"=>self::CONST ],
            [ "input"=>"***", "tag"=>"mo", "output"=>"\u{22C6}", "tex"=>"star", "ttype"=>self::CONST ],
            [ "input"=>"//", "tag"=>"mo", "output"=>"/",      "ttype"=>self::CONST ],
            [ "input"=>"\\\\", "tag"=>"mo", "output"=>"\\",   "tex"=>"backslash", "ttype"=>self::CONST ],
            [ "input"=>"setminus", "tag"=>"mo", "output"=>"\\", "ttype"=>self::CONST ],
            [ "input"=>"xx", "tag"=>"mo", "output"=>"\u{00D7}", "tex"=>"times", "ttype"=>self::CONST ],
            [ "input"=>"|><", "tag"=>"mo", "output"=>"\u{22C9}", "tex"=>"ltimes", "ttype"=>self::CONST ],
            [ "input"=>"><|", "tag"=>"mo", "output"=>"\u{22CA}", "tex"=>"rtimes", "ttype"=>self::CONST ],
            [ "input"=>"|><|", "tag"=>"mo", "output"=>"\u{22C8}", "tex"=>"bowtie", "ttype"=>self::CONST ],
            [ "input"=>"-:", "tag"=>"mo", "output"=>"\u{00F7}", "tex"=>"div", "ttype"=>self::CONST ],
            [ "input"=>"divide",   "tag"=>"mo", "output"=>"-:", "ttype"=>self::DEFINITION ],
            [ "input"=>"@",  "tag"=>"mo", "output"=>"\u{2218}", "tex"=>"circ", "ttype"=>self::CONST ],
            [ "input"=>"o+", "tag"=>"mo", "output"=>"\u{2295}", "tex"=>"oplus", "ttype"=>self::CONST ],
            [ "input"=>"ox", "tag"=>"mo", "output"=>"\u{2297}", "tex"=>"otimes", "ttype"=>self::CONST ],
            [ "input"=>"o.", "tag"=>"mo", "output"=>"\u{2299}", "tex"=>"odot", "ttype"=>self::CONST ],
            [ "input"=>"sum", "tag"=>"mo", "output"=>"\u{2211}", "ttype"=>self::UNDEROVER ],
            [ "input"=>"prod", "tag"=>"mo", "output"=>"\u{220F}", "ttype"=>self::UNDEROVER ],
            [ "input"=>"^^",  "tag"=>"mo", "output"=>"\u{2227}", "tex"=>"wedge", "ttype"=>self::CONST ],
            [ "input"=>"^^^", "tag"=>"mo", "output"=>"\u{22C0}", "tex"=>"bigwedge", "ttype"=>self::UNDEROVER ],
            [ "input"=>"vv",  "tag"=>"mo", "output"=>"\u{2228}", "tex"=>"vee", "ttype"=>self::CONST ],
            [ "input"=>"vvv", "tag"=>"mo", "output"=>"\u{22C1}", "tex"=>"bigvee", "ttype"=>self::UNDEROVER ],
            [ "input"=>"nn",  "tag"=>"mo", "output"=>"\u{2229}", "tex"=>"cap", "ttype"=>self::CONST ],
            [ "input"=>"nnn", "tag"=>"mo", "output"=>"\u{22C2}", "tex"=>"bigcap", "ttype"=>self::UNDEROVER ],
            [ "input"=>"uu",  "tag"=>"mo", "output"=>"\u{222A}", "tex"=>"cup", "ttype"=>self::CONST ],
            [ "input"=>"uuu", "tag"=>"mo", "output"=>"\u{22C3}", "tex"=>"bigcup", "ttype"=>self::UNDEROVER ],
            // binary relation symbols
            [ "input"=>"!=",  "tag"=>"mo", "output"=>"\u{2260}", "tex"=>"ne", "ttype"=>self::CONST ],
            [ "input"=>":=",  "tag"=>"mo", "output"=>"\u{2254}", "ttype"=>self::CONST ], // changed in UTF-8, GS
            [ "input"=>"lt",  "tag"=>"mo", "output"=>"<",      "ttype"=>self::CONST ],
            [ "input"=>"<=",  "tag"=>"mo", "output"=>"\u{2264}", "tex"=>"le", "ttype"=>self::CONST ],
            [ "input"=>"lt=", "tag"=>"mo", "output"=>"\u{2264}", "tex"=>"leq", "ttype"=>self::CONST ],
            [ "input"=>"gt",  "tag"=>"mo", "output"=>">",      "ttype"=>self::CONST ],
            [ "input"=>"mlt", "tag"=>"mo", "output"=>"\u{226A}", "tex"=>"ll", "ttype"=>self::CONST ],
            [ "input"=>">=",  "tag"=>"mo", "output"=>"\u{2265}", "tex"=>"ge", "ttype"=>self::CONST ],
            [ "input"=>"gt=", "tag"=>"mo", "output"=>"\u{2265}", "tex"=>"geq", "ttype"=>self::CONST ],
            [ "input"=>"mgt", "tag"=>"mo", "output"=>"\u{226B}", "tex"=>"gg", "ttype"=>self::CONST ],
            [ "input"=>"-<",  "tag"=>"mo", "output"=>"\u{227A}", "tex"=>"prec", "ttype"=>self::CONST ],
            [ "input"=>"-lt", "tag"=>"mo", "output"=>"\u{227A}", "ttype"=>self::CONST ],
            [ "input"=>">-",  "tag"=>"mo", "output"=>"\u{227B}", "tex"=>"succ", "ttype"=>self::CONST ],
            [ "input"=>"-<=", "tag"=>"mo", "output"=>"\u{2AAF}", "tex"=>"preceq", "ttype"=>self::CONST ],
            [ "input"=>">-=", "tag"=>"mo", "output"=>"\u{2AB0}", "tex"=>"succeq", "ttype"=>self::CONST ],
            [ "input"=>"in",  "tag"=>"mo", "output"=>"\u{2208}", "ttype"=>self::CONST ],
            [ "input"=>"!in", "tag"=>"mo", "output"=>"\u{2209}", "tex"=>"notin", "ttype"=>self::CONST ],
            [ "input"=>"sub", "tag"=>"mo", "output"=>"\u{2282}", "tex"=>"subset", "ttype"=>self::CONST ],
            [ "input"=>"sup", "tag"=>"mo", "output"=>"\u{2283}", "tex"=>"supset", "ttype"=>self::CONST ],
            [ "input"=>"sube", "tag"=>"mo", "output"=>"\u{2286}", "tex"=>"subseteq", "ttype"=>self::CONST ],
            [ "input"=>"supe", "tag"=>"mo", "output"=>"\u{2287}", "tex"=>"supseteq", "ttype"=>self::CONST ],
            [ "input"=>"!sub", "tag"=>"mo", "output"=>"\u{2284}", "tex"=>"notsubset", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"!sup", "tag"=>"mo", "output"=>"\u{2285}", "tex"=>"notsupset", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"!sube", "tag"=>"mo", "output"=>"\u{2288}", "tex"=>"notsubseteq", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"!supe", "tag"=>"mo", "output"=>"\u{2289}", "tex"=>"notsupseteq", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"-=",  "tag"=>"mo", "output"=>"\u{2261}", "tex"=>"equiv", "ttype"=>self::CONST ],
            [ "input"=>"!-=",  "tag"=>"mo", "output"=>"\u{2262}", "tex"=>"notequiv", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"~=",  "tag"=>"mo", "output"=>"\u{2245}", "tex"=>"cong", "ttype"=>self::CONST ],
            [ "input"=>"~~",  "tag"=>"mo", "output"=>"\u{2248}", "tex"=>"approx", "ttype"=>self::CONST ],
            [ "input"=>"~",  "tag"=>"mo", "output"=>"\u{223C}", "tex"=>"sim", "ttype"=>self::CONST ],
            [ "input"=>"prop", "tag"=>"mo", "output"=>"\u{221D}", "tex"=>"propto", "ttype"=>self::CONST ],
            // logical symbols
            [ "input"=>"and", "tag"=>"mtext", "output"=>"and", "ttype"=>self::SPACE ],
            [ "input"=>"or",  "tag"=>"mtext", "output"=>"or",  "ttype"=>self::SPACE ],
            [ "input"=>"not", "tag"=>"mo", "output"=>"\u{00AC}", "tex"=>"neg", "ttype"=>self::CONST ],
            [ "input"=>"=>",  "tag"=>"mo", "output"=>"\u{21D2}", "tex"=>"implies", "ttype"=>self::CONST ],
            [ "input"=>"if",  "tag"=>"mo", "output"=>"if",     "ttype"=>self::SPACE ],
            [ "input"=>"<=>", "tag"=>"mo", "output"=>"\u{21D4}", "tex"=>"iff", "ttype"=>self::CONST ],
            [ "input"=>"AA",  "tag"=>"mo", "output"=>"\u{2200}", "tex"=>"forall", "ttype"=>self::CONST ],
            [ "input"=>"EE",  "tag"=>"mo", "output"=>"\u{2203}", "tex"=>"exists", "ttype"=>self::CONST ],
            [ "input"=>"_|_", "tag"=>"mo", "output"=>"\u{22A5}", "tex"=>"bot", "ttype"=>self::CONST ],
            [ "input"=>"TT",  "tag"=>"mo", "output"=>"\u{22A4}", "tex"=>"top", "ttype"=>self::CONST ],
            [ "input"=>"|--",  "tag"=>"mo", "output"=>"\u{22A2}", "tex"=>"vdash", "ttype"=>self::CONST ],
            [ "input"=>"|==",  "tag"=>"mo", "output"=>"\u{22A8}", "tex"=>"models", "ttype"=>self::CONST ],
            // grouping brackets
            [ "input"=>"(", "tag"=>"mo", "output"=>"(", "tex"=>"left(", "ttype"=>self::LEFTBRACKET ],
            [ "input"=>")", "tag"=>"mo", "output"=>")", "tex"=>"right)", "ttype"=>self::RIGHTBRACKET ],
            [ "input"=>"[", "tag"=>"mo", "output"=>"[", "tex"=>"left[", "ttype"=>self::LEFTBRACKET ],
            [ "input"=>"]", "tag"=>"mo", "output"=>"]", "tex"=>"right]", "ttype"=>self::RIGHTBRACKET ],
            [ "input"=>"{", "tag"=>"mo", "output"=>"{", "ttype"=>self::LEFTBRACKET ],
            [ "input"=>"}", "tag"=>"mo", "output"=>"}", "ttype"=>self::RIGHTBRACKET ],
            [ "input"=>"|", "tag"=>"mo", "output"=>"|", "ttype"=>self::LEFTRIGHT ],
            [ "input"=>":|:", "tag"=>"mo", "output"=>"|", "ttype"=>self::CONST ],
            [ "input"=>"|:", "tag"=>"mo", "output"=>"|", "ttype"=>self::LEFTBRACKET ],
            [ "input"=>":|", "tag"=>"mo", "output"=>"|", "ttype"=>self::RIGHTBRACKET ],
            // [ "input"=>"||", "tag"=>"mo", "output"=>"||", "ttype"=>self::LEFTRIGHT ],
            [ "input"=>"(:", "tag"=>"mo", "output"=>"\u{2329}", "tex"=>"langle", "ttype"=>self::LEFTBRACKET ],
            [ "input"=>":)", "tag"=>"mo", "output"=>"\u{232A}", "tex"=>"rangle", "ttype"=>self::RIGHTBRACKET ],
            [ "input"=>"<<", "tag"=>"mo", "output"=>"\u{2329}", "ttype"=>self::LEFTBRACKET ],
            [ "input"=>">>", "tag"=>"mo", "output"=>"\u{232A}", "ttype"=>self::RIGHTBRACKET ],
            [ "input"=>"{:", "tag"=>"mo", "output"=>"{:", "ttype"=>self::LEFTBRACKET, "invisible"=>true ],
            [ "input"=>":}", "tag"=>"mo", "output"=>":}", "ttype"=>self::RIGHTBRACKET, "invisible"=>true ],
            // miscellaneous symbols
            [ "input"=>"int",  "tag"=>"mo", "output"=>"\u{222B}", "ttype"=>self::CONST ],
            [ "input"=>"dx",   "tag"=>"mi", "output"=>"{:d x:}", "ttype"=>self::DEFINITION ],
            [ "input"=>"dy",   "tag"=>"mi", "output"=>"{:d y:}", "ttype"=>self::DEFINITION ],
            [ "input"=>"dz",   "tag"=>"mi", "output"=>"{:d z:}", "ttype"=>self::DEFINITION ],
            [ "input"=>"dt",   "tag"=>"mi", "output"=>"{:d t:}", "ttype"=>self::DEFINITION ],
            [ "input"=>"oint", "tag"=>"mo", "output"=>"\u{222E}", "ttype"=>self::CONST ],
            [ "input"=>"del",  "tag"=>"mo", "output"=>"\u{2202}", "tex"=>"partial", "ttype"=>self::CONST ],
            [ "input"=>"grad", "tag"=>"mo", "output"=>"\u{2207}", "tex"=>"nabla", "ttype"=>self::CONST ],
            [ "input"=>"+-",   "tag"=>"mo", "output"=>"\u{00B1}", "tex"=>"pm", "ttype"=>self::CONST ],
            [ "input"=>"-+",   "tag"=>"mo", "output"=>"\u{2213}", "tex"=>"mp", "ttype"=>self::CONST ],
            [ "input"=>"O/",   "tag"=>"mi", "output"=>"\u{2205}", "tex"=>"emptyset", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"oo",   "tag"=>"mi", "output"=>"\u{221E}", "tex"=>"infty", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"aleph", "tag"=>"mi", "output"=>"\u{2135}", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"...",  "tag"=>"mo", "output"=>"\u{2026}",    "tex"=>"ldots", "ttype"=>self::CONST ], // changed in UTF-8, GS
            [ "input"=>":.",  "tag"=>"mo", "output"=>"\u{2234}",  "tex"=>"therefore", "ttype"=>self::CONST ],
            [ "input"=>":'",  "tag"=>"mo", "output"=>"\u{2235}",  "tex"=>"because", "ttype"=>self::CONST ],
            [ "input"=>"/_",  "tag"=>"mo", "output"=>"\u{2220}",  "tex"=>"angle", "ttype"=>self::CONST ],
            [ "input"=>"/_\\",  "tag"=>"mo", "output"=>"\u{25B3}",  "tex"=>"triangle", "ttype"=>self::CONST ],
            [ "input"=>"'",   "tag"=>"mo", "output"=>"\u{2032}",  "tex"=>"prime", "ttype"=>self::CONST ],
            [ "input"=>"''",   "tag"=>"mo", "output"=>"\u{2033}",  "tex"=>"dprime", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"'''",   "tag"=>"mo", "output"=>"\u{2033}",  "tex"=>"trprime", "ttype"=>self::CONST ], // added, GS
            [ "input"=>"tilde", "tag"=>"mover", "output"=>"~", "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"\\ ",  "tag"=>"mo", "output"=>"\u{00A0}", "ttype"=>self::CONST ],
            [ "input"=>"frown",  "tag"=>"mo", "output"=>"\u{2322}", "ttype"=>self::CONST ],
            [ "input"=>"quad", "tag"=>"mo", "output"=>"\u{00A0}\u{00A0}", "ttype"=>self::CONST ],
            [ "input"=>"qquad", "tag"=>"mo", "output"=>"\u{00A0}\u{00A0}\u{00A0}\u{00A0}", "ttype"=>self::CONST ],
            [ "input"=>"cdots", "tag"=>"mo", "output"=>"\u{22EF}", "ttype"=>self::CONST ],
            [ "input"=>"vdots", "tag"=>"mo", "output"=>"\u{22EE}", "ttype"=>self::CONST ],
            [ "input"=>"ddots", "tag"=>"mo", "output"=>"\u{22F1}", "ttype"=>self::CONST ],
            [ "input"=>"diamond", "tag"=>"mo", "output"=>"\u{22C4}", "ttype"=>self::CONST ],
            [ "input"=>"square", "tag"=>"mo", "output"=>"\u{25A1}", "ttype"=>self::CONST ],
            [ "input"=>"|__", "tag"=>"mo", "output"=>"\u{230A}",  "tex"=>"lfloor", "ttype"=>self::CONST ],
            [ "input"=>"__|", "tag"=>"mo", "output"=>"\u{230B}",  "tex"=>"rfloor", "ttype"=>self::CONST ],
            [ "input"=>"|~", "tag"=>"mo", "output"=>"\u{2308}",  "tex"=>"lceiling", "ttype"=>self::CONST ],
            [ "input"=>"~|", "tag"=>"mo", "output"=>"\u{2309}",  "tex"=>"rceiling", "ttype"=>self::CONST ],
            [ "input"=>"CC",  "tag"=>"mi", "output"=>"\u{2102}", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"NN",  "tag"=>"mi", "output"=>"\u{2115}", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"QQ",  "tag"=>"mi", "output"=>"\u{211A}", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"RR",  "tag"=>"mi", "output"=>"\u{211D}", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"ZZ",  "tag"=>"mi", "output"=>"\u{2124}", "ttype"=>self::CONST ], // changed in mi, GS
            [ "input"=>"f",   "tag"=>"mi", "output"=>"f",      "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"g",   "tag"=>"mi", "output"=>"g",      "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"h",   "tag"=>"mi", "output"=>"h",      "ttype"=>self::UNARY, "func"=>true ], // added, GS
            [ "input"=>"P",   "tag"=>"mi", "output"=>"P",      "ttype"=>self::UNARY, "func"=>true ], // added, GS
            [ "input"=>"hbar", "tag"=>"mo", "output"=>"\u{210F}", "ttype"=>self::CONST ], // added, GS
            // standard functions
            [ "input"=>"lim",  "tag"=>"mo", "output"=>"lim", "ttype"=>self::UNDEROVER ],
            [ "input"=>"Lim",  "tag"=>"mo", "output"=>"Lim", "ttype"=>self::UNDEROVER ],
            [ "input"=>"sin",  "tag"=>"mo", "output"=>"sin", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"cos",  "tag"=>"mo", "output"=>"cos", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"tan",  "tag"=>"mo", "output"=>"tan", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"sinh", "tag"=>"mo", "output"=>"sinh", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"cosh", "tag"=>"mo", "output"=>"cosh", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"tanh", "tag"=>"mo", "output"=>"tanh", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"cot",  "tag"=>"mo", "output"=>"cot", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"sec",  "tag"=>"mo", "output"=>"sec", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"csc",  "tag"=>"mo", "output"=>"csc", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"arcsin",  "tag"=>"mo", "output"=>"arcsin", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"arccos",  "tag"=>"mo", "output"=>"arccos", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"arctan",  "tag"=>"mo", "output"=>"arctan", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"coth",  "tag"=>"mo", "output"=>"coth", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"sech",  "tag"=>"mo", "output"=>"sech", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"csch",  "tag"=>"mo", "output"=>"csch", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"exp",  "tag"=>"mo", "output"=>"exp", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"abs",   "tag"=>"mo", "output"=>"abs",  "ttype"=>self::UNARY, "rewriteleftright"=>["|", "|"] ],
            [ "input"=>"norm",   "tag"=>"mo", "output"=>"norm",  "ttype"=>self::UNARY, "rewriteleftright"=>["\u{2225}", "\u{2225}"] ],
            [ "input"=>"floor",   "tag"=>"mo", "output"=>"floor",  "ttype"=>self::UNARY, "rewriteleftright"=>["\u{230A}", "\u{230B}"] ],
            [ "input"=>"ceil",   "tag"=>"mo", "output"=>"ceil",  "ttype"=>self::UNARY, "rewriteleftright"=>["\u{2308}", "\u{2309}"] ],
            [ "input"=>"log",  "tag"=>"mo", "output"=>"log", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"ln",   "tag"=>"mo", "output"=>"ln",  "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"det",  "tag"=>"mo", "output"=>"det", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"dim",  "tag"=>"mo", "output"=>"dim", "ttype"=>self::CONST ],
            [ "input"=>"mod",  "tag"=>"mo", "output"=>"mod", "ttype"=>self::CONST ],
            [ "input"=>"gcd",  "tag"=>"mo", "output"=>"gcd", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"lcm",  "tag"=>"mo", "output"=>"lcm", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"lub",  "tag"=>"mo", "output"=>"lub", "ttype"=>self::CONST ],
            [ "input"=>"glb",  "tag"=>"mo", "output"=>"glb", "ttype"=>self::CONST ],
            [ "input"=>"min",  "tag"=>"mo", "output"=>"min", "ttype"=>self::UNDEROVER ],
            [ "input"=>"max",  "tag"=>"mo", "output"=>"max", "ttype"=>self::UNDEROVER ],
            [ "input"=>"Sin",  "tag"=>"mo", "output"=>"Sin", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Cos",  "tag"=>"mo", "output"=>"Cos", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Tan",  "tag"=>"mo", "output"=>"Tan", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Arcsin",  "tag"=>"mo", "output"=>"Arcsin", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Arccos",  "tag"=>"mo", "output"=>"Arccos", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Arctan",  "tag"=>"mo", "output"=>"Arctan", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Sinh", "tag"=>"mo", "output"=>"Sinh", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Cosh", "tag"=>"mo", "output"=>"Cosh", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Tanh", "tag"=>"mo", "output"=>"Tanh", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Cot",  "tag"=>"mo", "output"=>"Cot", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Sec",  "tag"=>"mo", "output"=>"Sec", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Csc",  "tag"=>"mo", "output"=>"Csc", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Log",  "tag"=>"mo", "output"=>"Log", "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Ln",   "tag"=>"mo", "output"=>"Ln",  "ttype"=>self::UNARY, "func"=>true ],
            [ "input"=>"Abs",   "tag"=>"mo", "output"=>"abs",  "ttype"=>self::UNARY, "notexcopy"=>true, "rewriteleftright"=>["|", "|"] ],
            // arrows
            [ "input"=>"uarr", "tag"=>"mo", "output"=>"\u{2191}", "tex"=>"uparrow", "ttype"=>self::CONST ],
            [ "input"=>"darr", "tag"=>"mo", "output"=>"\u{2193}", "tex"=>"downarrow", "ttype"=>self::CONST ],
            [ "input"=>"rarr", "tag"=>"mo", "output"=>"\u{2192}", "tex"=>"rightarrow", "ttype"=>self::CONST ],
            [ "input"=>"->",   "tag"=>"mo", "output"=>"\u{2192}", "tex"=>"to", "ttype"=>self::CONST ],
            [ "input"=>">->",   "tag"=>"mo", "output"=>"\u{21A3}", "tex"=>"rightarrowtail", "ttype"=>self::CONST ],
            [ "input"=>"->>",   "tag"=>"mo", "output"=>"\u{21A0}", "tex"=>"twoheadrightarrow", "ttype"=>self::CONST ],
            [ "input"=>">->>",   "tag"=>"mo", "output"=>"\u{2916}", "tex"=>"twoheadrightarrowtail", "ttype"=>self::CONST ],
            [ "input"=>"|->",  "tag"=>"mo", "output"=>"\u{21A6}", "tex"=>"mapsto", "ttype"=>self::CONST ],
            [ "input"=>"larr", "tag"=>"mo", "output"=>"\u{2190}", "tex"=>"leftarrow", "ttype"=>self::CONST ],
            [ "input"=>"harr", "tag"=>"mo", "output"=>"\u{2194}", "tex"=>"leftrightarrow", "ttype"=>self::CONST ],
            [ "input"=>"rArr", "tag"=>"mo", "output"=>"\u{21D2}", "tex"=>"Rightarrow", "ttype"=>self::CONST ],
            [ "input"=>"lArr", "tag"=>"mo", "output"=>"\u{21D0}", "tex"=>"Leftarrow", "ttype"=>self::CONST ],
            [ "input"=>"hArr", "tag"=>"mo", "output"=>"\u{21D4}", "tex"=>"Leftrightarrow", "ttype"=>self::CONST ],
            // commands with argument
            [ "input"=>"sqrt", "tag"=>"msqrt", "output"=>"sqrt", "ttype"=>self::UNARY ],
            [ "input"=>"root", "tag"=>"mroot", "output"=>"root", "ttype"=>self::BINARY ],
            [ "input"=>"frac", "tag"=>"mfrac", "output"=>"/",    "ttype"=>self::BINARY ],
            [ "input"=>"/",    "tag"=>"mfrac", "output"=>"/",    "ttype"=>self::INFIX ],
            [ "input"=>"stackrel", "tag"=>"mover", "output"=>"stackrel", "ttype"=>self::BINARY ],
            [ "input"=>"overset", "tag"=>"mover", "output"=>"stackrel", "ttype"=>self::BINARY ],
            [ "input"=>"underset", "tag"=>"munder", "output"=>"stackrel", "ttype"=>self::BINARY ],
            [ "input"=>"_",    "tag"=>"msub",  "output"=>"_",    "ttype"=>self::INFIX ],
            [ "input"=>"^",    "tag"=>"msup",  "output"=>"^",    "ttype"=>self::INFIX ],
            [ "input"=>"hat", "tag"=>"mover", "output"=>"\u{005E}", "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"bar", "tag"=>"mover", "output"=>"\u{00AF}", "tex"=>"overline", "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"vec", "tag"=>"mover", "output"=>"\u{2192}", "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"dot", "tag"=>"mover", "output"=>".",      "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"ddot", "tag"=>"mover", "output"=>"..",    "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"overarc", "tag"=>"mover", "output"=>"\u{23DC}", "tex"=>"overparen", "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"ul", "tag"=>"munder", "output"=>"\u{0332}", "tex"=>"underline", "ttype"=>self::UNARY, "acc"=>true ],
            [ "input"=>"ubrace", "tag"=>"munder", "output"=>"\u{23DF}", "tex"=>"underbrace", "ttype"=>self::UNARYUNDEROVER, "acc"=>true ],
            [ "input"=>"obrace", "tag"=>"mover", "output"=>"\u{23DE}", "tex"=>"overbrace", "ttype"=>self::UNARYUNDEROVER, "acc"=>true ],
            [ "input"=>"text", "tag"=>"mtext", "output"=>"text", "ttype"=>self::TEXT ],
            [ "input"=>"mbox", "tag"=>"mtext", "output"=>"mbox", "ttype"=>self::TEXT ],
            [ "input"=>"color", "tag"=>"mstyle", "output"=>"dummy", "ttype"=>self::BINARY ],
            [ "input"=>"id", "tag"=>"mrow", "output"=>"dummy", "ttype"=>self::BINARY ],
            [ "input"=>"class", "tag"=>"mrow", "output"=>"dummy", "ttype"=>self::BINARY ],
            [ "input"=>"cancel", "tag"=>"menclose", "output"=>"cancel", "ttype"=>self::UNARY ],

            [ "input"=>"\"", "tag"=>"mtext", "output"=>"mbox", "ttype"=>self::TEXT ],
            [ "input"=>"bb", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"bold", "output"=>"bb", "ttype"=>self::UNARY ],
            [ "input"=>"mathbf", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"bold", "output"=>"mathbf", "ttype"=>self::UNARY ],
            [ "input"=>"sf", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"sans-serif", "output"=>"sf", "ttype"=>self::UNARY ],
            [ "input"=>"mathsf", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"sans-serif", "output"=>"mathsf", "ttype"=>self::UNARY ],
            [ "input"=>"bbb", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"double-struck", "output"=>"bbb", "ttype"=>self::UNARY, "codes"=>self::BBB ],
            [ "input"=>"mathbb", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"double-struck", "output"=>"mathbb", "ttype"=>self::UNARY, "codes"=>self::BBB ],
            [ "input"=>"cc",  "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"script", "output"=>"cc", "ttype"=>self::UNARY, "codes"=>self::CAL ],
            [ "input"=>"mathcal", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"script", "output"=>"mathcal", "ttype"=>self::UNARY, "codes"=>self::CAL ],
            [ "input"=>"tt",  "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"monospace", "output"=>"tt", "ttype"=>self::UNARY ],
            [ "input"=>"mathtt", "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"monospace", "output"=>"mathtt", "ttype"=>self::UNARY ],
            [ "input"=>"fr",  "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"fraktur", "output"=>"fr", "ttype"=>self::UNARY, "codes"=>self::FRK ],
            [ "input"=>"mathfrak",  "tag"=>"mstyle", "atname"=>"mathvariant", "atval"=>"fraktur", "output"=>"mathfrak", "ttype"=>self::UNARY, "codes"=>self::FRK ],
        ];

        foreach ($this->symbols as $symbol) {
            if (isset($symbol["tex"])) {
                $this->symbols[] = [
                    "input"=>$symbol["tex"],
                    "tag"=>$symbol["tag"],
                    "output"=>$symbol["output"],
                    "ttype"=>$symbol["ttype"],
                    "acc"=>$symbol["acc"] ?? null,
                ];
            }
        }
        usort($this->symbols, function($s1, $s2) { return strcmp($s1["input"], $s2["input"]); });
        $this->names = array_column($this->symbols, "input");
        $this->dom = new DOMDocument("1.0", "utf-8");
    }

    private function createMmlNode($t, $frag = null) {
        $ns = "http://www.w3.org/1998/Math/MathML";
        $node = $t=="math" ? $this->dom->createElementNS($ns, $t) : $this->dom->createElement($t);
        if ($frag) @$node->appendChild($frag); // @ because $frag can be empty
        return $node;
    }

    private function removeCharsAndBlanks($str, $n) {
        // remove n characters and any following blanks
        if (strlen($str)>=$n+1 && $str[$n]=="\\" && $str[$n+1]!="\\" && $str[$n+1]!=" ") $st = substr($str, $n+1);
        else $st = substr($str, $n);
        for ($i=0; $i<strlen($st) && ord($st[$i])<=32; $i++);
        return substr($st, $i);
    }

    private function position($arr, $str, $n) {
        // return position >=n where str appears or would be inserted
        // assumes arr is sorted
        if ($n==0) {
            $n = -1;
            $h = count($arr);
            while ($n+1<$h) {
                $m = ($n+$h) >> 1;
                if (strcmp($arr[$m], $str)<0) $n = $m;
                else $h = $m;
            }
            return $h;
        } else {
            for ($i=$n; $i<count($arr) && strcmp($arr[$i], $str)<0; $i++);
        }
        return $i; // i=arr.length || arr[i]>=str
    }

    private function getSymbol($str) {
        // return maximal initial substring of str that appears in names
        // return null if there is none
        $k = 0; // new pos
        $j = 0; // old pos
        $match = "";
        $more = true;
        for ($i=1; $i<=strlen($str) && $more; $i++) {
            $st = substr($str, 0, $i); // initial substring of length i
            $j = $k;
            $k = $this->position($this->names, $st, $j);
            if ($k<count($this->names) && substr($str, 0, strlen($this->names[$k]))==$this->names[$k]) {
                $match = $this->names[$k];
                $mk = $k;
                $i = strlen($match);
            }
            $more = $k<count($this->names) && strcmp(substr($str, 0, strlen($this->names[$k])), $this->names[$k])>=0;
        }
        $this->previousSymbol = $this->currentSymbol;
        if ($match!="") {
            $this->currentSymbol = $this->symbols[$mk]["ttype"];
            return $this->symbols[$mk];
        }
        // if str[0] is a digit or - return maxsubstring of digits.digits
        $this->currentSymbol = self::CONST;
        if (preg_match('/^\d+(?:\.\d+)?(?:e[-+]?\d+)?/', $str, $matches)) { // rewritten, GS
            $st = str_replace([ ".", "-" ], [ $this->decimal, "\u{2212}" ], $matches[0]); // added, GS
            $tagst = "mn";
        } else {
            $st = substr($str, 0, 1); // take 1 character
            $tagst = !preg_match('/[A-Za-z]/', $st) ? "mo" : "mi";
        }
        if ($st=="-" && strlen($str)>1 && $str[1]!==' ' && $this->previousSymbol==self::INFIX) {
            $this->currentSymbol = self::INFIX; // trick "/" into recognizing "-" on second parse
            return [ "input"=>$st, "tag"=>$tagst, "output"=>$st, "ttype"=>self::UNARY, "func"=>true ];
        }
        return [ "input"=>$st, "tag"=>$tagst, "output"=>$st, "ttype"=>self::CONST ];
    }

    private function removeBrackets($node) {
        if (!$node->hasChildNodes()) return;
        if ($node->firstChild->hasChildNodes() && $node->nodeName=="mrow") {
            if ($node->firstChild->nextSibling && $node->firstChild->nextSibling->nodeName=="mtable") return;
            $st = $node->firstChild->firstChild->nodeValue;
            if ($st=="(" || $st=="[" || $st=="{") $node->removeChild($node->firstChild);
        }
        if ($node->lastChild->hasChildNodes() && ($node->nodeName=="mrow")) {
            $st = $node->lastChild->firstChild->nodeValue;
            if ($st==")" || $st=="]" || $st=="}") $node->removeChild($node->lastChild);
        }
    }

    /*Parsing ASCII math expressions with the following grammar
    v ::= [A-Za-z] | greek letters | numbers | other constant symbols
    u ::= sqrt | text | bb | other unary symbols for font commands
    b ::= frac | root | stackrel         binary symbols
    l ::= ( | [ | { | (: | {:            left brackets
    r ::= ) | ] | } | :) | :}            right brackets
    S ::= v | lEr | uS | bSS             Simple expression
    I ::= S_S | S^S | S_S^S | S          Intermediate expression
    E ::= IE | I/I                       Expression
    Each terminal symbol is translated into a corresponding mathml node.*/

    private function parseSexpr($str) { // parses $str and returns [node, tailstr]
        $newFrag = $this->dom->createDocumentFragment();
        $str = $this->removeCharsAndBlanks($str, 0);
        $symbol = $this->getSymbol($str); // either a token or a bracket or empty
        if ($symbol==null || $symbol["ttype"]==self::RIGHTBRACKET && $this->nestingDepth>0) {
            return [ null, $str ];
        }
        if ($symbol["ttype"]==self::DEFINITION) {
            $str = $symbol["output"].$this->removeCharsAndBlanks($str, strlen($symbol["input"]));
            $symbol = $this->getSymbol($str);
        }
        switch ($symbol["ttype"]) {
            case self::UNDEROVER:
            case self::CONST:
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                return [ $this->createMmlNode($symbol["tag"], // it's a constant
                    $this->dom->createTextNode($symbol["output"])), $str ];
            case self::LEFTBRACKET: // read (expr+)
                $this->nestingDepth++;
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                $result = $this->parseExpr($str, true);
                $this->nestingDepth--;
                if (isset($symbol["invisible"])) {
                    $node = $this->createMmlNode("mrow", $result[0]);
                } else {
                    $node = $this->createMmlNode("mo", $this->dom->createTextNode($symbol["output"]));
                    $node = $this->createMmlNode("mrow", $node);
                    $node->appendChild($result[0]);
                }
                return [ $node, $result[1] ];
            case self::TEXT:
                if ($symbol["input"]!="\"") $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                if (substr($str, 0, 1)=="{") $i=strpos($str, "}");
                elseif (substr($str, 0, 1)=="(") $i = strpos($str, ")");
                elseif (substr($str, 0, 1)=="[") $i = strpos($str, "]");
                elseif ($symbol["input"]=="\"") $i = strpos(substr($str, 1), "\"")+1;
                else $i = 0;
                if ($i==-1) $i = strlen($str);
                $st = substr($str, 1, $i-1);
                if (substr($st, 0, 1)==" ") {
                    $node = $this->createMmlNode("mspace");
                    $node->setAttribute("width", "1ex");
                    $newFrag->appendChild($node);
                }
                $newFrag->appendChild(
                $this->createMmlNode($symbol["tag"], $this->dom->createTextNode($st)));
                if (substr($st, -1)==" ") {
                    $node = $this->createMmlNode("mspace");
                    $node->setAttribute("width", "1ex");
                    $newFrag->appendChild($node);
                }
                $str = $this->removeCharsAndBlanks($str, $i+1);
                return [ $this->createMmlNode("mrow", $newFrag), $str ];
            case self::UNARYUNDEROVER:
            case self::UNARY:
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                $result = $this->parseSexpr($str);
                if ($result[0]==null) { 
                    if ($symbol["tag"]=="mi" || $symbol["tag"]=="mo") {
                        return [ $this->createMmlNode($symbol["tag"], $this->dom->createTextNode($symbol["output"])), $str ];
                    } else {
                        $result[0] = $this->createMmlNode("mi");
                    }
                }
                if (isset($symbol["func"])) {
                    $st = substr($str, 0, 1);
                    if ($st=="^" || $st=="_" || $st=="/" || $st=="|" || $st=="," || (strlen($symbol["input"])==1 && preg_match('/\w/', $symbol["input"]) && $st!="(")) {
                        return [ $this->createMmlNode($symbol["tag"], $this->dom->createTextNode($symbol["output"])), $str ];
                    } else {
                        $node = $this->createMmlNode("mrow", $this->createMmlNode($symbol["tag"], $this->dom->createTextNode($symbol["output"])));
                        $node->appendChild($result[0]);
                        return [ $node, $result[1] ];
                    }
                }
                $this->removeBrackets($result[0]);
                if ($symbol["input"]=="sqrt") { // sqrt
                    return [ $this->createMmlNode($symbol["tag"], $result[0]), $result[1] ];
                } elseif (isset($symbol["rewriteleftright"])) { // abs, floor, ceil
                    $node = $this->createMmlNode("mrow", $this->createMmlNode("mo", $this->dom->createTextNode($symbol["rewriteleftright"][0])));
                    $node->appendChild($result[0]);
                    $node->appendChild($this->createMmlNode("mo", $this->dom->createTextNode($symbol["rewriteleftright"][1])));
                    return [ $node, $result[1] ];
                } elseif ($symbol["input"]=="cancel") { // cancel
                    $node = $this->createMmlNode($symbol["tag"], $result[0]);
                    $node->setAttribute("notation", "updiagonalstrike");
                    return [ $node, $result[1] ];
                } elseif (isset($symbol["acc"])) { // accent
                    $node = $this->createMmlNode($symbol["tag"], $result[0]);
                    $accnode = $this->createMmlNode("mo", $this->dom->createTextNode($symbol["output"]));
                    if ($symbol["input"]=="vec" && (
                        ($result[0]->nodeName=="mrow" &&
                            count($result[0]->childNodes)==1 &&
                            $result[0]->firstChild->nodeValue!=null &&
                            strlen($result[0]->firstChild->nodeValue)==1) ||
                        ($result[0]->nodeValue!=null &&
                            strlen($result[0]->nodeValue)==1))) { // fixed, GS
                        $accnode->setAttribute("stretchy", "false");
                    }
                    $node->appendChild($accnode);
                    return [ $node, $result[1] ];
                } else { // font change command
                    if (isset($symbol["codes"])) {
                        for ($i=0; $i<count($result[0]->childNodes); $i++) {
                            if ($result[0]->childNodes[$i]->nodeName=="mi" || $result[0]->nodeName=="mi") {
                                $st = $result[0]->nodeName=="mi" ? $result[0]->firstChild->nodeValue : $result[0]->childNodes[$i]->firstChild->nodeValue;
                                $newst = ""; // fixed, GS
                                for ($j=0; $j<strlen($st); $j++)
                                    $ord = ord($st[$j]);
                                    if ($ord>64 && $ord<91)
                                        $newst = $newst.$symbol["codes"][$ord-65];
                                    elseif ($ord>96 && $ord<123)
                                        $newst = $newst.$symbol["codes"][$ord-71];
                                    else $newst = $newst.$st[$j];
                                    if ($result[0]->nodeName=="mi")
                                        $result[0] = $this->createMmlNode("mo", $this->dom->createTextNode($newst)); 
                                    else 
                                        $result[0]->replaceChild($this->createMmlNode("mo", $this->dom->createTextNode($newst)), $result[0]->childNodes[$i]); // fixed, GS
                            }
                        }
                    }
                    $node = $this->createMmlNode($symbol["tag"], $result[0]);
                    if (!isset($symbol["codes"])) $node->setAttribute($symbol["atname"], $symbol["atval"]); // fixed, GS
                    return [ $node, $result[1] ];
                }
            case self::BINARY:
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                $result = $this->parseSexpr($str);
                if ($result[0]==null)
                    return [ $this->createMmlNode("mo", $this->dom->createTextNode($symbol["input"])), $str ];
                $this->removeBrackets($result[0]);
                $result2 = $this->parseSexpr($result[1]);
                if ($result2[0]==null)
                    return [ $this->createMmlNode("mo", $this->dom->createTextNode($symbol["input"])), $str ];
                $this->removeBrackets($result2[0]);
                if (in_array($symbol["input"], [ "color", "class", "id" ])) {
                    // Get the second argument
                    if (substr($str, 0, 1)=="{") $i = strpos($str, "}");
                    elseif (substr($str, 0, 1)=="(") $i = strpos($str, ")");
                    elseif (substr($str, 0, 1)=="[") $i = strpos($str, "]");
                    $st = substr($str, 1, $i-1);
                    // Make a mathml $node
                    $node = $this->createMmlNode($symbol["tag"], $result2[0]);
                    // Set the correct attribute
                    if ($symbol["input"]=="color") $node->setAttribute("mathcolor", $st);
                    elseif ($symbol["input"]=="class") $node->setAttribute("class", $st);
                    elseif ($symbol["input"]=="id") $node->setAttribute("id", $st);
                    return [ $node, $result2[1] ];
                }
                if ($symbol["input"]=="root" || $symbol["output"]=="stackrel") $newFrag->appendChild($result2[0]);
                $newFrag->appendChild($result[0]);
                if ($symbol["input"]=="frac") $newFrag->appendChild($result2[0]);
                return [ $this->createMmlNode($symbol["tag"], $newFrag), $result2[1] ];
            case self::INFIX:
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                return [ $this->createMmlNode("mo", $this->dom->createTextNode($symbol["output"])), $str ];
            case self::SPACE:
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                $node = $this->createMmlNode("mspace");
                $node->setAttribute("width", "1ex");
                $newFrag->appendChild($node);
                $newFrag->appendChild($this->createMmlNode($symbol["tag"], $this->dom->createTextNode($symbol["output"])));
                $node = $this->createMmlNode("mspace");
                $node->setAttribute("width", "1ex");
                $newFrag->appendChild($node);
                return [ $this->createMmlNode("mrow", $newFrag), $str ];
            case self::LEFTRIGHT:
                $this->nestingDepth++;
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                $result = $this->parseExpr($str, false);
                $this->nestingDepth--;
                $st = "";
                if ($result[0]->lastChild!=null) $st = $result[0]->lastChild->firstChild->nodeValue;
                if ($st=="|" && $str[0]!==",") { // it's an absolute value subterm
                    $node = $this->createMmlNode("mo", $this->dom->createTextNode($symbol["output"]));
                    $node = $this->createMmlNode("mrow", $node);
                    $node->appendChild($result[0]);
                    return [ $node, $result[1] ];
                } else { // the "|" is a \mid so use unicode 2223 (divides) for spacing
                    $node = $this->createMmlNode("mo", $this->dom->createTextNode("\u{2223}"));
                    $node = $this->createMmlNode("mrow", $node);
                    return [ $node, $str ];
                }
            default:
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                return [ $this->createMmlNode($symbol["tag"], // it's a constant
                    $this->dom->createTextNode($symbol["output"])),
                    $str
                ];
        }
    }

    private function parseIexpr($str) {
        $str = $this->removeCharsAndBlanks($str, 0);
        $sym1 = $this->getSymbol($str);
        $result = $this->parseSexpr($str);
        $node = $result[0];
        $str = $result[1];
        $symbol = $this->getSymbol($str);
        if ($symbol["ttype"]==self::INFIX && $symbol["input"]!="/") {
            $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
            $result = $this->parseSexpr($str);
            if ($result[0]==null) // show box in place of missing argument
                $result[0] = $this->createMmlNode("mo", $this->dom->createTextNode("\u{25A1}"));
            else $this->removeBrackets($result[0]);
            $str = $result[1];
            $underover = $sym1["ttype"]==self::UNDEROVER || $sym1["ttype"]==self::UNARYUNDEROVER;
            if ($symbol["input"]=="_") {
                $sym2 = $this->getSymbol($str);
                if ($sym2["input"]=="^") {
                    $str = $this->removeCharsAndBlanks($str, strlen($sym2["input"]));
                    $res2 = $this->parseSexpr($str);
                    $this->removeBrackets($res2[0]);
                    $str = $res2[1];
                    $node = $this->createMmlNode($underover ? "munderover" : "msubsup", $node);
                    $node->appendChild($result[0]);
                    $node->appendChild($res2[0]);
                    $node = $this->createMmlNode("mrow", $node); // so sum does not stretch
                } else {
                    $node = $this->createMmlNode(($underover ? "munder" : "msub"), $node);
                    $node->appendChild($result[0]);
                }
            } elseif ($symbol["input"]=="^" && $underover) {
                $node = $this->createMmlNode("mover", $node);
                $node->appendChild($result[0]);
            } else {
                $node = $this->createMmlNode($symbol["tag"], $node);
                $node->appendChild($result[0]);
            }
            if (isset($sym1["func"])) {
                $sym2 = $this->getSymbol($str);
                if ($sym2["ttype"]!=self::INFIX && $sym2["ttype"]!=self::RIGHTBRACKET &&
                    (strlen($sym1["input"])>1 || $sym2["ttype"]==self::LEFTBRACKET)) {
                    $result = $this->parseIexpr($str);
                    $node = $this->createMmlNode("mrow", $node);
                    $node->appendChild($result[0]);
                    $str = $result[1];
                }
            }
        }
        return [ $node, $str ];
    }

    private function parseExpr($str, $rightbracket) {
        $newFrag = $this->dom->createDocumentFragment();
        do {
            $str = $this->removeCharsAndBlanks($str, 0);
            $result = $this->parseIexpr($str);
            $node = $result[0];
            $str = $result[1];
            $symbol = $this->getSymbol($str);
            if ($symbol["ttype"]==self::INFIX && $symbol["input"]=="/") {
                $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
                $result = $this->parseIexpr($str);
                if ($result[0]==null) // show box in place of missing argument
                    $result[0] = $this->createMmlNode("mo", $this->dom->createTextNode("\u{25A1}"));
                else $this->removeBrackets($result[0]);
                $str = $result[1];
                $this->removeBrackets($node);
                $node = $this->createMmlNode($symbol["tag"], $node);
                $node->appendChild($result[0]);
                $newFrag->appendChild($node);
                $symbol = $this->getSymbol($str);
            } elseif (isset($node)) $newFrag->appendChild($node);
        } while (($symbol["ttype"]!=self::RIGHTBRACKET &&
             ($symbol["ttype"]!=self::LEFTRIGHT || $rightbracket) || $this->nestingDepth==0) &&
             $symbol!=null && $symbol["output"]!="");
        if ($symbol["ttype"]==self::RIGHTBRACKET || $symbol["ttype"]==self::LEFTRIGHT) {
            $len = count($newFrag->childNodes);
            if ($len>0 && $newFrag->childNodes[$len-1]->nodeName=="mrow"
                && $newFrag->childNodes[$len-1]->lastChild
                && $newFrag->childNodes[$len-1]->lastChild->firstChild ) { // matrix
                $right = $newFrag->childNodes[$len-1]->lastChild->firstChild->nodeValue;
                if ($right==")" || $right=="]") {
                    $left = $newFrag->childNodes[$len-1]->firstChild->firstChild->nodeValue;
                    if ($left=="(" && $right==")" && $symbol["output"]!="}" || $left=="[" && $right=="]") {
                        $pos = []; // positions of commas
                        $matrix = true;
                        $m = count($newFrag->childNodes);
                        for ($i=0; $matrix && $i<$m; $i=$i+2) {
                            $pos[$i] = [];
                            $node = $newFrag->childNodes[$i];
                            if ($matrix)
                                $matrix = $node->nodeName=="mrow" &&
                                    ($i==$m-1 || $node->nextSibling->nodeName=="mo" &&
                                    $node->nextSibling->firstChild->nodeValue==",") &&
                                    $node->firstChild->firstChild &&
                                    $node->firstChild->firstChild->nodeValue==$left &&
                                    $node->lastChild->firstChild &&
                                    $node->lastChild->firstChild->nodeValue==$right;
                            if ($matrix)
                                for ($j=0; $j<count($node->childNodes); $j++)
                                    if ($node->childNodes[$j]->firstChild->nodeValue==",")
                                        $pos[$i][] = $j;
                            if ($matrix && $i>1) $matrix = count($pos[$i])==count($pos[$i-2]);
                        }
                        $matrix = $matrix && (count($pos)>1 || count($pos[0])>0);
                        $columnlines = [];
                        if ($matrix) {
                            $table = $this->dom->createDocumentFragment();
                            for ($i=0; $i<$m; $i=$i+2) {
                                $row = $this->dom->createDocumentFragment();
                                $frag = $this->dom->createDocumentFragment();
                                $node = $newFrag->firstChild; // <mrow>(-,-,...,-,-)</mrow>
                                $n = count($node->childNodes);
                                $k = 0;
                                $node->removeChild($node->firstChild); // remove (
                                for ($j=1; $j<$n-1; $j++) {
                                    if (isset($pos[$i][$k]) && $j==$pos[$i][$k]) {
                                        $node->removeChild($node->firstChild); // remove ,
                                        if ($node->firstChild->nodeName=="mrow" &&
                                            count($node->firstChild->childNodes)==1 &&
                                            $node->firstChild->firstChild->firstChild->nodeValue=="\u{2223}") {
                                            // is columnline marker - skip it
                                            if ($i==0) { $columnlines[] = "solid"; }
                                            $node->removeChild($node->firstChild); // remove mrow
                                            $node->removeChild($node->firstChild); // remove ,
                                            $j += 2;
                                            $k++;
                                        } elseif ($i==0) {
                                            $columnlines[] = "none";
                                        }
                                        $row->appendChild($this->createMmlNode("mtd", $frag));
                                        $k++;
                                    } else
                                        $frag->appendChild($node->firstChild);
                                }
                                $row->appendChild($this->createMmlNode("mtd", $frag));
                                if ($i==0) { $columnlines[] = "none"; }
                                if (count($newFrag->childNodes)>2) {
                                    $newFrag->removeChild($newFrag->firstChild); // remove <mrow>)</mrow>
                                    $newFrag->removeChild($newFrag->firstChild); // remove <mo>,</mo>
                                }
                                $table->appendChild($this->createMmlNode("mtr", $row));
                            }
                            $node = $this->createMmlNode("mtable", $table);
                            $node->setAttribute("columnlines", implode(" ", $columnlines));
                            if (isset($symbol["invisible"])) $node->setAttribute("columnalign", "left");
                            $newFrag->replaceChild($node, $newFrag->firstChild);
                        }
                    }
                }
            }
            $str = $this->removeCharsAndBlanks($str, strlen($symbol["input"]));
            if (!isset($symbol["invisible"])) {
                $node = $this->createMmlNode("mo", $this->dom->createTextNode($symbol["output"]));
                $newFrag->appendChild($node);
            }
        }
        return [ $newFrag, $str ];
    }

    public function parseMath($str, $isDisplay = true) {
        $this->nestingDepth = 0;
        $frag = $this->parseExpr(ltrim($str), false)[0];
        //return $this->dom->saveXML($frag, LIBXML_NOEMPTYTAG); // DEBUG
        if ($this->isAnnotated) {
            if (count($frag->childNodes)!=1) $frag = $this->createMmlNode("mrow", $frag);
            $frag = $this->createMmlNode("semantics", $frag);
            $annotation = $this->createMmlNode("annotation", $this->dom->createTextNode(trim($str)));
            $annotation->setAttribute("encoding", "text/x-asciimath");
            $frag->appendChild($annotation);
        }
        $node = $this->createMmlNode("math", $frag);
        $node->setAttribute("display", $isDisplay ? "block" : "inline");
        return $this->dom->saveXML($node, LIBXML_NOEMPTYTAG);
    }
}
