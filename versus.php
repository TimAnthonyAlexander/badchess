<?php
namespace chess;

require('vendor/autoload.php');

// No maximum memory or time limit
ini_set('memory_limit', -1);
set_time_limit(0);


print "Chess by Tim Anthony Alexander with custom engine.".PHP_EOL;
print "Notation style: E2E4, E7E5, B2C3, etc".PHP_EOL.PHP_EOL;

$ml = new ML();
$board = new Board($ml);

$board->view();
print('----------------------------------------'.PHP_EOL);

print "Move: ";

$fp = fopen('php://stdin', 'r');
while (true) {
    $next_line = fgets($fp, 1024); // read the special file to get the user input from keyboard
    $action = Board::translateNormalNotationToAction($board, str_replace("\n", "", $next_line));
    $piece = $action[0];
    if ($piece === null) {
        print "Invalid move. Please try again.".PHP_EOL;
        print "Move: ";
        continue;
    }
    $board->movePiece($piece, $action[1], $action[2]);
    $ml->save();
    $board->view();
    print(PHP_EOL);
    print('THINKING...'.PHP_EOL);
    print(PHP_EOL);
    $eval = $board->evaluateBoard(Board::EVAL_LEVEL);
    $rec = $board->recommendMove($eval);
    if ($rec[1] !== null){
        $piece = $board->getPiece($rec[1], $rec[2]);
        if ($piece !== null){
            $board->movePiece($piece, $rec[3], $rec[4]);
        }
    } else {
        $randomMove = $board->getMovesOf(($board->getLastMove()[2] ?? 'b') === 'w' ? 'b' : 'w', 1);
        $randomMove = $randomMove[array_rand($randomMove)];
        $board->movePiece($board->getPiece($randomMove[0], $randomMove[1]), $randomMove[2], $randomMove[3]);
    }
    $board->view();
    print('----------------------------------------'.PHP_EOL);
    printf('The current evaluation is %s in favor of %s'.PHP_EOL, round($eval, 2), $eval > 0 ? 'white' : 'black');
    print("Move: ");
}
