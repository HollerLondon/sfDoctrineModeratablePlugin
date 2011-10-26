<?php
require(dirname(__FILE__) . '/../../../../test/bootstrap/unit.php');

$t = new lime_test(22);

$t->comment('::hasWord()');
$t->is(RudieFilter::hasWord('\f\u\c\k'), false, '\'/\' characters do not cause error');
$t->is(RudieFilter::hasWord('%f%u%c%k'), false, '\'%\' characters do not cause error');
$t->is(RudieFilter::hasWord('I fuck arses'), true, 'Word matches');
$t->is(RudieFilter::hasWord('FUCK'), true, 'Word matches case-insensitive');
$t->is(RudieFilter::hasWord('scunthorphe'), false, 'Only matches entire word');

$t->comment('::hasString()');
$t->is(RudieFilter::hasString('scunthorphe'), true, 'Match within words');
$t->is(RudieFilter::hasString('SCUNTHORPHE'), true, 'Match within words case-insensitive');

$t->comment('::check()');
$t->is(RudieFilter::check('shit'), true, 'Matches profanity');
$t->is(RudieFilter::check('fucking'), true, 'Matches profanity');
$t->is(RudieFilter::check('flowers'), false, 'Passes nice words');
$t->is(RudieFilter::check('scunthorphe', false), true, 'Selects string-match mode correctly');
$t->is(RudieFilter::check('scunthorphe', true), false, 'Selects word-only mode correctly');

$t->comment('::replace()');
$t->is(RudieFilter::replace('foo bar shit'), 'foo bar ****', 'Replaces profanity matching string length');
$t->is(RudieFilter::replace('foo bar bastard', '**', false), 'foo bar **', 'Replaces profanity with fixed length string');
$t->is(RudieFilter::replace('foo bar scunthorpe', '*', true, false), 'foo bar s****horpe', 'Replaces profanity within word matching string length');
$t->is(RudieFilter::replace('foo bar scuNthoRpe', '*', true, false), 'foo bar s****hoRpe', 'Replaces profanity within word matching string length (case insensitive)');
$t->is(RudieFilter::replace('foo bar scunthorpe', '*', false, false), 'foo bar s*horpe', 'Replaces profanity within word, fixed length');

$t->comment('::strip()');
$t->is(RudieFilter::strip('foo bar shit.'), 'foo bar.', 'Removes profane word at end of string');
$t->is(RudieFilter::strip('foo bar shit fuck'), 'foo bar', 'Removes adjacent profanities and their spaces');
$t->is(RudieFilter::strip('foo bar shit, fuck'), 'foo bar,', 'Removes adjacent profanities and their spaces');
$t->is(RudieFilter::strip('foo shit bar foo fuck'), 'foo bar foo', 'Removes multiple profanities within sentence');
$t->is(RudieFilter::strip('scunthorpe foo bar', false), 'shorpe foo bar', 'Removes profanity (within word)');