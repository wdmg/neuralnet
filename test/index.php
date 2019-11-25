<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);

require "../src/NeuralNet.php";

$network = new \wdmg\ai\NeuralNet();

$array = [
    [1, 1, 2, 4, 2, 4, 2, 4, 2, 4],
    [4, 2, 2, 1, 1, 4, 2, 1, 1, 4],
    [3, 2, 1, 2, 1, 0, 1, 2, 1, 0],
    [3, 2, 1, 2, 1, 0, 1, 2, 1, 0],
    [1, 2, 3, 2, 3, 4, 3, 2, 3, 4],
    [3, 2, 1, 2, 1, 0, 1, 2, 1, 0],
    [3, 2, 1, 2, 1, 0, 1, 2, 1, 0],
    [3, 2, 1, 2, 1, 0, 1, 2, 1, 0],
    [1, 2, 3, 2, 3, 4, 3, 2, 3, 4],
    [3, 2, 1, 2, 1, 0, 1, 2, 1, 0],
];
/*
$array = [
    [1, 1, 2, 2],
    [1, 1, 2, 2],
    [3, 3, 4, 4],
    [3, 3, 4, 4]
];*/
/*
$output = $network->downsampling($array, 2);
*/

echo "<br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";


echo "<br/>";
//foreach ($output = $network->downsampling($array) as $line) {
foreach ($output as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";

?>