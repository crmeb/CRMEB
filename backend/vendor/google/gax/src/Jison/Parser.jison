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

/* description: Parses a path template string and returns an array of
 * Segment objects.
 */

/* options */

//option namespace:Google\GAX\Jison

/* lexical grammar */
%lex
%%

\s+                     /* skip whitespace */
"/"                     return 'FORWARD_SLASH'
"{"                     return 'LEFT_BRACE'
"}"                     return 'RIGHT_BRACE'
"="                     return 'EQUALS'
"**"                    return 'PATH_WILDCARD'
"*"                     return 'WILDCARD'
[^*=}{}\/]+             return 'LITERAL'
<<EOF>>                 /* skip EOF */

/lex

%token 'FORWARD_SLASH'
%token 'LEFT_BRACE'
%token 'RIGHT_BRACE'
%token 'EQUALS'
%token 'LITERAL'
%token 'PATH_WILDCARD'
%token 'WILDCARD'

%% /* language grammar */

/* This file is used by a php port of Jison (https://github.com/zaach/jison). */

template
    : FORWARD_SLASH bound_segments
        {//php    return $2->text;
}
    | bound_segments
        {//php    return $1->text;
}
    ;

bound_segments
    : bound_segment FORWARD_SLASH bound_segments
        {/*php
    $t1 = $1->text;
    $t3 = $3->text;
    $$ = array_merge($t1, $t3);
*/}
    | bound_segment
        {//php    $$ = $1->text;
}
    ;

unbound_segments
    : unbound_terminal FORWARD_SLASH unbound_segments
        {/*php
    $t1 = $1->text;
    $t3 = $3->text;
    $$ = array_merge($t1, $t3);
*/}
    | unbound_terminal
        {//php    $$ = $1->text;
}
    ;

bound_segment
    : bound_terminal
        {//php    $$ = $1->text;
}
    | variable
        {//php    $$ = $1->text;
}
    ;

unbound_terminal
    : WILDCARD
        {/*php
    $t1 = $1->text;
    Segment::incrementSegmentCount();
    $$ = [new Segment(Segment::TERMINAL, $t1)];
*/}
    | PATH_WILDCARD
        {/*php
    $t1 = $1->text;
    Segment::incrementSegmentCount();
    $$ = [new Segment(Segment::TERMINAL, $t1)];
*/}
    | LITERAL
        {/*php
    $t1 = $1->text;
    Segment::incrementSegmentCount();
    $$ = [new Segment(Segment::TERMINAL, $t1)];
*/}
    ;

bound_terminal
    : unbound_terminal
        {/*php
    $t1 = $1->text;
    if (in_array($t1[0]['literal'], ['*', '**'])) {
        $$ = [
            new Segment(Segment::BINDING,
                sprintf('$%d', Segment::getBindingCount())),
            $t1[0],
            new Segment(Segment::END_BINDING, '')
        ];
        Segment::incrementBindingCount();
    } else {
        $$ = $t1;
    }
*/}
    ;

variable
    : LEFT_BRACE LITERAL EQUALS unbound_segments RIGHT_BRACE
        {/*php
    $t2 = $2->text;
    $t4 = $4->text;
    $tmp = [new Segment(Segment::BINDING, $t2)];
    $tmp = array_merge($tmp, $t4);
    array_push($tmp, new Segment(Segment::END_BINDING, ''));
    $$ = $tmp;
*/}
    | LEFT_BRACE LITERAL RIGHT_BRACE
        {/*php
    $t2 = $2->text;
    $$ = [
        new Segment(Segment::BINDING, $t2),
        new Segment(Segment::TERMINAL, '*'),
        new Segment(Segment::END_BINDING, '')
    ];
    Segment::incrementSegmentCount();
*/}
    ;

