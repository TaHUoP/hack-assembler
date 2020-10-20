<?php


namespace TaHUoP;


class Instruction
{
    public string $text;
    private int $line;
    private int $originalFileLine;

    public function __construct(string $text, int $line, int $originalFileLine)
    {
        $this->text = $text;
        $this->line = $line;
        $this->originalFileLine = $originalFileLine;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getOriginalFileLine(): int
    {
        return $this->originalFileLine;
    }
}