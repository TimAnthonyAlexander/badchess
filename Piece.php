<?php
namespace chess;


abstract class Piece {
    private int $x;
    private int $y;
    private string $color = 'white';

    private array $lastMove = [];

    public function __construct(int $x, int $y, string $color = 'white', protected ?Board $board = null) {
        $this->x = $x;
        $this->y = $y;
        $this->color = $color;
    }

    public function getNotation(): string {
        return $this->notation;
    }

    public function getColor(): string{
        return $this->color;
    }

    public function getLastMove(): array{
        return $this->lastMove;
    }

    private function setLastMove(int $x, int $y): void{
        $this->lastMove = [$x, $y];
    }

    public function setColor(string $color): void{
        $this->color = $color;
    }

    public function getX(): int{
        return $this->x;
    }

    public function setX(int $x): void{
        $this->x = $x;
    }

    public function getY(): int{
        return $this->y;
    }

    public function setY(int $y): void{
        $this->y = $y;
    }

    public function getMoves(): array {
        return [];
    }

    public function canDo(int $x, int $y, int $checkCheck = 1, bool $ignoreOwn = false): bool {
        if ($x < 1 || $x > 8 || $y < 1 || $y > 8) {
            return false;
        }
        if ($x === $this->getX() && $y === $this->getY()) {
            return false;
        }
        $ownKing = $this->board->getPiecesOf('K', $this->getColor())[0];
        assert($ownKing instanceof King);
        if ($this->getNotation() === 'K' ? $ownKing->wouldBeCheck($x, $y, $checkCheck-1) : $ownKing->wouldBeCheck($ownKing->getX(), $ownKing->getY(), $checkCheck-1)) {
            if ($this->board->checkCheck($checkCheck)){
                $fakeBoard = clone $this->board;
                $fakeBoard->movePiece($this, $x, $y, true, last: true);
                if($fakeBoard->checkCheck($checkCheck)){
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    public function couldDo(int $x, int $y) {
        $checkCheck = 1;
        return $this->canDo($x, $y, $checkCheck, true);
    }

    public function do(int $x, int $y) {
        $this->setLastMove($x, $y);
        $this->setX($x);
        $this->setY($y);
    }

    public function toString() {
        return ($this->color === 'w' ? 'w' : 'b').strtoupper($this->notation ?? '');
    }
}
