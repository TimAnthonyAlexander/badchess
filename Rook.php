<?php
namespace chess;


class Rook extends Piece{
    protected string $notation = 'R';

    public function canDo(int $x, int $y, int $checkCheck = 2, bool $ignoreOwn = false): bool {
        if (!parent::canDo($x, $y, $checkCheck)) {
            return false;
        }
        if ($this->getX() === $x) {
            for ($i = min($this->getY(), $y); $i < max($this->getY(), $y); $i++) {
                if ($this->board->getPiece($x, $i) !== null) {
                    return false;
                }
            }
            return true;
        }
        if ($this->getY() === $y) {
            for ($i = min($this->getX(), $x); $i < max($this->getX(), $x); $i++) {
                if ($this->board->getPiece($i, $y) !== null) {
                    return false;
                }
            }
            return true;
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
