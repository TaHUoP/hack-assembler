<?php

namespace TaHUoP\HackAsm;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class App extends SingleCommandApplication
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        parent::__construct();
        $this
            ->addArgument('inputFilePath', InputArgument::REQUIRED, 'Path to .asm file')
            ->addArgument('outputFilePath', InputArgument::OPTIONAL, 'Path to binary file')
            ->addArgument('memoryLimit', InputArgument::OPTIONAL, 'PHP memory limit. Unlimited by default')
            ->setCode([$this, 'main']);
        $this->parser = $parser;
    }

    public function main(InputInterface $input, OutputInterface $output): void
    {
        try {
            ini_set('memory_limit', $input->getArgument('memoryLimit') ?? -1);

            $inputFilePath = $input->getArgument('inputFilePath');
            $assembledFileContent = $this->parser->parseFile($inputFilePath);

            $outputFilePath = $input->getArgument('outputFilePath') ?? $this->getOutputFilePath($inputFilePath);
            if (file_put_contents($outputFilePath, $assembledFileContent)) {
                $output->writeln("File $outputFilePath was successfully built.");
            } else {
                $output->writeln("<fg=red>Unable to write to file $outputFilePath.</>");
            }
        } catch (Exception $e) {
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
        }
    }

    private function getOutputFilePath(string $inputFilePath): string
    {
        $pathInfo = pathinfo($inputFilePath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.hack';
    }
}
