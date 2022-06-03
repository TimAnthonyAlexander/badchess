<?php
namespace chess;


class Queen extends Piece{
    protected string $notation = 'Q';

    public function canDo(int $x, int $y, int $checkCheck = 2, bool $ignoreOwn = false): bool {
        if (!parent::canDo($x, $y, $checkCheck)) {
            return false;
        }
        $dy = abs($this->getY() - $y);
        $dx = abs($this->getX() - $x);
        if ($this->board->getPiece($x, $y) !== null
            && $this->board->getPiece($x, $y)->getColor() === $this->getColor()) {
            return $ignoreOwn;
        }
        if ($dx === $dy) {
            for ($i = 1; $i < $dx; $i++) {
                $nextPosX = $x > $this->getX() ? $this->getX() + $i : $this->getX() - $i;
                $nextPosY = $y > $this->getY() ? $this->getY() + $i : $this->getY() - $i;
                if ($this->board->getPiece($nextPosX, $nextPosY) !== null) {
                    return false;
                }
            }
            return true;
        }
        if ($dx === 0) {
            for ($i = 1; $i < $dy; $i++) {
                if ($this->board->getPiece($this->getX(), $this->getY() - $i) !== null) {
                    return false;
                }
            }
            return true;
        }
        if ($dy === 0) {
            for ($i = 1; $i < $dx; $i++) {
                if ($this->board->getPiece($this->getX() - $i, $this->getY()) !== null) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function getMoves(): array {
        $moves = [];
        for ($i = 1; $i < 8; $i++) {
            $nextPosX = $this->getX() + $i;
            $nextPosY = $this->getY() + $i;
            if ($this->canDo($nextPosX, $nextPosY)) {
                $moves[] = [$this, $nextPosX, $nextPosY];
            }
        }
        for ($i = 1; $i < 8; $i++) {
            $nextPosX = $this->getX() - $i;
            $nextPosY = $this->getY() + $i;
            if ($this->canDo($nextPosX, $nextPosY)) {
                $moves[] = [$this, $nextPosX, $nextPosY];
            }
        }
        for ($i = 1; $i < 8; $i++) {
            $nextPosX = $this->getX() + $i;
            $nextPosY = $this->getY() - $i;
            if ($this->canDo($nextPosX, $nextPosY)) {
                $moves[] = [$this, $nextPosX, $nextPosY];
            }
        }
        for ($i = 1; $i < 8; $i++) {
            $nextPosX = $this->getX() - $i;
            $nextPosY = $this->getY() - $i;
            if ($this->canDo($nextPosX, $nextPosY)) {
                $moves[] = [$this, $nextPosX, $nextPosY];
            }
        }
        return $moves;
    }
}
