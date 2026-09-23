<?php
/*
 * Copyright 2016, Google Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *     * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/* Jison generated parser */
namespace Google\GAX\Jison;
use Exception;




class Parser
{
    public $symbols = array();
    public $terminals = array();
    public $productions = array();
    public $table = array();
    public $defaultActions = array();
    public $version = '0.3.12';
    public $debug = false;
    public $none = 0;
    public $shift = 1;
    public $reduce = 2;
    public $accept = 3;

    function trace()
    {

    }

    function __construct()
    {
        //Setup Parser
        
			$symbol0 = new ParserSymbol("accept", 0);
			$symbol1 = new ParserSymbol("end", 1);
			$symbol2 = new ParserSymbol("error", 2);
			$symbol3 = new ParserSymbol("template", 3);
			$symbol4 = new ParserSymbol("FORWARD_SLASH", 4);
			$symbol5 = new ParserSymbol("bound_segments", 5);
			$symbol6 = new ParserSymbol("bound_segment", 6);
			$symbol7 = new ParserSymbol("unbound_segments", 7);
			$symbol8 = new ParserSymbol("unbound_terminal", 8);
			$symbol9 = new ParserSymbol("bound_terminal", 9);
			$symbol10 = new ParserSymbol("variable", 10);
			$symbol11 = new ParserSymbol("WILDCARD", 11);
			$symbol12 = new ParserSymbol("PATH_WILDCARD", 12);
			$symbol13 = new ParserSymbol("LITERAL", 13);
			$symbol14 = new ParserSymbol("LEFT_BRACE", 14);
			$symbol15 = new ParserSymbol("EQUALS", 15);
			$symbol16 = new ParserSymbol("RIGHT_BRACE", 16);
			$this->symbols[0] = $symbol0;
			$this->symbols["accept"] = $symbol0;
			$this->symbols[1] = $symbol1;
			$this->symbols["end"] = $symbol1;
			$this->symbols[2] = $symbol2;
			$this->symbols["error"] = $symbol2;
			$this->symbols[3] = $symbol3;
			$this->symbols["template"] = $symbol3;
			$this->symbols[4] = $symbol4;
			$this->symbols["FORWARD_SLASH"] = $symbol4;
			$this->symbols[5] = $symbol5;
			$this->symbols["bound_segments"] = $symbol5;
			$this->symbols[6] = $symbol6;
			$this->symbols["bound_segment"] = $symbol6;
			$this->symbols[7] = $symbol7;
			$this->symbols["unbound_segments"] = $symbol7;
			$this->symbols[8] = $symbol8;
			$this->symbols["unbound_terminal"] = $symbol8;
			$this->symbols[9] = $symbol9;
			$this->symbols["bound_terminal"] = $symbol9;
			$this->symbols[10] = $symbol10;
			$this->symbols["variable"] = $symbol10;
			$this->symbols[11] = $symbol11;
			$this->symbols["WILDCARD"] = $symbol11;
			$this->symbols[12] = $symbol12;
			$this->symbols["PATH_WILDCARD"] = $symbol12;
			$this->symbols[13] = $symbol13;
			$this->symbols["LITERAL"] = $symbol13;
			$this->symbols[14] = $symbol14;
			$this->symbols["LEFT_BRACE"] = $symbol14;
			$this->symbols[15] = $symbol15;
			$this->symbols["EQUALS"] = $symbol15;
			$this->symbols[16] = $symbol16;
			$this->symbols["RIGHT_BRACE"] = $symbol16;

			$this->terminals = array(
					2=>&$symbol2,
					4=>&$symbol4,
					11=>&$symbol11,
					12=>&$symbol12,
					13=>&$symbol13,
					14=>&$symbol14,
					15=>&$symbol15,
					16=>&$symbol16
				);

			$table0 = new ParserState(0);
			$table1 = new ParserState(1);
			$table2 = new ParserState(2);
			$table3 = new ParserState(3);
			$table4 = new ParserState(4);
			$table5 = new ParserState(5);
			$table6 = new ParserState(6);
			$table7 = new ParserState(7);
			$table8 = new ParserState(8);
			$table9 = new ParserState(9);
			$table10 = new ParserState(10);
			$table11 = new ParserState(11);
			$table12 = new ParserState(12);
			$table13 = new ParserState(13);
			$table14 = new ParserState(14);
			$table15 = new ParserState(15);
			$table16 = new ParserState(16);
			$table17 = new ParserState(17);
			$table18 = new ParserState(18);
			$table19 = new ParserState(19);
			$table20 = new ParserState(20);
			$table21 = new ParserState(21);
			$table22 = new ParserState(22);

			$tableDefinition0 = array(
				
					3=>new ParserAction($this->none, $table1),
					4=>new ParserAction($this->shift, $table2),
					5=>new ParserAction($this->none, $table3),
					6=>new ParserAction($this->none, $table4),
					8=>new ParserAction($this->none, $table7),
					9=>new ParserAction($this->none, $table5),
					10=>new ParserAction($this->none, $table6),
					11=>new ParserAction($this->shift, $table9),
					12=>new ParserAction($this->shift, $table10),
					13=>new ParserAction($this->shift, $table11),
					14=>new ParserAction($this->shift, $table8)
				);

			$tableDefinition1 = array(
				
					1=>new ParserAction($this->accept)
				);

			$tableDefinition2 = array(
				
					5=>new ParserAction($this->none, $table12),
					6=>new ParserAction($this->none, $table4),
					8=>new ParserAction($this->none, $table7),
					9=>new ParserAction($this->none, $table5),
					10=>new ParserAction($this->none, $table6),
					11=>new ParserAction($this->shift, $table9),
					12=>new ParserAction($this->shift, $table10),
					13=>new ParserAction($this->shift, $table11),
					14=>new ParserAction($this->shift, $table8)
				);

			$tableDefinition3 = array(
				
					1=>new ParserAction($this->reduce, $table2)
				);

			$tableDefinition4 = array(
				
					1=>new ParserAction($this->reduce, $table4),
					4=>new ParserAction($this->shift, $table13)
				);

			$tableDefinition5 = array(
				
					1=>new ParserAction($this->reduce, $table7),
					4=>new ParserAction($this->reduce, $table7)
				);

			$tableDefinition6 = array(
				
					1=>new ParserAction($this->reduce, $table8),
					4=>new ParserAction($this->reduce, $table8)
				);

			$tableDefinition7 = array(
				
					1=>new ParserAction($this->reduce, $table12),
					4=>new ParserAction($this->reduce, $table12)
				);

			$tableDefinition8 = array(
				
					13=>new ParserAction($this->shift, $table14)
				);

			$tableDefinition9 = array(
				
					1=>new ParserAction($this->reduce, $table9),
					4=>new ParserAction($this->reduce, $table9),
					16=>new ParserAction($this->reduce, $table9)
				);

			$tableDefinition10 = array(
				
					1=>new ParserAction($this->reduce, $table10),
					4=>new ParserAction($this->reduce, $table10),
					16=>new ParserAction($this->reduce, $table10)
				);

			$tableDefinition11 = array(
				
					1=>new ParserAction($this->reduce, $table11),
					4=>new ParserAction($this->reduce, $table11),
					16=>new ParserAction($this->reduce, $table11)
				);

			$tableDefinition12 = array(
				
					1=>new ParserAction($this->reduce, $table1)
				);

			$tableDefinition13 = array(
				
					5=>new ParserAction($this->none, $table15),
					6=>new ParserAction($this->none, $table4),
					8=>new ParserAction($this->none, $table7),
					9=>new ParserAction($this->none, $table5),
					10=>new ParserAction($this->none, $table6),
					11=>new ParserAction($this->shift, $table9),
					12=>new ParserAction($this->shift, $table10),
					13=>new ParserAction($this->shift, $table11),
					14=>new ParserAction($this->shift, $table8)
				);

			$tableDefinition14 = array(
				
					15=>new ParserAction($this->shift, $table16),
					16=>new ParserAction($this->shift, $table17)
				);

			$tableDefinition15 = array(
				
					1=>new ParserAction($this->reduce, $table3)
				);

			$tableDefinition16 = array(
				
					7=>new ParserAction($this->none, $table18),
					8=>new ParserAction($this->none, $table19),
					11=>new ParserAction($this->shift, $table9),
					12=>new ParserAction($this->shift, $table10),
					13=>new ParserAction($this->shift, $table11)
				);

			$tableDefinition17 = array(
				
					1=>new ParserAction($this->reduce, $table14),
					4=>new ParserAction($this->reduce, $table14)
				);

			$tableDefinition18 = array(
				
					16=>new ParserAction($this->shift, $table20)
				);

			$tableDefinition19 = array(
				
					4=>new ParserAction($this->shift, $table21),
					16=>new ParserAction($this->reduce, $table6)
				);

			$tableDefinition20 = array(
				
					1=>new ParserAction($this->reduce, $table13),
					4=>new ParserAction($this->reduce, $table13)
				);

			$tableDefinition21 = array(
				
					7=>new ParserAction($this->none, $table22),
					8=>new ParserAction($this->none, $table19),
					11=>new ParserAction($this->shift, $table9),
					12=>new ParserAction($this->shift, $table10),
					13=>new ParserAction($this->shift, $table11)
				);

			$tableDefinition22 = array(
				
					16=>new ParserAction($this->reduce, $table5)
				);

			$table0->setActions($tableDefinition0);
			$table1->setActions($tableDefinition1);
			$table2->setActions($tableDefinition2);
			$table3->setActions($tableDefinition3);
			$table4->setActions($tableDefinition4);
			$table5->setActions($tableDefinition5);
			$table6->setActions($tableDefinition6);
			$table7->setActions($tableDefinition7);
			$table8->setActions($tableDefinition8);
			$table9->setActions($tableDefinition9);
			$table10->setActions($tableDefinition10);
			$table11->setActions($tableDefinition11);
			$table12->setActions($tableDefinition12);
			$table13->setActions($tableDefinition13);
			$table14->setActions($tableDefinition14);
			$table15->setActions($tableDefinition15);
			$table16->setActions($tableDefinition16);
			$table17->setActions($tableDefinition17);
			$table18->setActions($tableDefinition18);
			$table19->setActions($tableDefinition19);
			$table20->setActions($tableDefinition20);
			$table21->setActions($tableDefinition21);
			$table22->setActions($tableDefinition22);

			$this->table = array(
				
					0=>$table0,
					1=>$table1,
					2=>$table2,
					3=>$table3,
					4=>$table4,
					5=>$table5,
					6=>$table6,
					7=>$table7,
					8=>$table8,
					9=>$table9,
					10=>$table10,
					11=>$table11,
					12=>$table12,
					13=>$table13,
					14=>$table14,
					15=>$table15,
					16=>$table16,
					17=>$table17,
					18=>$table18,
					19=>$table19,
					20=>$table20,
					21=>$table21,
					22=>$table22
				);

			$this->defaultActions = array(
				
					3=>new ParserAction($this->reduce, $table2),
					12=>new ParserAction($this->reduce, $table1),
					15=>new ParserAction($this->reduce, $table3),
					22=>new ParserAction($this->reduce, $table5)
				);

			$this->productions = array(
				
					0=>new ParserProduction($symbol0),
					1=>new ParserProduction($symbol3,2),
					2=>new ParserProduction($symbol3,1),
					3=>new ParserProduction($symbol5,3),
					4=>new ParserProduction($symbol5,1),
					5=>new ParserProduction($symbol7,3),
					6=>new ParserProduction($symbol7,1),
					7=>new ParserProduction($symbol6,1),
					8=>new ParserProduction($symbol6,1),
					9=>new ParserProduction($symbol8,1),
					10=>new ParserProduction($symbol8,1),
					11=>new ParserProduction($symbol8,1),
					12=>new ParserProduction($symbol9,1),
					13=>new ParserProduction($symbol10,5),
					14=>new ParserProduction($symbol10,3)
				);




        //Setup Lexer
        
			$this->rules = array(
				
					0=>"/^(?:\s+)/",
					1=>"/^(?:\/)/",
					2=>"/^(?:\{)/",
					3=>"/^(?:\})/",
					4=>"/^(?:=)/",
					5=>"/^(?:\*\*)/",
					6=>"/^(?:\*)/",
					7=>"/^(?:[^*=}{}\/]+)/",
					8=>"/^(?:$)/"
				);

			$this->conditions = array(
				
					"INITIAL"=>new LexerConditions(array( 0,1,2,3,4,5,6,7,8), true)
				);


    }

    function parserPerformAction(&$thisS, &$yy, $yystate, &$s, $o)
    {
        
/* this == yyval */


switch ($yystate) {
case 1: case 2:
    return $s[$o]->text;

break;
case 3: case 5:

    $t1 = $s[$o-2]->text;
    $t3 = $s[$o]->text;
    $thisS = array_merge($t1, $t3);

break;
case 4: case 6: case 7: case 8:
    $thisS = $s[$o]->text;

break;
case 9: case 10: case 11:

    $t1 = $s[$o]->text;
    Segment::incrementSegmentCount();
    $thisS = [new Segment(Segment::TERMINAL, $t1)];

break;
case 12:

    $t1 = $s[$o]->text;
    if (in_array($t1[0]['literal'], ['*', '**'])) {
        $thisS = [
            new Segment(Segment::BINDING,
                sprintf('$%d', Segment::getBindingCount())),
            $t1[0],
            new Segment(Segment::END_BINDING, '')
        ];
        Segment::incrementBindingCount();
    } else {
        $thisS = $t1;
    }

break;
case 13:

    $t2 = $s[$o-3]->text;
    $t4 = $s[$o-1]->text;
    $tmp = [new Segment(Segment::BINDING, $t2)];
    $tmp = array_merge($tmp, $t4);
    array_push($tmp, new Segment(Segment::END_BINDING, ''));
    $thisS = $tmp;

break;
case 14:

    $t2 = $s[$o-1]->text;
    $thisS = [
        new Segment(Segment::BINDING, $t2),
        new Segment(Segment::TERMINAL, '*'),
        new Segment(Segment::END_BINDING, '')
    ];
    Segment::incrementSegmentCount();

break;
}

    }

    function parserLex()
    {
        $token = $this->lexerLex(); // $end = 1

        if (isset($token)) {
            return $token;
        }

        return $this->symbols["end"];
    }

    function parseError($str = "", ParserError $hash = null)
    {
        throw new Exception($str);
    }

    function lexerError($str = "", LexerError $hash = null)
    {
        throw new Exception($str);
    }

    function parse($input)
    {
        if (empty($this->table)) {
            throw new Exception("Empty Table");
        }
        $this->eof = new ParserSymbol("Eof", 1);
        $firstAction = new ParserAction(0, $this->table[0]);
        $firstCachedAction = new ParserCachedAction($firstAction);
        $stack = array($firstCachedAction);
        $stackCount = 1;
        $vstack = array(null);
        $vstackCount = 1;
        $yy = null;
        $_yy = null;
        $recovering = 0;
        $symbol = null;
        $action = null;
        $errStr = "";
        $preErrorSymbol = null;
        $state = null;

        $this->setInput($input);

        while (true) {
            // retrieve state number from top of stack
            $state = $stack[$stackCount - 1]->action->state;
            // use default actions if available
            if ($state != null && isset($this->defaultActions[$state->index])) {
                $action = $this->defaultActions[$state->index];
            } else {
                if (empty($symbol) == true) {
                    $symbol = $this->parserLex();
                }
                // read action for current state and first input
                if (isset($state) && isset($state->actions[$symbol->index])) {
                    //$action = $this->table[$state][$symbol];
                    $action = $state->actions[$symbol->index];
                } else {
                    $action = null;
                }
            }

            if ($action == null) {
                if ($recovering == 0) {
                    // Report error
                    $expected = array();
                    foreach($this->table[$state->index]->actions as $p => $item) {
                        if (!empty($this->terminals[$p]) && $p > 2) {
                            $expected[] = $this->terminals[$p]->name;
                        }
                    }

                    $errStr = "Parse error on line " . ($this->yy->lineNo + 1) . ":\n" . $this->showPosition() . "\nExpecting " . implode(", ", $expected) . ", got '" . (isset($this->terminals[$symbol->index]) ? $this->terminals[$symbol->index]->name : 'NOTHING') . "'";

                    $this->parseError($errStr, new ParserError($this->match, $state, $symbol, $this->yy->lineNo, $this->yy->loc, $expected));
                }
            }

            if ($state === null || $action === null) {
                break;
            }

            switch ($action->action) {
                case 1:
                    // shift
                    //$this->shiftCount++;
                    $stack[] = new ParserCachedAction($action, $symbol);
                    $stackCount++;

                    $vstack[] = clone($this->yy);
                    $vstackCount++;

                    $symbol = "";
                    if ($preErrorSymbol == null) { // normal execution/no error
                        $yy = clone($this->yy);
                        if ($recovering > 0) $recovering--;
                    } else { // error just occurred, resume old look ahead f/ before error
                        $symbol = $preErrorSymbol;
                        $preErrorSymbol = null;
                    }
                    break;

                case 2:
                    // reduce
                    $len = $this->productions[$action->state->index]->len;
                    // perform semantic action
                    $_yy = $vstack[$vstackCount - $len];// default to $S = $1
                    // default location, uses first token for firsts, last for lasts

                    if (isset($this->ranges)) {
                        //TODO: add ranges
                    }

                    $r = $this->parserPerformAction($_yy->text, $yy, $action->state->index, $vstack, $vstackCount - 1);

                    if (isset($r)) {
                        return $r;
                    }

                    // pop off stack
                    while ($len > 0) {
                        $len--;

                        array_pop($stack);
                        $stackCount--;

                        array_pop($vstack);
                        $vstackCount--;
                    }

                    if (is_null($_yy))
                    {
                        $vstack[] = new ParserValue();
                    }
                    else
                    {
                        $vstack[] = $_yy;
                    }
                    $vstackCount++;

                    $nextSymbol = $this->productions[$action->state->index]->symbol;
                    // goto new state = table[STATE][NONTERMINAL]
                    $nextState = $stack[$stackCount - 1]->action->state;
                    $nextAction = $nextState->actions[$nextSymbol->index];

                    $stack[] = new ParserCachedAction($nextAction, $nextSymbol);
                    $stackCount++;

                    break;

                case 3:
                    // accept
                    return true;
            }

        }

        return true;
    }


    /* Jison generated lexer */
    public $eof;
    public $yy = null;
    public $match = "";
    public $matched = "";
    public $conditionStack = array();
    public $conditionStackCount = 0;
    public $rules = array();
    public $conditions = array();
    public $done = false;
    public $less;
    public $more;
    public $input;
    public $offset;
    public $ranges;
    public $flex = false;

    function setInput($input)
    {
        $this->input = $input;
        $this->more = $this->less = $this->done = false;
        $this->yy = new ParserValue();
        $this->conditionStack = array('INITIAL');
        $this->conditionStackCount = 1;

        if (isset($this->ranges)) {
            $loc = $this->yy->loc = new ParserLocation();
            $loc->Range(new ParserRange(0, 0));
        } else {
            $this->yy->loc = new ParserLocation();
        }
        $this->offset = 0;
    }

    function input()
    {
        $ch = $this->input[0];
        $this->yy->text .= $ch;
        $this->yy->leng++;
        $this->offset++;
        $this->match .= $ch;
        $this->matched .= $ch;
        $lines = preg_match("/(?:\r\n?|\n).*/", $ch);
        if (count($lines) > 0) {
            $this->yy->lineNo++;
            $this->yy->lastLine++;
        } else {
            $this->yy->loc->lastColumn++;
        }
        if (isset($this->ranges)) {
            $this->yy->loc->range->y++;
        }

        $this->input = array_slice($this->input, 1);
        return $ch;
    }

    function unput($ch)
    {
        $len = strlen($ch);
        $lines = explode("/(?:\r\n?|\n)/", $ch);
        $linesCount = count($lines);

        $this->input = $ch . $this->input;
        $this->yy->text = substr($this->yy->text, 0, $len - 1);
        //$this->yylen -= $len;
        $this->offset -= $len;
        $oldLines = explode("/(?:\r\n?|\n)/", $this->match);
        $oldLinesCount = count($oldLines);
        $this->match = substr($this->match, 0, strlen($this->match) - 1);
        $this->matched = substr($this->matched, 0, strlen($this->matched) - 1);

        if (($linesCount - 1) > 0) $this->yy->lineNo -= $linesCount - 1;
        $r = $this->yy->loc->range;
        $oldLinesLength = (isset($oldLines[$oldLinesCount - $linesCount]) ? strlen($oldLines[$oldLinesCount - $linesCount]) : 0);

        $this->yy->loc = new ParserLocation(
            $this->yy->loc->firstLine,
            $this->yy->lineNo,
            $this->yy->loc->firstColumn,
            $this->yy->loc->firstLine,
            (empty($lines) ?
                ($linesCount == $oldLinesCount ? $this->yy->loc->firstColumn : 0) + $oldLinesLength :
                $this->yy->loc->firstColumn - $len)
        );

        if (isset($this->ranges)) {
            $this->yy->loc->range = array($r[0], $r[0] + $this->yy->leng - $len);
        }
    }

    function more()
    {
        $this->more = true;
    }

    function pastInput()
    {
        $past = substr($this->matched, 0, strlen($this->matched) - strlen($this->match));
        return (strlen($past) > 20 ? '...' : '') . preg_replace("/\n/", "", substr($past, -20));
    }

    function upcomingInput()
    {
        $next = $this->match;
        if (strlen($next) < 20) {
            $next .= substr($this->input, 0, 20 - strlen($next));
        }
        return preg_replace("/\n/", "", substr($next, 0, 20) . (strlen($next) > 20 ? '...' : ''));
    }

    function showPosition()
    {
        $pre = $this->pastInput();

        $c = '';
        for($i = 0, $preLength = strlen($pre); $i < $preLength; $i++) {
            $c .= '-';
        }

        return $pre . $this->upcomingInput() . "\n" . $c . "^";
    }

    function next()
    {
        if ($this->done == true) {
            return $this->eof;
        }

        if (empty($this->input)) {
            $this->done = true;
        }

        if ($this->more == false) {
            $this->yy->text = '';
            $this->match = '';
        }

        $rules = $this->currentRules();
        for ($i = 0, $j = count($rules); $i < $j; $i++) {
            preg_match($this->rules[$rules[$i]], $this->input, $tempMatch);
            if ($tempMatch && (empty($match) || count($tempMatch[0]) > count($match[0]))) {
                $match = $tempMatch;
                $index = $i;
                if (isset($this->flex) && $this->flex == false) {
                    break;
                }
            }
        }
        if ( $match ) {
            $matchCount = strlen($match[0]);
            $lineCount = preg_match("/(?:\r\n?|\n).*/", $match[0], $lines);
            $line = ($lines ? $lines[$lineCount - 1] : false);
            $this->yy->lineNo += $lineCount;

            $this->yy->loc = new ParserLocation(
                $this->yy->loc->lastLine,
                $this->yy->lineNo + 1,
                $this->yy->loc->lastColumn,
                ($line ?
                    count($line) - preg_match("/\r?\n?/", $line, $na) :
                    $this->yy->loc->lastColumn + $matchCount
                )
            );


            $this->yy->text .= $match[0];
            $this->match .= $match[0];
            $this->matches = $match;
            $this->matched .= $match[0];

            $this->yy->leng = strlen($this->yy->text);
            if (isset($this->ranges)) {
                $this->yy->loc->range = new ParserRange($this->offset, $this->offset += $this->yy->leng);
            }
            $this->more = false;
            $this->input = substr($this->input, $matchCount, strlen($this->input));
            $ruleIndex = $rules[$index];
            $nextCondition = $this->conditionStack[$this->conditionStackCount - 1];

            $token = $this->lexerPerformAction($ruleIndex, $nextCondition);

            if ($this->done == true && empty($this->input) == false) {
                $this->done = false;
            }

            if (empty($token) == false) {
                return $this->symbols[
                $token
                ];
            } else {
                return null;
            }
        }

        if (empty($this->input)) {
            return $this->eof;
        } else {
            $this->lexerError("Lexical error on line " . ($this->yy->lineNo + 1) . ". Unrecognized text.\n" . $this->showPosition(), new LexerError("", -1, $this->yy->lineNo));
            return null;
        }
    }

    function lexerLex()
    {
        $r = $this->next();

        while (is_null($r) && !$this->done) {
            $r = $this->next();
        }

        return $r;
    }

    function begin($condition)
    {
        $this->conditionStackCount++;
        $this->conditionStack[] = $condition;
    }

    function popState()
    {
        $this->conditionStackCount--;
        return array_pop($this->conditionStack);
    }

    function currentRules()
    {
        $peek = $this->conditionStack[$this->conditionStackCount - 1];
        return $this->conditions[$peek]->rules;
    }

    function LexerPerformAction($avoidingNameCollisions, $YY_START = null)
    {
        
;
switch($avoidingNameCollisions) {
case 0:/* skip whitespace */
break;
case 1:return 4;
break;
case 2:return 14;
break;
case 3:return 16;
break;
case 4:return 15;
break;
case 5:return 12;
break;
case 6:return 11;
break;
case 7:return 13;
break;
case 8:/* skip EOF */
break;
}

    }
}

class ParserLocation
{
    public $firstLine = 1;
    public $lastLine = 0;
    public $firstColumn = 1;
    public $lastColumn = 0;
    public $range;

    public function __construct($firstLine = 1, $lastLine = 0, $firstColumn = 1, $lastColumn = 0)
    {
        $this->firstLine = $firstLine;
        $this->lastLine = $lastLine;
        $this->firstColumn = $firstColumn;
        $this->lastColumn = $lastColumn;
    }

    public function Range($range)
    {
        $this->range = $range;
    }

    public function __clone()
    {
        return new ParserLocation($this->firstLine, $this->lastLine, $this->firstColumn, $this->lastColumn);
    }
}

class ParserValue
{
    public $leng = 0;
    public $loc;
    public $lineNo = 0;
    public $text;

    function __clone() {
        $clone = new ParserValue();
        $clone->leng = $this->leng;
        if (isset($this->loc)) {
            $clone->loc = clone $this->loc;
        }
        $clone->lineNo = $this->lineNo;
        $clone->text = $this->text;
        return $clone;
    }
}

class LexerConditions
{
    public $rules;
    public $inclusive;

    function __construct($rules, $inclusive)
    {
        $this->rules = $rules;
        $this->inclusive = $inclusive;
    }
}

class ParserProduction
{
    public $len = 0;
    public $symbol;

    public function __construct($symbol, $len = 0)
    {
        $this->symbol = $symbol;
        $this->len = $len;
    }
}

class ParserCachedAction
{
    public $action;
    public $symbol;

    function __construct($action, $symbol = null)
    {
        $this->action = $action;
        $this->symbol = $symbol;
    }
}

class ParserAction
{
    public $action;
    public $state;
    public $symbol;

    function __construct($action, &$state = null, &$symbol = null)
    {
        $this->action = $action;
        $this->state = $state;
        $this->symbol = $symbol;
    }
}

class ParserSymbol
{
    public $name;
    public $index = -1;
    public $symbols = array();
    public $symbolsByName = array();

    function __construct($name, $index)
    {
        $this->name = $name;
        $this->index = $index;
    }

    public function addAction($a)
    {
        $this->symbols[$a->index] = $this->symbolsByName[$a->name] = $a;
    }
}

class ParserError
{
    public $text;
    public $state;
    public $symbol;
    public $lineNo;
    public $loc;
    public $expected;

    function __construct($text, $state, $symbol, $lineNo, $loc, $expected)
    {
        $this->text = $text;
        $this->state = $state;
        $this->symbol = $symbol;
        $this->lineNo = $lineNo;
        $this->loc = $loc;
        $this->expected = $expected;
    }
}

class LexerError
{
    public $text;
    public $token;
    public $lineNo;

    public function __construct($text, $token, $lineNo)
    {
        $this->text = $text;
        $this->token = $token;
        $this->lineNo = $lineNo;
    }
}

class ParserState
{
    public $index;
    public $actions = array();

    function __construct($index)
    {
        $this->index = $index;
    }

    public function setActions(&$actions)
    {
        $this->actions = $actions;
    }
}

class ParserRange
{
    public $x;
    public $y;

    function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}
