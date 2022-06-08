<?php
namespace chess;

require('vendor/autoload.php');

// No maximum memory or time limit

/*
ini_set('memory_limit', -1);
set_time_limit(0);
*/

$ml = new ML();


$board = new Board($ml, false);

// Game notation: E2E4, E7E5, G1F3, etc

// Example game:
$game = [
    'E2E4',
    'B8C6',
    'B1C3',
    'C6D4',
    'G1F3',
    'D4F3',
    'G2F3',
];



$board->runGame($game, true);
