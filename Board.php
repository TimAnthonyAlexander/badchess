<?php
namespace chess;


class Board {
    private const PIECE_CLASSES = [
        'pawn' => Pawn::class,
        'rook' => Rook::class,
        'knight' => Knight::class,
        'bishop' => Bishop::class,
        'queen' => Queen::class,
        'king' => King::class,
    ];

    private const PIECE_WORTH = [
        'P' => 1,
        'R' => 5,
        'N' => 3,
        'B' => 3,
        'Q' => 9,
        'K' => 0,
    ];

    private array $board = [];

    private array $lastMove = [];

    private bool $isMate = false;

    public function __construct(private ML $ml, private bool $eval = true) {
        $this->init();
    }

    public function getPiece(int $x, int $y): ?Piece {
        return $this->board[$x-1][$y-1] ?? null;
    }

    public function runGame(array $game, bool $view = false) {
        $moveNum = 1;
        foreach ($game as $move) {
            [$piece, $x, $y] = self::translateNormalNotationToAction($this, $move);
            if ($piece === null) {
                throw new \Exception('No piece at ' . self::translateXYToNotation($x, $y));
            }
            $movePiece = $this->movePiece($piece, $x, $y);
            if ($view && $movePiece) {
                $eval = $this->eval ? $this->evaluateBoard(1) : 0;
                print(PHP_EOL);
                $lastMove = $this->lastMove;
                if (!isset($lastMove[2])) {
                    $lastMove[2] = '-';
                }
                if ($lastMove[2] === 'w'){
                    print(PHP_EOL.'---------------------------------'.PHP_EOL);
                    printf('%d. %s | Score: %f %s', $moveNum, $move, $eval, PHP_EOL);
                    $moveNum++;
                } else {
                    printf('%s | Score: %f %s', $move, $eval, PHP_EOL);
                }
                $this->view();
                $this->isCheck();
                if ($this->isMate) {
                    print('Mate! '.($this->lastMove[2] === 'w' ? 'white' : 'black').' won!'.PHP_EOL);
                }
                if ($this->eval){
                    $rec = $this->recommendMove($eval);
                    if(isset($rec[3])){
                        printf('Recommended move by machine for %s: %s %s%s.%s', $lastMove[2] === 'w' ? 'black' : 'white', $rec[0], self::translateXYToNotation($rec[1], $rec[2]), self::translateXYToNotation($rec[3], $rec[4]), PHP_EOL);
                    }else{
                        printf('No recommended move by machine for %s.%s', $lastMove[2] === 'w' ? 'black' : 'white', PHP_EOL);
                    }
                }
            }
        }
        print('Game ran successfully'.PHP_EOL);
    }

    public function isCheck(): void {
        $king = $this->getPiecesOf('K', ($this->lastMove[2] === 'w' ? 'b' : 'w'))[0];
        assert($king instanceof King);
        if ($king->isInCheck(1)) {
            print('Check!'.PHP_EOL);
        }
        $moves = $king->getMoves();
        foreach ($moves as $move) {
            if ($move[0]->getNotation() === 'K' ? $king->wouldBeCheck($move[1], $move[2], 1) : $king->isInCheck(1)) {
                print('Checkmated!'.PHP_EOL);
            }
        }
    }

    public function evaluateBoard(int $level = 1, bool $useML = true): float {
        $data = $this->ml->get('eval_'.$this->getBoardMD5());
        if ($data !== null && $useML) {
            return $data;
        }
        $white = [];
        $black = [];
        $white['pieces'] = $this->getAllPiecesOf('w');
        $black['pieces'] = $this->getAllPiecesOf('b');

        $white['score'] = 0;
        $black['score'] = 0;

        foreach ($white['pieces'] as $whitePiece) {
            assert($whitePiece instanceof Piece);
            $white['score'] += self::PIECE_WORTH[$whitePiece->getNotation()];
        }

        foreach ($black['pieces'] as $blackPiece) {
            assert($blackPiece instanceof Piece);
            $black['score'] += self::PIECE_WORTH[$blackPiece->getNotation()];
        }


        if ($level > 0){
            $currentTurn = ($this->lastMove[2] ?? 'b') === 'w' ? 'b' : 'w';

            $randomMoves = $this->getMovesOf($currentTurn);

            foreach($randomMoves as $randomMove){
                $fakeBoard = clone $this;
                $fakeBoard->movePiece($randomMove[0], $randomMove[1], $randomMove[2], true);
                $white['score'] += $fakeBoard->evaluateBoard($level-1) * 0.0001;
                // Remove fakeBoard object instance
                $fakeBoard = null;
            }
        }

        $whiteMoves = $this->getMovesOf('w');
        $blackMoves = $this->getMovesOf('b');

        $white['moves'] = count($whiteMoves);
        $black['moves'] = count($blackMoves);

        $white['score'] += $white['moves'] * 0.001;
        $black['score'] += $black['moves'] * 0.001;

        $return = $white['score'] - $black['score'];
        $this->ml->set('eval_'.$this->getBoardMD5(), $return);
        return $return;
    }

    public function recommendMove(float $eval, bool $useML = true): array {
        $data = $this->ml->get('rec_'.$this->getBoardMD5());
        if ($data !== null && $useML) {
            return $data;
        }
        $score = $eval;

        $currentTurn = ($this->lastMove[2] ?? 'b') === 'w' ? 'b' : 'w';

        $randomMoves = $this->getMovesOf($currentTurn);

        $highestScore= [0, 0, 0];

        foreach ($randomMoves as $randomMove) {
            $fakeBoard = clone $this;
            $fakeBoard->movePiece($randomMove[0], $randomMove[1], $randomMove[2], true);
            $fakeScore = $fakeBoard->evaluateBoard();
            if ($currentTurn === 'w' ? $fakeScore > $score : $fakeScore < $score){
                $score = $fakeScore;
                $highestScore = [$fakeScore, $randomMove[0]->getX(), $randomMove[0]->getY(), $randomMove[1], $randomMove[2]];
            }
            // Remove fakeBoard object instance
            $fakeBoard = null;
        }

        $this->ml->set('rec_'.$this->getBoardMD5(), $highestScore);
        return $highestScore;
    }

    public function init() {
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $this->board[$x][$y] = null;
            }
        }
        $this->default();
    }

    private function setPiece(Piece $piece) {
        $this->board[$piece->getX()-1][$piece->getY()-1] = $piece;
    }

    private function default(): void {
        $this->setPiece(new Pawn(1, 2, 'w', $this));
        $this->setPiece(new Pawn(2, 2, 'w', $this));
        $this->setPiece(new Pawn(3, 2, 'w', $this));
        $this->setPiece(new Pawn(4, 2, 'w', $this));
        $this->setPiece(new Pawn(5, 2, 'w', $this));
        $this->setPiece(new Pawn(6, 2, 'w', $this));
        $this->setPiece(new Pawn(7, 2, 'w', $this));
        $this->setPiece(new Pawn(8, 2, 'w', $this));
        $this->setPiece(new Rook(1, 1, 'w', $this));
        $this->setPiece(new Rook(8, 1, 'w', $this));
        $this->setPiece(new Knight(2, 1, 'w', $this));
        $this->setPiece(new Knight(7, 1, 'w', $this));
        $this->setPiece(new Bishop(3, 1, 'w', $this));
        $this->setPiece(new Bishop(6, 1, 'w', $this));
        $this->setPiece(new Queen(4, 1, 'w', $this));
        $this->setPiece(new King(5, 1, 'w', $this));

        $this->setPiece(new Pawn(1, 7, 'b', $this));
        $this->setPiece(new Pawn(2, 7, 'b', $this));
        $this->setPiece(new Pawn(3, 7, 'b', $this));
        $this->setPiece(new Pawn(4, 7, 'b', $this));
        $this->setPiece(new Pawn(5, 7, 'b', $this));
        $this->setPiece(new Pawn(6, 7, 'b', $this));
        $this->setPiece(new Pawn(7, 7, 'b', $this));
        $this->setPiece(new Pawn(8, 7, 'b', $this));
        $this->setPiece(new Rook(1, 8, 'b', $this));
        $this->setPiece(new Rook(8, 8, 'b', $this));
        $this->setPiece(new Knight(2, 8, 'b', $this));
        $this->setPiece(new Knight(7, 8, 'b', $this));
        $this->setPiece(new Bishop(3, 8, 'b', $this));
        $this->setPiece(new Bishop(6, 8, 'b', $this));
        $this->setPiece(new Queen(4, 8, 'b', $this));
        $this->setPiece(new King(5, 8, 'b', $this));
    }

    public function getPieceByNotation(string $notation): ?Piece {
        return $this->getPiece(...self::translateNotationToXY($notation));
    }

    public static function translateNotationToXY(string $notation): array {
        $letter = substr($notation, 0, 1);
        $number = (int) substr($notation, 1);
        $x = ord($letter) - ord('A') + 1;
        $y = $number;
        return [$x, $y];
    }

    public static function translateXYToNotation(int $x, int $y): string {
        $letter = chr(ord('A') + $x - 1);
        $number = $y;
        return $letter . $number;
    }

    public static function translateNormalNotationToAction(Board $board, string $notation): array {
        // Notation: E2E4
        $firstPosX = substr($notation, 0, 1);
        $firstPosY = (int) substr($notation, 1, 1);
        $secondPosX = substr($notation, 2, 1);
        $secondPosY = (int) substr($notation, 3, 1);

        // Translate X and X2 to numbers
        $firstPosX = ord($firstPosX) - ord('A') + 1;
        $secondPosX = ord($secondPosX) - ord('A') + 1;

        $piece = $board->getPiece($firstPosX, $firstPosY);
        $x2 = $secondPosX;
        $y2 = $secondPosY;
        return [$piece, $x2, $y2];
    }

    public function getPiecesOf(string $notation, string $color): array {
        $pieces = [];
        foreach ($this->board as $row) {
            foreach ($row as $piece) {
                if ($piece !== null && $piece->getNotation() === $notation && $piece->getColor() === $color) {
                    $pieces[] = $piece;
                }
            }
        }
        return $pieces;
    }

    public function movePiece(Piece $piece, int $x, int $y, bool $fake = false): bool {
        if ($fake) {
            $piece = clone $piece;
        }
        if ($this->isMate && !$fake) {
            return false;
        }
        if ($piece->getColor() === ($this->lastMove[2] ?? 'b') && !$fake) {
            printf("%s is not your turn to do %s.%s", $piece->getColor(), self::translateXYToNotation($x, $y) , PHP_EOL);
            return false;
        }
        if ($piece->canDo($x, $y)) {
            $this->board[$x-1][$y-1] = $piece;
            $this->board[$piece->getX()-1][$piece->getY()-1] = null;
            $piece->do($x, $y);
            $this->lastMove = [$x, $y, $piece->getColor()];
            if (!$fake){
                $this->isMate();
            }
            return true;
        }
        if (!$fake){
            printf(
                'Could not move %s.%s',
                $piece->getNotation().
                self::translateXYToNotation($piece->getX(), $piece->getY()).
                self::translateXYToNotation($x, $y), PHP_EOL);
            return false;
        }
        return false;
    }

    public function isMate(): void {
        $checkFor = $this->lastMove[2] === 'w' ? 'b' : 'w';
        $allPiecesOf = $this->getAllPiecesOf($checkFor);
        foreach ($allPiecesOf as $piece) {
            if ($piece->getMoves() !== []) {
                return;
            }
        }
        $colorText = $this->lastMove[2] === 'w' ? 'White' : 'Black';
        $this->isMate = true;
    }

    public function getAllPiecesOf(string $color = 'w'): array {
        $pieces = [];
        foreach ($this->board as $row) {
            foreach ($row as $piece) {
                if ($piece !== null && $piece->getColor() === $color) {
                    $pieces[] = $piece;
                }
            }
        }
        return $pieces;
    }

    public function getAllPieces(): array {
        $pieces = [];
        foreach ($this->board as $row) {
            foreach ($row as $piece) {
                if ($piece !== null) {
                    $pieces[] = $piece;
                }
            }
        }
        return $pieces;
    }

    public function getMovesOf(string $color, int $limit = 0): array {
        $moves = [];
        $i = 0;
        foreach ($this->getAllPiecesOf($color) as $piece) {
            if ($i === $limit && $limit !== 0) {
                continue;
            }
            $moves = array_merge($moves, $piece->getMoves());
            $i++;
        }
        return $moves;
    }

    public function getMoves(): array {
        $pieces = $this->getAllPieces();
        $moves = [];
        foreach ($pieces as $piece) {
            if ($piece !== null) {
                $moves = array_merge($moves, $piece->getMoves());
            }
        }
        return $moves;
    }

    public function generatePiece(string $pieceType, string $color, int $x, int $y) {
        $pieceClass = self::PIECE_CLASSES[$pieceType];
        $piece = new $pieceClass($x, $y, $this);
        $piece->setColor($color);
        $this->board[$x][$y] = $piece;
    }

    public function view() {
        for ($y = 7; $y >= 0; $y--) {
            for ($x = 0; $x < 8; $x++) {
                $piece = $this->board[$x][$y];
                if ($this->lastMove[0] === $x+1 && $this->lastMove[1] === $y+1) {
                    echo '['.($piece ? $piece->toString() : '  ').']';
                } else {
                    echo '('.($piece ? $piece->toString() : '  ').')';
                }
            }
            echo PHP_EOL;
        }
    }

    public function getBoardMD5(): string {
        return md5(json_encode($this->getBoard()));
    }

    public function getIsMate(): array{
        $count = $this->getMovesOf('black');
        if ($this->isMate && count($count) === 0) {
            return [true, 'w'];
        } else if ($this->isMate && count($count) > 0){
            return [true, 'b'];
        }
        return [false, null];
    }

    public function getBoard(): array {
        $boardData = [];
        foreach ($this->board as $key => $row) {
            foreach ($row as $subkey => $piece) {
                if ($piece === null) {
                    $boardData[$key][$subkey] = null;
                } else {
                    assert($piece instanceof Piece);
                    $boardData[$key][$subkey] = $piece->toString();
                }
            }
        }
        $boardData['lastMove'] = $this->lastMove;
        return $boardData;
    }
}
