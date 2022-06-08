<?php
namespace chess;

use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    private Board $board;

    public function setUp(): void
    {
        $this->ml = $ml = new ML();
        $this->board = new Board($ml);
    }
}
