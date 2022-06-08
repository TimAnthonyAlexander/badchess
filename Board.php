<?php
namespace chess;


class Board {
    public const EVAL_LEVEL = 3;

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

    private array $recommendations = [];

    /**
     *
     */
    public function __construct(private ML $ml, private bool $eval = true) {
        $this->init();
    }

    /**
     * @TODO Describe getPiece
     * @param int $x
     * @param int $y
     * @return \chess\Piece|null
     */
    public function getPiece(int $x, int $y): ?Piece {
        return $this->board[$x-1][$y-1] ?? null;
    }

    /**
     * @TODO Describe runGame
     * @param array $game
     * @param bool  $view
     * @return void
     * @throws \Exception
     */
    public function runGame(array $game, bool $view = false) {
        $moveNum = 1;
        foreach ($game as $move) {
            [$piece, $x, $y] = self::translateNormalNotationToAction($this, $move);
            if ($piece === null) {
                throw new \Exception('No piece at ' . self::translateXYToNotation($x, $y));
            }
            $movePiece = $this->movePiece($piece, $x, $y);
            if ($view && $movePiece) {
                $eval = $this->eval ? $this->evaluateBoard(self::EVAL_LEVEL) : 0;
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

    /**
     * @TODO Describe checkCheck
     * @param int $checkCheck
     * @return bool
     */
    public function checkCheck(int $checkCheck = 2): bool {
        $king = $this->getPiecesOf('K', (($this->lastMove[2] ?? 'b') === 'w' ? 'b' : 'w'))[0];
        assert($king instanceof King);
        return $king->isInCheck($checkCheck-1);
    }

    /**
     * @TODO Describe checkMate
     * @return bool
     */
    public function checkMate(): bool {
        if ($this->checkCheck(2)){
            $king = $this->getPiecesOf('K', ($this->lastMove[2] === 'w' ? 'b' : 'w'))[0];
            assert($king instanceof King);
            $moves = $king->getMoves();
            foreach($moves as $move){
                if($move[0]->getNotation() === 'K' ? $king->wouldBeCheck($move[1], $move[2], 1) : $king->isInCheck(1)){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @TODO Describe isCheck
     * @return void
     */
    public function isCheck(): void {
        if ($this->checkCheck()) {
            print('Check!'.PHP_EOL);
        }
        if ($this->checkMate()) {
            print('Checkmate!'.PHP_EOL);
        }
    }

    /**
     * @TODO Describe evaluateBoard
     * @param int  $level
     * @param bool $useML
     * @return float
     */
    public function evaluateBoard(int $level = self::EVAL_LEVEL, bool $useML = true): float {
        if ($level === 0) {
            return 0;
        }

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

        foreach ($black['pieces'] as $blackPiece) {
            assert($blackPiece instanceof Piece);
            // Check if position is identical to default
            $notation = $blackPiece->getNotation();
            $isDefault = match($notation){
                'P' => in_array([$blackPiece->getX(), $blackPiece->getY()], [[1, 7], [2, 7], [3, 7], [4, 7], [5, 7], [6, 7], [7, 7], [8, 7]]),
                'R' => in_array([$blackPiece->getX(), $blackPiece->getY()], [[1, 8], [8, 8]]),
                'N' => in_array([$blackPiece->getX(), $blackPiece->getY()], [[2, 8], [7, 8]]),
                'B' => in_array([$blackPiece->getX(), $blackPiece->getY()], [[3, 8], [6, 8]]),
                'Q' => [$blackPiece->getX(), $blackPiece->getY()] == [4, 8],
                'K' => [$blackPiece->getX(), $blackPiece->getY()] == [5, 8],
                default => false,
            };

            if ($isDefault) {
                $black['score'] -= self::PIECE_WORTH[$blackPiece->getNotation()] * 0.1;
            }
        }

        foreach ($white['pieces'] as $whitePiece) {
            assert($whitePiece instanceof Piece);
            // Check if position is identical to default
            $notation = $whitePiece->getNotation();
            $isDefault = match($notation){
                'P' => in_array([$whitePiece->getX(), $whitePiece->getY()], [[1, 2], [2, 2], [3, 2], [4, 2], [5, 2], [6, 2], [7, 2], [8, 2]]),
                'R' => in_array([$whitePiece->getX(), $whitePiece->getY()], [[1, 1], [8, 1]]),
                'N' => in_array([$whitePiece->getX(), $whitePiece->getY()], [[2, 1], [7, 1]]),
                'B' => in_array([$whitePiece->getX(), $whitePiece->getY()], [[3, 1], [6, 1]]),
                'Q' => [$whitePiece->getX(), $whitePiece->getY()] == [4, 1],
                'K' => [$whitePiece->getX(), $whitePiece->getY()] == [5, 1],
                default => false,
            };

            if ($isDefault) {
                $white['score'] -= self::PIECE_WORTH[$whitePiece->getNotation()] * 0.1;
            }
        }

        $lastMove = $this->getLastMove()[2] ?? 'b';

        $whiteMoves = $this->getMovesOf('w');
        $blackMoves = $this->getMovesOf('b');

        $interestingMoves = $lastMove === 'w' ? $blackMoves : $whiteMoves;
        foreach ($interestingMoves as $interestingMove) {
            $fakeBoard = clone $this;
            if ($fakeBoard->movePiece($interestingMove[0], $interestingMove[1], $interestingMove[2], true)){
                $scoreColor = $fakeBoard->getLastMove()[2] === 'w' ? 'black' : 'white';
                $eval = $fakeBoard->evaluateBoard($level - 1) * 0.2;
                if ($scoreColor === 'white') {
                    $white['score'] += $eval * 0.1;
                } else {
                    $black['score'] += $eval * 0.1;
                }
            }
        }

        $white['moves'] = count($whiteMoves);
        $black['moves'] = count($blackMoves);

        $white['score'] += $white['moves'] * 0.001;
        $black['score'] += $black['moves'] * 0.001;

        $return = $white['score'] - $black['score'];
        $this->ml->set('eval_'.$this->getBoardMD5(), $return);
        return $return;
    }

    /**
     * @TODO Describe recommendMove
     * @param float $eval
     * @param bool  $useML
     * @param int   $level
     * @return array|int[]
     */
    public function recommendMove(float $eval, bool $useML = true, int $level = 1): array {
        $data = $this->ml->get('rec_'.$this->getBoardMD5());
        if ($data !== null && $useML) {
            return $data;
        }

        $currentTurn = ($this->lastMove[2] ?? 'b') === 'w' ? 'b' : 'w';

        $randomMoves = $this->getMovesOf($currentTurn);
        $moveEvals = [];
        $this->recommendations = [];

        $highestScore = [($currentTurn === 'w' ? -99999999 : 9999999), 0, 0];

        if ($level === 0) {
            return [$randomMoves[0][0], $randomMoves[0][1], $randomMoves[0][2]];
        }

        $evaluations = [];

        foreach ($randomMoves as $randomMove) {
            $fakeBoard = clone $this;
            if ($fakeBoard->movePiece($randomMove[0], $randomMove[1], $randomMove[2], true)){
                $fakeScore = $fakeBoard->evaluateBoard(self::EVAL_LEVEL);
                $moveEvals[$randomMove[0]->getNotation() . $randomMove[1] . $randomMove[2]] = $fakeScore;
                $evaluations[] = [$fakeScore, $randomMove[0]->getX(), $randomMove[0]->getY(), $randomMove[1], $randomMove[2]];
            }
            // Remove fakeBoard object instance
            $fakeBoard = null;
        }

        if ($currentTurn === 'w') {
            foreach ($evaluations as $eval) {
                if ($eval[0] > $highestScore[0]) {
                    $highestScore = $eval;
                }
            }
        } else {
            foreach ($evaluations as $eval) {
                if ($eval[0] < $highestScore[0]) {
                    $highestScore = $eval;
                }
            }
        }

        // Sort moves from highest to lowest value, keep keys
        asort($moveEvals);
        if ($currentTurn === 'w') {
            $moveEvals = array_reverse($moveEvals, true);
        }

        $this->recommendations = $moveEvals;

        $this->ml->set('rec_'.$this->getBoardMD5(), $highestScore);
        return $highestScore;
    }

    /**
     * @TODO Describe init
     * @return void
     */
    public function init() {
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $this->board[$x][$y] = null;
            }
        }
        $this->default();
    }

    /**
     * @TODO Describe setPiece
     * @param \chess\Piece $piece
     * @return void
     */
    private function setPiece(Piece $piece) {
        $this->board[$piece->getX()-1][$piece->getY()-1] = $piece;
    }

    /**
     * @TODO Describe default
     * @return void
     */
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

    /**
     * @TODO Describe getPieceByNotation
     * @param string $notation
     * @return \chess\Piece|null
     */
    public function getPieceByNotation(string $notation): ?Piece {
        return $this->getPiece(...self::translateNotationToXY($notation));
    }

    /**
     * @TODO Describe translateNotationToXY
     * @param string $notation
     * @return array
     */
    public static function translateNotationToXY(string $notation): array {
        $letter = substr($notation, 0, 1);
        $number = (int) substr($notation, 1);
        $x = ord($letter) - ord('A') + 1;
        $y = $number;
        return [$x, $y];
    }

    /**
     * @TODO Describe translateXYToNotation
     * @param int $x
     * @param int $y
     * @return string
     */
    public static function translateXYToNotation(int $x, int $y): string {
        $letter = chr(ord('A') + $x - 1);
        $number = $y;
        return $letter . $number;
    }

    /**
     * @TODO Describe translateNormalNotationToAction
     * @param \chess\Board $board
     * @param string       $notation
     * @return array
     */
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

    /**
     * @TODO Describe getPiecesOf
     * @param string $notation
     * @param string $color
     * @return array
     */
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

    /**
     * @TODO Describe movePiece
     * @param \chess\Piece $piece
     * @param int          $x
     * @param int          $y
     * @param bool         $fake
     * @param bool         $last
     * @return bool
     */
    public function movePiece(Piece $piece, int $x, int $y, bool $fake = false, bool $last = false): bool {
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
        if ($piece->canDo($x, $y, 1)) {
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

    /**
     * @TODO Describe isMate
     * @return void
     */
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

    /**
     * @TODO Describe getAllPiecesOf
     * @param string $color
     * @return array
     */
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

    /**
     * @TODO Describe getAllPieces
     * @return array
     */
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

    /**
     * @TODO Describe getMovesOf
     * @param string $color
     * @param int    $limit
     * @return array
     */
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

    /**
     * @TODO Describe getMoves
     * @return array
     */
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

    /**
     * @TODO Describe generatePiece
     * @param string $pieceType
     * @param string $color
     * @param int    $x
     * @param int    $y
     * @return void
     */
    public function generatePiece(string $pieceType, string $color, int $x, int $y) {
        $pieceClass = self::PIECE_CLASSES[$pieceType];
        $piece = new $pieceClass($x, $y, $this);
        $piece->setColor($color);
        $this->board[$x][$y] = $piece;
    }

    /**
     * @TODO Describe view
     * @return void
     */
    public function view() {
        $lastMove = $this->lastMove;
        if (!isset($lastMove[0]) && !isset($lastMove[1])) {
            $lastMove = [0, 0, 'b'];
        }
        for ($y = 7; $y >= 0; $y--) {
            for ($x = 0; $x < 8; $x++) {
                $piece = $this->board[$x][$y];
                if ($lastMove[0] === $x+1 && $lastMove[1] === $y+1) {
                    echo '['.($piece ? $piece->toString() : '  ').']';
                } else {
                    echo '('.($piece ? $piece->toString() : '  ').')';
                }
            }
            echo '  '.($y+1).PHP_EOL;
        }
        echo PHP_EOL.'( A)( B)( C)( D)( E)( F)( G)( H)'.PHP_EOL.PHP_EOL;
    }

    /**
     * @TODO Describe getBoardMD5
     * @return string
     */
    public function getBoardMD5(): string {
        return md5(json_encode($this->getBoard()));
    }

    /**
     * @TODO Describe getIsMate
     * @return array
     */
    public function getIsMate(): array{
        $count = $this->getMovesOf('black');
        if ($this->isMate && count($count) === 0) {
            return [true, 'w'];
        } else if ($this->isMate && count($count) > 0){
            return [true, 'b'];
        }
        return [false, null];
    }

    /**
     * @return array
     */
    public function getRecommendations(): array{
        return $this->recommendations;
    }

    /**
     * @TODO Describe getBoard
     * @return array
     */
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

    /**
     * @return array
     */
    public function getLastMove(): array{
        return $this->lastMove;
    }
}
