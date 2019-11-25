<?php declare(strict_types=1);

namespace wdmg\ai;

/**
 * Multi-layer Neural Network
 *
 * @category        Library
 * @version         0.0.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/neuralnet
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

/**
 * Neural Network definition class
 */
class NeuralNet
{


    const ACTIVATION_FUNC_STEP = 1; // Ступенчатая (пороговая) функция активации
    const ACTIVATION_FUNC_LINR = 2; // Линейная функция активации
    const ACTIVATION_FUNC_SIGM = 3; // Сигмоидная функция активации
    const ACTIVATION_FUNC_TANH = 4; // Гиперболическая функция активации
    const ACTIVATION_FUNC_RELU = 5; // Rectified linear unit (ReLU)
    const ACTIVATION_FUNC_LRELU = 6; // Leaky rectified linear unit (Leaky ReLU)
    const ACTIVATION_FUNC_PRELU = 7; // Parametric rectified linear unit (Parametric ReLU)
    const ACTIVATION_FUNC_RRELU = 8; // Randomized rectified linear unit (Randomized LReLU)

    public $angular = 0.01; // Угловой коэффициент на отрицательном интервале для Leaky ReLU и Randomized LReLU
    public $shared = 0.01; // Угловой коэффициент на положительном интервале для Parametric ReLU

    public function __construct()
    {

    }

    /*
     * Функция активации нейрона в зависимости от порогового значения и типа выбранной функции
     *
     * @param $input integer, значение суммы весов нейрона
     * @param $type integer, тип выбранной функции активации
     * @return integer or boolean, значение возбужденного/не возбуждённого нейрона или false при ошибке
     */
    public function activation($value = 0, $type) {

        $output = false;
        if ($type === self::ACTIVATION_FUNC_STEP) {
            $output = ($value >= 0.5) ? 1 : 0;
        } else if ($type === self::ACTIVATION_FUNC_LINR) {
            $output = ($value > 0) ? 1 : 0;
        } else if ($type === self::ACTIVATION_FUNC_SIGM) {
            $output = (1 / (1 + exp(-$value)));
        } else if ($type === self::ACTIVATION_FUNC_TANH) {
            $output = tanh($value);
        } else if ($type === self::ACTIVATION_FUNC_RELU) {
            $output = max(0, $value);
        } else if ($type === self::ACTIVATION_FUNC_LRELU) {
            $output = ($value > 0) ? max(0, $value) : -max(0, ((1-$value) * $this->angular));
        } else if ($type === self::ACTIVATION_FUNC_PRELU) {
            $output = max(0, $value) + ($this->shared * min(0, $value));
        } else if ($type === self::ACTIVATION_FUNC_RRELU) {

            $angular = rand(-$this->angular, $this->angular);
            if (rand(1, 10) % 2 === 0)
                $angular = ($angular + ((rand((($angular + 1) / 10), (($angular + 1) * 10))) / 100));
            else
                $angular = ($angular + ((rand((($angular - 1) / 10), (($angular - 1) * 10))) / 100));

            $output = ($value > 0) ? max(0, $value) : -max(0, ((1-$value) * $angular));

        }

        return $output;
    }

    /*
     * Функция сравнивает два массива и определяет их схожесть/расхождение
     *
     * @param $array1 array, массив для сравнения
     * @param $array2 array, массив для сравнения
     * @return $threshold integer or float, погрешность для значений с плавающей точкой
     * @return $format boolean, форматирование результирующего значения в %
     * @return string or integer, схожесть/расхождение в условном выражении от 0 до 1 или в %
     * */
    public function conformity($array1, $array2, $threshold = 0, $format = false) {

        $length = count($array1);
        if (is_float($threshold))
            $length = floatval(count($array1));

        $max = $current = 0;
        if (is_float($threshold))
            $max = floatval($current = 0);

        for ($i = 0; $i < $length; $i++) {

            array_map(function($x, $y) use (&$current, $threshold) {

                if (is_float($threshold) && isset($x) && isset($y)) {
                    if (abs((($x - $y) / $y)) < ($threshold * 100)) {

                        if ($x < $y)
                            $current += floatval((($x * 100) / $y) / 100);
                        else
                            $current += floatval((($y * 100) / $x) / 100);
                    }
                } else {
                    $current += intval($x === $y);
                }

            }, $array1, $array2);

            if ($current === $length) {
                return ($format) ? sprintf("%s%%\n", number_format(100, 2)) : 1;
            }

            $max = $max > $current ? $max : $current;
            $current = 0;

            $array1[] = array_shift($array1);
        }

        return ($format) ? sprintf("%s%%\n", number_format(100 * ($max / $length), 2)) : ($max / $length);
    }

    /*
     * Функция субдискретизация (sub-sampling)
     *
     * @param $array array, входной многоуровневый массив значений
     * @param $matrix array, матрица выборки (ширина/высота, например [2, 2] или [3, 3])
     * @param $type string, применяемый фильтр усреднения: average, max, summ (по-умолчанию: max)
     * @param $step integer, шах прохождения (по-умолчанию равен ширине матрицы)
     * @return $strict boolean, проверять на вхождение стека в выборку (если `true` - не помещающиеся
     * в стек игнорируются)
     * @return array or boolean, массив значений или false при ошибке
     */
    function pooling($array = [], $matrix = null, $type = null, $step = null, $strict = true)
    {
        $pool = [];

        if (!is_array($array))
            return false;

        if (!count($array))
            return false;

        if (is_null($matrix))
            return false;

        if (is_array($matrix)) {
            $width = intval($matrix[0]);
            $height = intval($matrix[1]);
        } else {
            $width = 2;
            $height = 2;
        }

        if (is_null($step)) {
            $stepX = $width;
            $stepY = $height;
        } else {
            $stepX = intval($step);
            $stepY = intval($step);
        }

        for ($x = 0; $x <= count($array); $x += $stepX) {
            if (isset($array[$x])) {

                $summary = [];
                for ($y = 0; $y <= count($array[$x]); $y += $stepY) {
                    if (isset($array[$x][$y])) {

                        $sum = [];
                        for ($w = 0; $w < $width; $w++) {
                            for ($k = 0; $k < $height; $k++) {

                                if (isset($array[($x + $k)][($y + $w)]))
                                    $sum[] = $array[($x + $k)][($y + $w)];

                            }
                        }

                        if ($strict && count($array) < ($x + $width)) {
                            break;
                        } else if ($strict && count($array[$x]) < ($y + $width)) {
                            break;
                        } else {

                            if ($type == 'summ')
                                $summary[] = array_sum($sum);
                            elseif ($type == 'average')
                                $summary[] = (array_sum($sum) / count($sum));
                            else
                                $summary[] = max($sum);

                        }
                    }
                }
                $pool[] = $summary;
            }
        }

        return $pool;
    }

    /*
     * Функция дискретного свёртывания
     *
     * @param $array array, массив входных значений
     * @param $matrix array, матричное ядро (фильтр) в виде массива (3x3, 2x2, 5x7 etc.)
     * @param $depth integer, максимальная глубина прохождения
     * @return array or boolean, массив выходных значений или false при ошибке
     */
    public function convolution($array = [], $matrix = null, $depth = 0) {

        $features = [];

        if (!is_array($array))
            return false;

        if (!count($array))
            return false;

        if (is_null($matrix))
            return false;

        $height = intval(count($matrix));
        $width = intval(count($matrix[0]));

        for ($x = 0; $x <= count($array); $x++) {
            if (isset($array[$x])) {

                $conv = [];
                for ($y = 0; $y <= count($array[$x]); $y++) {
                    if (isset($array[$x][$y])) {
                        if (count($array) < ($x + $width)) {
                            break;
                        } else if (count($array[$x]) < ($y + $width)) {
                            break;
                        }

                        $sum = [];
                        for ($w = 0; $w < $width; $w++) {
                            for ($k = 0; $k < $height; $k++) {
                                if (isset($array[($x + $k)][($y + $w)])) {
                                    $value = $array[($x + $k)][($y + $w)];
                                    $sum[] = $value * $matrix[$k][$w];
                                }
                            }
                        }
                        $conv[] = array_sum($sum);

                    }
                }
                $features[] = $conv;
            }
        }

        if ($depth > 0) {
            $depth--;
            return $this->convolution($features, $matrix, $depth);
        }

        return $features;
    }

    /*
     * Функция сжатия (понижения) значений
     * в зависимости от максимального значения в наборе
     *
     * @param $array array, входные значения
     * @param $precision boolean (false) or integer, порог округления значений
     * @param $abs boolean, флаг приведения к абсолютному положительному значению
     * @return array or boolean, массив значений в диапазоне от -1 и до 1 или false при ошибке
     */
    public function downScale($array = [], $precision = false, $abs = false) {

        if (!is_array($array))
            return false;

        $scaled = [];
        foreach ($array as $value) {
            $value = $value / max($array);
            $value = ($precision) ? round($value, $precision) : $value;
            $scaled[] = ($abs) ? abs($value) : $value;
        }

        return $scaled;
    }

    /*
     * Функция нормализации значений массива
     * в зависимости от минимально / максимально допустимого порога значений
     *
     * @param $array integer or array, входное(-ые) значения
     * @param $min integer, минимально допустимый порог
     * @param $max integer, максимально допустимый порог
     * @param $precision boolean (false) or integer, порог округления значений
     * @param $abs boolean, флаг приведения к абсолютному положительному значению
     * @return array or boolean, массив значений в диапазоне от -1 и до 1 или false при ошибке
     * */
    public function normalize($array = null, $min = 0, $max = 255, $precision = false, $abs = false) {

        if ($min >= $max)
            return false;

        if (is_array($array)) {
            $normalized = [];
            foreach ($array as $value) {
                $value = ($value - $min) / $max;
                $value = ($precision) ? round($value, $precision) : $value;
                $normalized[] = ($abs) ? abs($value) : $value;
            }
            return $normalized;
        } else if (is_int($array)) {
            $array = ($array - $min) / $max;
            $array = ($precision) ? round($array, $precision) : $array;
            return ($abs) ? abs($array) : $array;
        } else {
            return false;
        }

    }

    /*
     * Функция упаковки нескольких массивов
     *
     * @param $array1 array, входные значения 1-го массива
     * @param $array2 array, входные значения 2-го массива
     * @return array or boolean, новый массив значений или false при ошибке
     */
    function pack($array1, $array2) {

        if (!is_array($array1) || !is_array($array2))
            return false;

        if (count($array1) != count($array2))
            return false;

        $args = func_get_args();
        $last = array_pop($args);

        if (is_array($last))
            $args[] = $last;

        $counts = array_map(function ($array) {
            return count($array);
        }, $args);

        $count = ($last) ? min($counts) : max($counts);

        $packed = [];
        for ($x = 0; $x < $count; $x++) {
            for ($y = 0; $y < count($args); $y++) {
                $value = (isset($args[$y][$x])) ? $args[$y][$x] : 0;
                $packed[$x][$y] = $value;
            }
        }
        return $packed;
    }

    /*
     * Функция рассчитывает расхождение (дистанцию) между значениями массива
     * по одному из выбранных алгоритмов
     *
     * @param $array1 array, входные значения 1-го массива
     * @param $array2 array, входные значения 2-го массива
     * @param $algo string, идентификатор алгоритма или его название ('euclidean', 'manhattan', 'chebyshev', 'hamming')
     * @param $abs boolean, флаг если нужно вернуть только положительное значение
     * @return integer or boolean, дистанция или false при ошибке
     */
    public function distance($array1, $array2, $algo = 'euclidean', $abs = false) {

        if (!is_array($array1) || !is_array($array2))
            return false;

        if (count($array1) != count($array2))
            return false;

        if ($algo == 'euclidean' || $algo == 1) {
            $distance = 0;
            for ($i = 0; $i < count($array1); $i++) {
                $distance += pow(($array2[$i] - $array1[$i]), 2);
            }
            return ($abs) ? abs(sqrt($distance)) : sqrt($distance);
        } else if ($algo == 'manhattan' || $algo == 2) {
            $distance = 0;
            for ($i = 0; $i < count($array1); $i++) {
                $distance += abs($array1[$i] - $array2[$i]);
            }
            return $distance;
        } else if ($algo == 'chebyshev' || $algo == 3) {
            $distance = [];
            for ($i = 0; $i < count($array1); $i++) {
                $distance[$i] = abs($array1[$i] - $array2[$i]);
            }
            return max($distance);
        } else if ($algo == 'hamming' || $algo == 4) {
            $distance = array_diff_assoc($array1, $array2);
            return count($distance);
        } else {
            $distance = 0;
            $packed = $this->pack($array1, $array2);
            foreach($packed as $value) {
                $distance = array_reduce($value, function ($summ, $value) {
                    $summ -= $value;
                    return $summ;
                },0);
            }
            return ($abs) ? abs($distance) : $distance;
        }
    }

    /*
     * Функция инвертирует значения массива
     *
     * @param $array integer or array, входной массив
     * @return array or null, массив значений или false при ошибке
     */
    public function invert($array) {

        if (!is_array($array))
            return false;

        if (empty($array))
            return null;

        for ($x = 0; $x < count($array); $x++) {

            if (empty($array[$x]))
                break;

            for ($y = 0; $y < count($array[$x]); $y++) {

                if (empty($array[$x][$y]))
                    break;

                $array[$x][$y] = ($array[$x][$y] <= 0) ? abs($array[$x][$y]) : -$array[$x][$y];
            }
        }

        return $array;
    }

    /*
     * Функция возвращает первый ключ массива
     * или значение по данному ключу
     *
     * @param $array integer or array, входной массив
     * @param $instance boolean, флаг нужно ли вернуть значение вместо индекса
     * @return array or null, индекс массива, значение массива или false при ошибке
     */
    public function firstKey($array, $instance = false) {

        $key = null;
        if (!function_exists('array_key_first')) {
            if (!empty($array))
                $key = key(array_slice($array, 1, 1, true));
        } else {
            $key = array_key_first($array);
        }

        if (!is_null($key) && $instance)
            return $array[$key];

        return $key;
    }

    /*
     * Функция возвращает последний ключ массива
     * или значение по данному ключу
     *
     * @param $array integer or array, входной массив
     * @param $instance boolean, флаг нужно ли вернуть значение вместо индекса
     * @return array or null, индекс массива, значение массива или false при ошибке
     */
    public function lastKey($array, $instance = false) {

        $key = null;
        if (!function_exists('array_key_last')) {
            if (!empty($array))
                $key = key(array_slice($array, -1, 1, true));
        } else {
            $key = array_key_last($array);
        }

        if (!is_null($key) && $instance)
            return $array[$key];

        return $key;
    }

    /*
     * Функция переворачивает массив по вертикали
     *
     * @param $array integer or array, входной массив
     * @return array or null, массив значений или false при ошибке
     */
    public function flipVertical($array) {
        return (!empty($array)) ? array_reverse($array) : null;
    }

    /*
     * Функция переворачивает массив по горизонтали
     *
     * @param $array integer or array, входной массив
     * @return array or null, массив значений или false при ошибке
     */
    public function flipHorizontal($array) {

        if (!is_array($array))
            return false;

        if (empty($array))
            return null;

        for ($x = 0; $x < count($array); $x++) {

            if (empty($array[$x]))
                break;

            $array[$x] = array_reverse($array[$x]);
        }

        return $array;
    }

    /*
     * Функция поворачивает массив на 180°
     *
     * @return array or null, массив значений или false при ошибке
     */
    public function flip($array) {
        $array = $this->flipVertical($array);
        return $this->flipHorizontal($array);
    }

    /*
     * Вспомогательная функция: отражает массив значений пикселов по вертикали
     *
     * @return array or null, массив значений или false при ошибке
     */
    public function imageFlipVertical($array) {
        return $this->flipHorizontal($array);
    }

    /*
     * Вспомогательная функция: отражает массив значений пикселов по горизонтали
     *
     * @return array or null, массив значений или false при ошибке
     */
    public function imageFlipHorizontal($array) {
        return $this->flipVertical($array);
    }

    /*
     * Вспомогательная функция: поворачивает массив значений пикселов на 180°
     *
     * @return array or null, массив значений или false при ошибке
     */
    public function imageFlip($array) {
        $array = $this->imageFlipVertical($array);
        return $this->imageFlipHorizontal($array);
    }

}