<?php
namespace chess;


class Pawn extends Piece{
    protected string $notation = 'P';

    public function canDo(int $x, int $y, int $checkCheck = 2, bool $ignoreOwn = false): bool {
        if (!parent::canDo($x, $y, $checkCheck)) {
            return false;
        }
        if ($this->getColor() === 'w') {
            if ($y === $this->getY() + 1 || ($y === $this->getY() + 2 && $this->getLastMove() === [])) {
                if ($x === $this->getX()) {
                    return true;
                }
            }
            if ($x === $this->getX() + 1 || $x === $this->getX() - 1) {
                if ($y === $this->getY() + 1) {
                    if ($this->board->getPiece($x, $y) !== null
                        && $this->board->getPiece($x, $y)->getColor() === $this->getColor()){
                        return $ignoreOwn;
                    }
                    return true;
                }
            }
        } else {
            if ($y === $this->getY() - 1 || ($y === $this->getY() - 2 && $this->getLastMove() === [])) {
                if ($x === $this->getX()) {
                    return true;
                }
            }
            if ($x === $this->getX() + 1 || $x === $this->getX() - 1) {
                if ($y === $this->getY() - 1) {
                    if ($this->board->getPiece($x, $y) !== null
                        && $this->board->getPiece($x, $y)->getColor() !== $this->getColor()
                        && $this->board->getPiece($x, $y)->getNotation() !== 'K'){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getMoves(): array {
        $moves = [];
        if ($this->canDo($this->getX(), $this->getY() + 1)) {
            $moves[] = [$this, $this->getX(), $this->getY() + 1];
        }
        if ($this->canDo($this->getX(), $this->getY() + 2)) {
            $moves[] = [$this, $this->getX(), $this->getY() + 2];
        }
        if ($this->canDo($this->getX() + 1, $this->getY() + 1)) {
            $moves[] = [$this, $this->getX() + 1, $this->getY() + 1];
        }
        if ($this->canDo($this->getX() - 1, $this->getY() + 1)) {
            $moves[] = [$this, $this->getX() - 1, $this->getY() + 1];
        }
        if ($this->canDo($this->getX(), $this->getY()-1)) {
            $moves[] = [$this, $this->getX(), $this->getY() - 1];
        }
        if ($this->canDo($this->getX(), $this->getY()-2)) {
            $moves[] = [$this, $this->getX(), $this->getY() - 2];
        }
        if ($this->canDo($this->getX() + 1, $this->getY() - 1)) {
            $moves[] = [$this, $this->getX() + 1, $this->getY() - 1];
        }
        if ($this->canDo($this->getX() - 1, $this->getY() - 1)) {
            $moves[] = [$this, $this->getX() - 1, $this->getY() - 1];
        }
        return $moves;
    }
}
