<?php
/**
 * 62进制转换成10进制
 */
function b62to10($str)
{
    $from = 62;
    $str = strval($str);
    $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $len = strlen($str);
    $dec = 0;
    for($i = 0; $i < $len; $i++) {
        $pos = strpos($dict, $str[$i]);
        $dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
    }
    return $dec;
}
/**
 * 10进制转换成62进制
 */
function b10to62($num)
{
    $to = 62;
    $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $ret = '';
    do {
        $ret = $dict[bcmod($num, $to)] . $ret; //bcmod取得高精确度数字的余数。
        $num = bcdiv($num, $to);  //bcdiv将二个高精确度数字相除。
    } while ($num > 0);
    return $ret;
}
?>
