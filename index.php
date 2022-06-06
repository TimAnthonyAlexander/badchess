<?php
namespace chess;
require 'vendor/autoload.php';

$ml = new ML();
$board = new Board($ml);

$board->getBoard();

foreach ($board->getBoard() as $key => $column) {
    foreach ($column as $subkey => $piece) {
        $color = substr($piece ?? '  ', 0, 1);
        $pieceNotation = substr($piece ?? '  ', 1, 1);
        $row[$subkey][$key] = $color.$pieceNotation;
    }
}

print(json_encode($row));
