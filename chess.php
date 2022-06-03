<?php
namespace chess;

require('vendor/autoload.php');



$ml = new ML();


$board = new Board($ml, false);

// Game notation: E2E4, E7E5, G1F3, etc

// Example game:
$game = [
    'E2E4',
    'E7E5',
    'D1H5',
    'A7A6',
    'F1C4',
    'B8C6',
    'H5F7',
];



$board->runGame($game, true);
