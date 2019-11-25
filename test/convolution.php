<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);

require "../src/NeuralNet.php";

$network = new \wdmg\ai\NeuralNet();

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


$matrix = [
    [0, -1, 0],
    [-1, 6, -1],
    [0, -1, 0]
];

$array[0] = $network->convolution($array[0], $matrix, 2);
$array[1] = $network->convolution($array[1], $matrix, 2);
$array[2] = $network->convolution($array[2], $matrix, 2);

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

echo "<h3>Convolution (RGB):</h3>";
echo "<b>Input:</b><br/>";
echo "<img src='data:image/png;base64,".base64_encode($orig_data)."'>";
echo "<br/>";
echo "<b>Matrix:</b><br/>";
foreach ($matrix as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Depth:</b> 3";
echo "<br/>";
echo "<b>Output:</b><br/>";
echo "<img src='data:image/png;base64,".base64_encode($conv_data)."'>";
echo "<br/>";

?>