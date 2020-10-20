<?php


namespace TaHUoP;


class Parser
{
    private const COMMENT_REGEX = '/\/\/.*$/';
    private const SYMBOL_REGEX = '/^\((.*)\)$/';
    private const A_INSTRUCTION_REGEX = '/^@(.*)$/';
    private const SYMBOLLESS_A_INSTRUCTION_VALUE_REGEX = '/^([0-9])*$/';
    private const C_INSTRUCTION_REGEX = '/^(M=|D=|MD=|A=|AM=|AD=|AMD=)?(0|1|-1|D|A|M|!D|!A|!M|-D|-A|-M|D\+1|A\+1|M\+1|D-1|A-1|M-1|D\+A|D\+M|D-A|D-M|A-D|M-D|D&A|D&M|D\|A|D\|M)(;JGT|;JEQ|;JGE|;JLT|;JNE|;JLE|;JMP)?$/';
    private const COMP = [
        '0' => '0101010',
        '1' => '0111111',
        '-1' => '0111010',
        'D' => '0001100',
        'A' => '0110000',
        'M' => '1110000',
        '!D' => '0001101',
        '!A' => '0110001',
        '!M' => '1110001',
        '-D' => '0001111',
        '-A' => '0110011',
        '-M' => '1110011',
        'D+1' => '0011111',
        'A+1' => '0110111',
        'M+1' => '1110111',
        'D-1' => '0001110',
        'A-1' => '0110010',
        'M-1' => '1110010',
        'D+A' => '0000010',
        'D+M' => '1000010',
        'D-A' => '0010011',
        'D-M' => '1010011',
        'A-D' => '0000111',
        'M-D' => '1000111',
        'D&A' => '0000000',
        'D&M' => '1000000',
        'D|A' => '0010101',
        'D|M' => '1010101',
    ];
    private const DEST = [
        '' => '000',
        'M' => '001',
        'D' => '010',
        'MD' => '011',
        'A' => '100',
        'AM' => '101',
        'AD' => '110',
        'AMD' => '111',
    ];
    private const JMP = [
        '' => '000',
        'JGT' => '001',
        'JEQ' => '010',
        'JGE' => '011',
        'JLT' => '100',
        'JNE' => '101',
        'JLE' => '110',
        'JMP' => '111',
    ];
    private const DEFAULT_SYMBOLS_TABLE = [
        'RO' => 0,
        'R1' => 1,
        'R2' => 2,
        'R3' => 3,
        'R4' => 4,
        'R5' => 5,
        'R6' => 6,
        'R7' => 7,
        'R8' => 8,
        'R9' => 9,
        'R1O' => 10,
        'R11' => 11,
        'R12' => 12,
        'R13' => 13,
        'R14' => 14,
        'R15' => 15,
        'SCREEN' => 16384,
        'KEYBOARD' => 24576,
        'SP' => 0,
        'LCL' => 1,
        'ARG' => 2,
        'THIS' => 3,
        'THAT' => 4,
    ];

    private array $symbolsTable;

    private static int $variableAddress = 16;

    public function parseFile(string $filePath): string
    {
        $this->symbolsTable = self::DEFAULT_SYMBOLS_TABLE;

        $lines = [];
        $lineNum = 0;
        foreach (file($filePath) as $originalLineNum => $line) {
            $line = trim(str_replace(' ', '', preg_replace(self::COMMENT_REGEX, '', $line)));

            if (!$line)
                continue;

            if (preg_match(self::SYMBOL_REGEX, $line, $matches)) {
                $this->symbolsTable[$matches[1]] = $lineNum;
                continue;
            }

            $lines[]= new Instruction($line, $lineNum, $originalLineNum);
            $lineNum++;
        }

        $assembledContent = '';
        /** @var Instruction $instruction */
        foreach ($lines as $key => $instruction) {
            $assembledContent .= ($key != 0 ? PHP_EOL : '') . $this->getOpcode($instruction);
        }

        return $assembledContent;
    }

    private function getOpcode(Instruction $instruction): string
    {
        if (preg_match(self::A_INSTRUCTION_REGEX, $instruction->text, $matches)) {
            return $this->getAInstructionOpcode($matches[1]);
        } elseif (preg_match(self::C_INSTRUCTION_REGEX, $instruction->text, $matches)) {
            return $this->getCInstructionOpcode($matches[1] ?? '', $matches[2], $matches[3] ?? '');
        } else {
            throw new \Exception("Invalid instruction \"$instruction->text\" on line " . $instruction->getOriginalFileLine() . '.');
        }
    }

    private function getAInstructionOpcode(string $aInstructionValue): string
    {
        if (!preg_match(self::SYMBOLLESS_A_INSTRUCTION_VALUE_REGEX, $aInstructionValue, $matches)) {
            $aInstructionValue = $this->symbolsTable[$aInstructionValue] ?? self::$variableAddress++;
        }

        $string = str_pad(decbin($aInstructionValue), 15, "0", STR_PAD_LEFT);

        return '0'. substr($string, strlen($string) - 15, 15);
    }

    private function getCInstructionOpcode(string $dest, string $comp, string $jmp): string
    {
        $dest = $dest ? substr($dest, 0, -1) : '';
        $jmp = $jmp ? substr($jmp, 1) : '';
        return '111' . self::COMP[$comp] . self::DEST[$dest] . self::JMP[$jmp];
    }
}