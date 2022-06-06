<?php
namespace chess;

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
    $board->movePiece($action[0], $action[1], $action[2]);
    $eval = $board->evaluateBoard();
    $rec = $board->recommendMove($eval);
    $board->movePiece($board->getPiece($rec[1], $rec[2]), $rec[3], $rec[4]);
    $_SESSION['board'] = $board;
}


$getBoard = $board->getBoard();
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
        $textcolor = $color;
        $color = ($color === 'white' ? 'gray' : 'white');
        if ($piece === '') {
            echo '<td style="height: 62px; width: 62px; background-color: '.$color.'; color: '.$textcolor.';"> </td>';
        } else{
            echo '<td style="height: 62px; width: 62px; background-color: ' . $color . '; color: ' . $textcolor . '"><img src="'.getImage($piece).'"></td>';
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

