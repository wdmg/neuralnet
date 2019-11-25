<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);

require "../src/NeuralNet.php";

$network = new \wdmg\ai\NeuralNet();

$array = [
    [8, 3, 5, 9],
    [4, 7, 2, 4],
    [6, 5, 2, 1],
    [1, 7, 3, 6],
];
$matrix = [2, 2];

echo "<h2>Pooling (sub-sampling):</h2>";
echo '<h3>$network->pooling($array, $matrix, type: null, step: null)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Matrix:</b><br/>";
foreach ([$matrix] as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->pooling($array, $matrix, null, null) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";

$array = [
    [2, 3, 1, 4],
    [1, 2, 0, 3],
    [0, 2, 2, 7],
    [1, 2, 3, 5],
];
$matrix = [2, 2];

echo '<h3>$network->pooling($array, $matrix, type: \'average\', step: 1)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Matrix:</b><br/>";
foreach ([$matrix] as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->pooling($array, $matrix, 'average', 1) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";


$array = [
    [1, 1, 2, 4, 2, 4, 2, 4, 2, 0],
    [4, 1, 2, 1, 1, 4, 2, 1, 0, 4],
    [3, 2, 1, 2, 1, 0, 1, 0, 1, 1],
    [3, 2, 1, 1, 1, 0, 0, 2, 1, 2],
    [1, 2, 3, 2, 1, 5, 3, 2, 3, 4],
    [3, 2, 1, 2, 0, 1, 1, 2, 1, 0],
    [3, 2, 1, 0, 1, 0, 1, 2, 1, 3],
    [3, 2, 0, 2, 1, 0, 1, 1, 1, 0],
    [1, 0, 3, 2, 3, 4, 3, 2, 1, 4],
    [0, 2, 1, 2, 1, 0, 1, 2, 1, 1],
];
$matrix = [3, 3];

echo '<h3>$network->pooling($array, $matrix, type: \'summ\', step: 2)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Matrix:</b><br/>";
foreach ([$matrix] as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->pooling($array, $matrix, 'summ', 2) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";






$array = [];
$image = "images/warbler-bird.png";
$source = imagecreatefrompng($image);
list($width, $height) = getimagesize($image);
for ($x = 1; $x <= $width; $x++) {
    $red = [];
    $green = [];
    $blue = [];
    for ($y = 1; $y <= $height; $y++) {
        $rgb = imagecolorat($source, $x-1, $y-1);

        $r = ($rgb >> 16) & 255;
        $g = ($rgb >> 8) & 255;
        $b = $rgb & 255;

        $value1 = abs($r / 255);
        $value2 = abs($g / 255);
        $value3 = abs($b / 255);

        if (is_null($value1))
            $value1 = 0;

        if (is_null($value2))
            $value2 = 0;

        if (is_null($value3))
            $value3 = 0;

        $red[] = $value1;
        $green[] = $value2;
        $blue[] = $value3;
    }
    $array[0][] = $red;
    $array[1][] = $green;
    $array[2][] = $blue;
}

$gd = imagecreatetruecolor(count($array[0]), count($array[0][0]));
for ($x = 0; $x <= $width; $x++) {
    for ($y = 0; $y <= $height; $y++) {
        if (isset($array[0][$x][$y]) && isset($array[1][$x][$y]) && isset($array[2][$x][$y]))
            imagesetpixel($gd, $x, $y, imagecolorallocate($gd, round($array[0][$x][$y] * 255), round($array[1][$x][$y] * 255), round($array[2][$x][$y] * 255)));
    }
}

ob_start();
imagepng($gd);
$orig_data = ob_get_contents();
ob_end_clean();


$matrix = [5, 7];
$array[0] = $network->pooling($array[0], $matrix, 'max', 2);
$array[1] = $network->pooling($array[1], $matrix, 'max', 2);
$array[2] = $network->pooling($array[2], $matrix, 'max', 2);

$gd = imagecreatetruecolor(count($array[0]), count($array[0][0]));
for ($x = 0; $x <= $width; $x++) {
    for ($y = 0; $y <= $height; $y++) {
        if (isset($array[0][$x][$y]) && isset($array[1][$x][$y]) && isset($array[2][$x][$y]))
            imagesetpixel($gd, $x, $y, imagecolorallocate($gd, round($array[0][$x][$y] * 255), round($array[1][$x][$y] * 255), round($array[2][$x][$y] * 255)));
    }
}

ob_start();
imagepng($gd);
$conv_data = ob_get_contents();
ob_end_clean();

echo "<h2>Sub-sampling (RGB):</h2>";
echo '<h3>$network->pooling($array, $matrix, type: \'max\', step: 2)</h3>';
echo "<b>Input:</b><br/>";
echo "<img src='data:image/png;base64,".base64_encode($orig_data)."'>";
echo "<br/>";
echo "<b>Matrix:</b><br/>";
foreach ([$matrix] as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
echo "<img src='data:image/png;base64,".base64_encode($conv_data)."'>";
echo "<br/>";

?>