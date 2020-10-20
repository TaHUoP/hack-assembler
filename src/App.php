<?php

namespace TaHUoP;

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
            ->addArgument('outputFilePath', InputArgument::REQUIRED, 'Path to binary file')
            ->addArgument('memoryLimit', InputArgument::OPTIONAL, 'PHP memory limit. Unlimited by default')
            ->setCode([$this, 'main']);
        $this->parser = $parser;
    }

    public function main(InputInterface $input, OutputInterface $output): void
    {
        try {
            ini_set('memory_limit', $input->getArgument('memoryLimit') ?? -1);

            $assembledFileContent = $this->parser->parseFile($input->getArgument('inputFilePath'));

            $outputFilePath = $input->getArgument('outputFilePath');
            if (file_put_contents($outputFilePath, $assembledFileContent)) {
                $output->writeln("File $outputFilePath was successfully built.");
            } else {
                $output->writeln("<fg=red>Unable to write to file $outputFilePath.</>");
            }
        } catch (\Exception $e) {
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
        }
    }
}
