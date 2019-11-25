<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);

require "../src/NeuralNet.php";

$network = new \wdmg\ai\NeuralNet();

$array = [
    [8.3, 3, 5.5, -9],
    [-4, 7, -2, 4],
    [6, -5.2, 2, 1],
    [-1.2, 7, -3, 6.2],
];

echo "<h2>Array invert:</h2>";
echo '<h3>$network->invert($array)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->invert($array) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";

$array1 = [-1.2, 7, -3, 6.2];
$array2 = [8.3, 3, 5.5, 9];
echo "<h2>Array packaging:</h2>";
echo '<h3>$network->pack($array1, $array2)</h3>';
echo "<b>Input (\$array1):</b><br/>";

foreach ($array1 as $value) {
    echo "[$value] ";
}
echo "<br/>";

echo "<br/>";
echo "<b>Input (\$array2):</b><br/>";

foreach ($array2 as $value) {
    echo "[$value] ";
}
echo "<br/>";
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->pack($array1, $array2) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";


$array1 = [5, 6, 7];
$array2 = [8, 9, 7];
echo "<h2>Array distance:</h2>";
echo '<h3>$network->distance($array1, $array2)</h3>';
echo "<b>Input (\$array1):</b><br/>";

foreach ($array1 as $value) {
    echo "[$value] ";
}
echo "<br/>";

echo "<br/>";
echo "<b>Input (\$array2):</b><br/>";

foreach ($array2 as $value) {
    echo "[$value] ";
}
echo "<br/>";

echo "<br/>";
echo "<b>Output (Euclidean):</b> ";
echo "[".$network->distance($array1, $array2, 'euclidean')."] ";
echo "<br/>";
echo "<b>Output (Manhattan):</b> ";
echo "[".$network->distance($array1, $array2, 'manhattan')."] ";
echo "<br/>";
echo "<b>Output (Chebyshev):</b> ";
echo "[".$network->distance($array1, $array2, 'chebyshev')."] ";
echo "<br/>";
echo "<b>Output (Hamming):</b> ";
echo "[".$network->distance($array1, $array2, 'hamming')."] ";
echo "<br/>";
echo "<b>Output:</b> ";
echo "[".$network->distance($array1, $array2, false)."] ";
echo "<br/>";
echo "<br/>";

echo "<h2>Array flip:</h2>";
echo '<h3>$network->flipHorizontal($array)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->flipHorizontal($array) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";

echo '<h3>$network->flipVertical($array)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->flipVertical($array) as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<br/>";

echo '<h3>$network->flip($array)</h3>';
echo "<b>Input:</b><br/>";
foreach ($array as $line) {
    foreach ($line as $value) {
        echo "[$value] ";
    }
    echo "<br/>";
}
echo "<br/>";
echo "<b>Output:</b><br/>";
foreach ($network->flip($array) as $line) {
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

$input = $array;
$out1[0] = $network->imageFlipHorizontal($input[0]);
$out1[1] = $network->imageFlipHorizontal($input[1]);
$out1[2] = $network->imageFlipHorizontal($input[2]);

$gd = imagecreatetruecolor(count($out1[0]), count($out1[0][0]));
for ($x = 0; $x <= $width; $x++) {
    for ($y = 0; $y <= $height; $y++) {
        if (isset($out1[0][$x][$y]) && isset($out1[1][$x][$y]) && isset($out1[2][$x][$y]))
            imagesetpixel($gd, $x, $y, imagecolorallocate($gd, round($out1[0][$x][$y] * 255), round($out1[1][$x][$y] * 255), round($out1[2][$x][$y] * 255)));
    }
}

ob_start();
imagepng($gd);
$new_data1 = ob_get_contents();
ob_end_clean();

$input = $array;
$out2[0] = $network->imageFlipVertical($input[0]);
$out2[1] = $network->imageFlipVertical($input[1]);
$out2[2] = $network->imageFlipVertical($input[2]);

$gd = imagecreatetruecolor(count($out2[0]), count($out2[0][0]));
for ($x = 0; $x <= $width; $x++) {
    for ($y = 0; $y <= $height; $y++) {
        if (isset($out2[0][$x][$y]) && isset($out2[1][$x][$y]) && isset($out2[2][$x][$y]))
            imagesetpixel($gd, $x, $y, imagecolorallocate($gd, round($out2[0][$x][$y] * 255), round($out2[1][$x][$y] * 255), round($out2[2][$x][$y] * 255)));
    }
}

ob_start();
imagepng($gd);
$new_data2 = ob_get_contents();
ob_end_clean();

$input = $array;
$out3[0] = $network->imageFlip($input[0]);
$out3[1] = $network->imageFlip($input[1]);
$out3[2] = $network->imageFlip($input[2]);

$gd = imagecreatetruecolor(count($out3[0]), count($out3[0][0]));
for ($x = 0; $x <= $width; $x++) {
    for ($y = 0; $y <= $height; $y++) {
        if (isset($out3[0][$x][$y]) && isset($out3[1][$x][$y]) && isset($out3[2][$x][$y]))
            imagesetpixel($gd, $x, $y, imagecolorallocate($gd, round($out3[0][$x][$y] * 255), round($out3[1][$x][$y] * 255), round($out3[2][$x][$y] * 255)));
    }
}

ob_start();
imagepng($gd);
$new_data3 = ob_get_contents();
ob_end_clean();

echo "<h2>Flip image (RGB):</h2>";
echo "<b>Input:</b><br/>";
echo "<img src='data:image/png;base64,".base64_encode($orig_data)."'>";
echo "<br/>";
echo "<br/>";
echo '<b>Output: $network->imageFlipHorizontal($array)</b><br/>';
echo "<img src='data:image/png;base64,".base64_encode($new_data1)."'>";
echo "<br/>";
echo "<br/>";
echo '<b>Output: $network->imageFlipVertical($array)</b><br/>';
echo "<img src='data:image/png;base64,".base64_encode($new_data2)."'>";
echo "<br/>";
echo "<br/>";
echo '<b>Output: $network->imageFlip($array)</b><br/>';
echo "<img src='data:image/png;base64,".base64_encode($new_data3)."'>";
echo "<br/>";


?>