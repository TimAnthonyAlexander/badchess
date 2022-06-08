<?php
namespace chess;


class Rook extends Piece{
    protected string $notation = 'R';

    public function canDo(int $x, int $y, int $checkCheck = 2, bool $ignoreOwn = false): bool {
        if (!parent::canDo($x, $y, $checkCheck)) {
            return false;
        }
        if ($y === $this->getY() && $x !== $this->getX()) {
            if ($x < $this->getX()) {
                for ($i = $this->getX()-1; $i > $x; $i--) {
                    if ($this->board->getPiece($i, $y) !== null) {
                        return false;
                    }
                }
                return true;
            }
            if ($x > $this->getX()) {
                for ($i = $this->getX()+1; $i < $x; $i++) {
                    if ($this->board->getPiece($i, $y) !== null) {
                        return false;
                    }
                }
                return true;
            }
        }
        if ($x === $this->getX() && $y !== $this->getY()) {
            if ($y < $this->getY()) {
                for ($i = $this->getY()-1; $i > $y; $i--) {
                    if ($this->board->getPiece($x, $i) !== null) {
                        return false;
                    }
                }
                return true;
            }
            if ($y > $this->getY()) {
                for ($i = $this->getY()+1; $i < $y; $i++) {
                    if ($this->board->getPiece($x, $i) !== null) {
                        return false;
                    }
                }
                return true;
            }
        }
        if ($this->board->getPiece($x, $y) !== null && $this->board->getPiece($x, $y)->getColor() === $this->getColor()) {
            return $ignoreOwn;
        }
        return false;
    }

    public function getMoves(): array {
        $moves = [];
        for ($i = min($this->getX(), $this->getY()); $i < max($this->getX(), $this->getY()); $i++) {
            if ($this->canDo($i, $i)) {
                $moves[] = [$this, $i, $i];
            }
        }
        return $moves;
    }
}
