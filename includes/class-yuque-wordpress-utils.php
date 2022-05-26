<?php


class Yuque_Wordpress_Utils
{

    public static function returnJson($data = true, $code = 200, $message = '操作成功')
    {
        $res = [];
        $res['code'] = $code;
        $res['message'] = $message;
        $res['data'] = $data;
        wp_die(json_encode($res));
    }

    public static  function objectToArray($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::objectToArray($value);
            }
        }
        return $array;
    }

    public static function getConfigData($optionKey)
    {
        $res = array();
        $configFromDbJson = get_option($optionKey);
        if ($configFromDbJson) {
            $configFromDb = self::objectToArray(json_decode($configFromDbJson));
            if (is_array($configFromDb)) {
                foreach (DEFAULT_CONFIG as $key => $val) {
                    $res[self::toCamelCase($key)] = in_array($key, $configFromDb) ? $configFromDb[$key] : DEFAULT_CONFIG[$key];
                }
            }
        }else{
            foreach (DEFAULT_CONFIG as $key => $val) {
                $res[self::toCamelCase($key)] = DEFAULT_CONFIG[$key];
            }
        }
        return $res;
    }

    /** 驼峰命名转下划线命名
     * @param $str
     * @return string
     */
    public static function toUnderLine($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $str);
        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }

    /** 下划线命名到驼峰命名
     * @param $str
     * @return mixed|string
     */
    private static function toCamelCase($str)
    {
        $array = explode('_', $str);
        $result = $array[0];
        $len = count($array);
        if ($len > 1) {
            for ($i = 1; $i < $len; $i++) {
                $result .= ucfirst($array[$i]);
            }
        }
        return $result;
    }

}
