<?php
namespace chess;


class King extends Piece{
    protected string $notation = 'K';

    public function canDo(int $x, int $y, int $checkCheck = 2, bool $ignoreOwn = false): bool {
        if (!parent::canDo($x, $y, $checkCheck)) {
            return false;
        }
        $dx = abs($this->getX() - $x);
        $dy = abs($this->getY() - $y);
        if ($this->wouldBeCheck($x, $y, $checkCheck)) {
            return false;
        }
        if ($this->board->getPiece($x, $y) !== null && $this->board->getPiece($x, $y)->getColor() === $this->getColor()) {
            return $ignoreOwn;
        }
        return ($dx <= 1 && $dy <= 1);
    }

    public function isInCheck(int $checkCheck): bool {
        return $this->wouldBeCheck($this->getX(), $this->getY(), $checkCheck);
    }

    public function wouldBeCheck(int $x, int $y, int $checkCheck): bool {
        if ($checkCheck === 0) {
            return false;
        }

        // Check for opponent's pieces
        $color = $this->getColor();
        $oppositeColor = match ($color) {
            'w' => 'b',
            'b' => 'w',
        };

        // Queen
        $queens = $this->board->getPiecesOf('Q', $oppositeColor);
        foreach ($queens as $queen) {
            assert($queen instanceof Queen);
            if ($queen->couldDo($x, $y)) {
                return true;
            }
        }

        // Rook
        $rooks = $this->board->getPiecesOf('R', $oppositeColor);
        foreach ($rooks as $rook) {
            assert($rook instanceof Rook);
            if ($rook->couldDo($x, $y)) {
                return true;
            }
        }

        // Bishop
        $bishops = $this->board->getPiecesOf('B', $oppositeColor);
        foreach ($bishops as $bishop) {
            assert($bishop instanceof Bishop);
            if ($bishop->couldDo($x, $y)) {
                return true;
            }
        }

        // Knight
        $knights = $this->board->getPiecesOf('N', $oppositeColor);
        foreach ($knights as $knight) {
            assert($knight instanceof Knight);
            if ($knight->couldDo($x, $y)) {
                return true;
            }
        }

        // Pawn
        $pawns = $this->board->getPiecesOf('P', $oppositeColor);
        foreach ($pawns as $pawn) {
            assert($pawn instanceof Pawn);
            if ($pawn->couldDo($x, $y)) {
                return true;
            }
        }

        // King
        $king = $this->board->getPiecesOf('K', $oppositeColor)[0];
        if ($king->getX() === $x && $king->getY() === $y) {
            return true;
        }
        return false;
    }

    public function getMoves(): array {
        $moves = [];
        if ($this->canDo($this->getX() + 1, $this->getY())) {
            $moves[] = [$this, $this->getX()+ 1, $this->getY()];
        }
        if ($this->canDo($this->getX() - 1, $this->getY())) {
            $moves[] = [$this, $this->getX()- 1, $this->getY()];
        }
        if ($this->canDo($this->getX(), $this->getY() + 1)) {
            $moves[] = [$this, $this->getX(), $this->getY() + 1];
        }
        if ($this->canDo($this->getX(), $this->getY() - 1)) {
            $moves[] = [$this, $this->getX(), $this->getY() - 1];
        }
        if ($this->canDo($this->getX() + 1, $this->getY() + 1)) {
            $moves[] = [$this, $this->getX()+ 1, $this->getY() + 1];
        }
        if ($this->canDo($this->getX() - 1, $this->getY() - 1)) {
            $moves[] = [$this, $this->getX()- 1, $this->getY() - 1];
        }
        if ($this->canDo($this->getX() + 1, $this->getY() - 1)) {
            $moves[] = [$this, $this->getX()+ 1, $this->getY() - 1];
        }
        if ($this->canDo($this->getX() - 1, $this->getY() + 1)) {
            $moves[] = [$this, $this->getX()- 1, $this->getY() + 1];
        }
        return $moves;
    }
}
