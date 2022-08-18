<?php
if (!function_exists('format_human_duration')) {
    function format_human_duration($seconds) {
        $units = [
            ['label' => '秒', 'unit' => 60],
            ['label' => '分钟', 'unit' => 60],
            ['label' => '小时', 'unit' => 24,],
            ['label' => '天', 'unit' => 365,],
            ['label' => '年', 'unit' => 1,],
        ];
        $return = [];
        do {
            $unit = array_shift($units);
            $first = intval(floor($seconds / $unit['unit']));
            $second = $seconds % $unit['unit'];
            if ($second > 0) {
                array_unshift($return, $unit['label']);
                array_unshift($return, $second);
            }
            //$old_unit = $unit['label'];
            //if (isset($units[0]) && $units[0]['unit']>=$first) {
            //    break;
            //}
            $seconds = $first;
        }while($seconds > 0 && !empty($units));
        return implode('', $return);
    }
}
