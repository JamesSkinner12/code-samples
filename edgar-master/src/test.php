<?php


require_once(__DIR__ . "/../vendor/autoload.php");
ini_set('memory_limit', '1028M');

$data = new \Edgar\Report("https://sec.gov/Archives/edgar/data/320193/000032019319000010/aapl-20181229.xml");
$start = microtime(true);

$collection = $data->collection();
$end = microtime(true);
$diff = $end - $start;

$count = count($collection);
echo $count;
//var_dump($collection);
//echo "Total time taken to process collection: " . $diff . " seconds\n";
