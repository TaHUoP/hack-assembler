<?php


namespace TaHUoP\HackAsm;


class Instruction
{
    public function __construct(
        public readonly string $text,
        public readonly int $line,
        public readonly int $originalFileLine
    ) {}
}
