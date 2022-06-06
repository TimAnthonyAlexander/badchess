<?php
namespace chess;

require 'vendor/autoload.php';

$ml = new ML();

$results = entireGame($ml);

$white = $results[0];
$black = $results[1];

printf("White won %d times\n", $white);
printf("Black won %d times\n", $black);

function entireGame(ML $ml, int $black = 0, int $white = 0, $counter = 0): array {
    print('New game.'. PHP_EOL);
    $counter++;
    if ($counter > 2) {
        return [$white, $black];
    }
    $board = new Board($ml);

    $turn = true;

    for($i = 0; $i < 50; $i++){
        if ($board->getIsMate()[0]) {
            print('Mate recorded'. PHP_EOL);
            if ($board->getIsMate()[1] === 'b') {
                $white++;
            } else {
                $black++;
            }
            $board->view();
            return entireGame($ml, $black, $white, $counter);
        }
        $color = $turn ? 'w' : 'b';
        $random = rand(0,1) === 0;
        $possibleMoves = $board->getMovesOf($color);
        if (count ($possibleMoves) === 0) {
            if ($color === 'w') {
                $black++;
            } else {
                $white++;
            }
            return entireGame($ml, $black, $white, $counter);
        }
        $randomMove = $possibleMoves[rand(0, count($possibleMoves) - 1)];
        $rec = $random ? $randomMove : $board->recommendMove($board->evaluateBoard(2));
        if(!isset($rec[3])){
            $rec = $board->recommendMove($board->evaluateBoard(3, false), false);
        }
        if(!isset($rec[3])){
            $randomMove = $possibleMoves[rand(0, count($possibleMoves) - 1)];
            assert($randomMove[0] instanceof Piece);
            assert(is_int($randomMove[1]));
            assert(is_int($randomMove[2]));
            printf('Random move due to no recommendations: %s%s %s', Board::translateXYToNotation($randomMove[0]->getX(), $randomMove[0]->getY()), Board::translateXYToNotation($randomMove[1], $randomMove[2]), PHP_EOL);
            $board->movePiece($randomMove[0], $randomMove[1], $randomMove[2]);
            $turn = !$turn;
            $ml->save();
            continue;
        }
        $piece = $board->getPiece($rec[1], $rec[2]);
        $board->movePiece($piece, $rec[3], $rec[4]);
        printf('Learning recommended move %s%s for color %s on board %s: %s.%s', Board::translateXYToNotation($rec[1], $rec[2]), Board::translateXYToNotation($rec[3], $rec[4]), $color === 'b' ? 'black' : 'white', $board->getBoardMD5(), round($rec[0], 3), PHP_EOL);
        $board->view();
        $turn = !$turn;
        $ml->save();
    }

    return entireGame($ml, $black, $white, $counter);
}
