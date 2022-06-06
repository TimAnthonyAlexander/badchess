<?php
namespace chess;
ini_set('display_errors', 1);
error_reporting(E_ALL);


require(__DIR__.'/vendor/autoload.php');

$ml = new ML();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check if board is initalized
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = new Board($ml);
}

$board = $_SESSION['board'];


if ($_POST['move'] ?? false) {
    $action = Board::translateNormalNotationToAction($board, strtoupper($_POST['move']));
    $movePiece = $board->movePiece($action[0], $action[1], $action[2]);
    if ($movePiece){
        $eval = $board->evaluateBoard();
        $rec = $board->recommendMove($eval);
        if ($rec[1] !== null){
            $board->movePiece($board->getPiece($rec[1], $rec[2]), $rec[3], $rec[4]);
        } else {
            $randomMove = $board->getMovesOf('b', 1);
            $randomMove = $randomMove[array_rand($randomMove)];
            $board->movePiece($board->getPiece($randomMove[0], $randomMove[1]), $randomMove[2], $randomMove[3]);
        }
    } else {
        print ("Try another move.");
    }
    $_SESSION['board'] = $board;
}

$eval = $board->evaluateBoard();

if (isset($eval)) {
    $eval > 0
        ? print("The evaluation is currently ".$eval." in favor of white.<br>")
        : print("The evaluation is currently ".$eval." in favor of black.<br>");
}


$getBoard = $board->getBoard();
$lastMove = $board->getLastMove();
if ($lastMove !== []){
    $translation = Board::translateXYToNotation($lastMove[0], $lastMove[1]);
    print ("<h3>Last move: " . $translation . " " . $lastMove[2] . "</h3>");
}
unset($getBoard['lastMove']);

foreach ($getBoard as $key => $column) {
    foreach ($column as $subkey => $piece) {
        $color = substr($piece ?? '  ', 0, 1);
        $pieceNotation = substr($piece ?? '  ', 1, 1);
        $row[$subkey][$key] = $color.$pieceNotation;
    }
}



function getImage(string $piece) {
    $pawnWhite = 'https://upload.wikimedia.org/wikipedia/commons/0/04/Chess_plt60.png';
    $pawnBlack = 'https://upload.wikimedia.org/wikipedia/commons/c/cd/Chess_pdt60.png';
    $rookWhite = 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Chess_rlt60.png';
    $rookBlack = 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Chess_rdt60.png';
    $knightWhite = 'https://upload.wikimedia.org/wikipedia/commons/2/28/Chess_nlt60.png';
    $knightBlack = 'https://upload.wikimedia.org/wikipedia/commons/f/f1/Chess_ndt60.png';
    $bishopWhite = 'https://upload.wikimedia.org/wikipedia/commons/9/9b/Chess_blt60.png';
    $bishopBlack = 'https://upload.wikimedia.org/wikipedia/commons/8/81/Chess_bdt60.png';
    $queenWhite = 'https://upload.wikimedia.org/wikipedia/commons/4/49/Chess_qlt60.png';
    $queenBlack = 'https://upload.wikimedia.org/wikipedia/commons/a/af/Chess_qdt60.png';
    $kingWhite = 'https://upload.wikimedia.org/wikipedia/commons/3/3b/Chess_klt60.png';
    $kingBlack = 'https://upload.wikimedia.org/wikipedia/commons/e/e3/Chess_kdt60.png';

    return match($piece) {
        'wP' => $pawnWhite,
        'bP' => $pawnBlack,
        'wR' => $rookWhite,
        'bR' => $rookBlack,
        'wN' => $knightWhite,
        'bN' => $knightBlack,
        'wB' => $bishopWhite,
        'bB' => $bishopBlack,
        'wQ' => $queenWhite,
        'bQ' => $queenBlack,
        'wK' => $kingWhite,
        'bK' => $kingBlack,
        default => '',
    };
}

// The 3d array $row is horizontally mirrored, but vertically correct
$row = array_reverse($row);

// Print a html table with the pieces
echo '<table>';
$color = 'white';
foreach ($row as $key => $column) {
    echo '<tr>';
    foreach ($column as $subkey => $piece) {
        $posY = 8-$key;
        $posX = $subkey+1;
        $textcolor = $color;
        $isLast = false;
        $color = ($color === 'white' ? 'gray' : 'white');
        if (isset($lastMove[1]) && $posX === $lastMove[0] && $posY === $lastMove[1]) {
            $isLast = true;
        }
        if (trim($piece) === '') {
            echo '<td style="height: 62px; width: 62px; background-color: '.($isLast ? 'red' : $color).'; color: '.$textcolor.';">'.Board::translateXYToNotation($posX, $posY).' </td>';
        } else{
            echo '<td style="height: 62px; width: 62px; background-color: ' . ($isLast ? 'red' : $color) . '; color: ' . $textcolor . '"><img src="'.getImage($piece).'"></td>';
        }
    }
    echo '</tr>';
}
echo '</table>';

// Form to send the move
echo '<form action="" method="post">';
echo '<input type="text" name="move" autofocus>';
echo '<input type="submit" value="Send">';
echo '</form>';

