# Hack assembler
Implementation of the 6th project from [nand2tetris](http://www.nand2tetris.org/) course, assembler for hack assembly language.

## Installation
Clone the repository and run the following command from the root folder:

    composer install
For guidelines on composer installation visit https://getcomposer.org/download/

## Usage
To compile a file run:

    hackasm <inputFilePath> [<outputFilePath>]
> ! Make sure you have write privileges for the path specified in \<outputFilePath>.
>
To get detailed usage manual run:

    hackasm --help