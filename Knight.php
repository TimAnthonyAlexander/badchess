<?php
namespace chess;


class Knight extends Piece{
    protected string $notation = 'N';

    public function canDo(int $x, int $y, int $checkCheck = 2, bool $ignoreOwn = false): bool {
        if (!parent::canDo($x, $y, $checkCheck)) {
            return false;
        }
        if ($this->board->getPiece($x, $y) !== null
            && $this->board->getPiece($x, $y)->getColor() === $this->getColor()) {
            return $ignoreOwn;
        }
        $dx = abs($this->getX() - $x);
        $dy = abs($this->getY() - $y);
        return ($dx == 2 && $dy == 1) || ($dx == 1 && $dy == 2);
    }

    public function getMoves(): array {
        $moves = [];
        for ($i = -2; $i <= 2; $i++) {
            for ($j = -2; $j <= 2; $j++) {
                if ($i == 0 && $j == 0) {
                    continue;
                }
                $nextPosX = $this->getX() + $i;
                $nextPosY = $this->getY() + $j;
                if ($this->canDo($nextPosX, $nextPosY)) {
                    $moves[] = [$this, $nextPosX, $nextPosY];
                }
            }
        }
        return $moves;
    }
}
