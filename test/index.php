<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once 'src/my365/PDFGenerator.php';

$input = __DIR__.'/fixture/booklet.pdf';

$g = new PDFGenerator($input, null, array(
    'author' => 'Jonas',
    'title' => 'PDF Tools',
    'subject' => 'PDF Tools demo',
));

$g->generate(__DIR__.'/booklet_recto-verso.pdf', PDFGenerator::NONE, false, PDFGenerator::ODD);
$g->generate(__DIR__.'/booklet_recto.pdf', PDFGenerator::EVEN, false, PDFGenerator::ALL);
$g->generate(__DIR__.'/booklet_verso.pdf', PDFGenerator::ODD, true);


