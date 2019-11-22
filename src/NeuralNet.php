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
     * Функция активации нейрона
     * в зависимости от порогового значения и типа выбранной функции
     *
     * @param $input integer, значение суммы весов нейрона
     * @param $type integer, тип выбранной функции активации
     * @return integer or boolean, значение возбужденного/не возбуждённого
     * нейрона или false при ошибке
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
            //return max(0, $value) + ($this->angular * min(0, $value));
            $output = max(0, $value) + ($this->shared * min(0, $value));
        } else if ($type === self::ACTIVATION_FUNC_RRELU) {

            $angular = rand($this->angular, $this->angular);
            if(rand(1, 10) % 2 === 0)
                $angular = ($angular + ((rand((($angular + 1) / 10), (($angular + 1) * 10))) / 100));
            else
                $angular = ($angular + ((rand((($angular - 1) / 10), (($angular - 1) * 10))) / 100));

            $output = ($value > 0) ? max(0, $value) : -max(0, ((1-$value) * $angular));

        }
        return $output;
    }

    /*
     * Функция сравнивает два массива
     * и определяет их схожесть/расхождение в условном выражении от 0 до 1 или в %
     *
     * @param $array1 array, массив для сравнения
     * @param $array2 array, массив для сравнения
     * @return $threshold integer or float, погрешность для значений с плавающей точкой
     * @return $format boolean, форматирование результирующего значения в %
     * @return string or integer, текущий статус сети
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
     * Функция объединения (усреднения) значений массива
     *
     * @param $array array, входной многоуровневый массив значений
     * @param $depth integer, глубина усреднения
     * @param $filter string, применяемый фильтр усреднения (summ, middle, max),
     * если не установлен, функция возвращает выборку усреднения
     * @return array or boolean, массив усредненных значений или
     * false при ошибке
     */
    public function pooling($array = [], $depth = 1, $filter = null) {

        $pool = [];
        $summary = [];

        if (is_null($depth))
            return false;

        if (!is_array($array))
            return false;

        if (!count($array))
            return false;

        $height = count($array);
        foreach ($array as $x => $value) {

            if (!is_array($array[$x]))
                return false;

            if (!count($array[$x]))
                return false;

            $width = count($array[$x]);
            for ($y = 0; $y < count($array[$x]); $y++) {

                if ($y < round($height/$depth) && $x < round($width/$depth)) {
                    if ($array[$x][$y])
                        $summary[0][] = $array[$x][$y];
                }

                if ($y >= round($height/$depth) && $x < round($width/$depth)) {
                    if ($array[$x][$y])
                        $summary[1][] = $array[$x][$y];
                }

                if ($y < round($height/$depth) && $x >= round($width/$depth)) {
                    if ($array[$x][$y])
                        $summary[2][] = $array[$x][$y];
                }

                if ($y >= round($height/$depth) && $x >= round($width/$depth)) {
                    if ($array[$x][$y])
                        $summary[3][] = $array[$x][$y];
                }
            }
        }

        foreach ($summary as $sum) {
            if ($filter == 'summ')
                $pool[] = array_sum($sum);
            elseif ($filter == 'middle')
                $pool[] = (array_sum($sum) / count($sum));
            elseif ($filter == 'max')
                $pool[] = max($sum);
            else
                $pool[] = $sum;
        }

        return $pool;

    }

    /*
     * Функция сжатия (понижения) значений
     * в зависимости от максимального значения в наборе
     *
     * @param $input array, входные значения
     * @param $precision boolean (false) or integer, порог округления значений
     * @param $abs boolean, флаг приведения к абсолютному положительному значению
     * @return array or boolean, массив значений в диапазоне от -1 и до 1 или
     * false при ошибке
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
     * Функция нормализации значений
     * в зависимости от минимально / максимально допустимого порога значений
     *
     * @param $array integer or array, входное(-ые) значения
     * @param $min integer, минимально допустимый порог
     * @param $max integer, максимально допустимый порог
     * @param $precision boolean (false) or integer, порог округления значений
     * @param $abs boolean, флаг приведения к абсолютному положительному значению
     * @return array or boolean, массив значений в диапазоне от -1 и до 1 или
     * false при ошибке
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

}