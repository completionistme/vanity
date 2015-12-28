<?php

if(!function_exists('array_get')) {
    /**
     * From http://stackoverflow.com/a/14706302
     *
     * @param array $a
     * @param       $path
     * @param null  $default
     * @return array|null
     */
    function array_get(array $a, $path, $default = null)
    {
        $current = $a;
        $p = strtok($path, '.');
        while ($p !== false) {
            if (!isset($current[$p])) {
                return $default;
            }
            $current = $current[$p];
            $p = strtok('.');
        }
        return $current;
    }
}

if(!function_exists('flatten_array')) {
    /**
     * Modification of http://stackoverflow.com/a/10424516
     *
     * @param $a
     * @return array
     */
    function flatten_array($a)
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($a));
        $result = [];
        foreach ($ritit as $leafValue) {
            $keys = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $continuousKeys = [];
            foreach ($keys as $key) {
                if (is_numeric($key)) {
                    break;
                }
                $continuousKeys[] = $key;
            }
            //$result[join('.', $keys)] = $leafValue;
            $dotKeys = join('.', $continuousKeys);
            if (!in_array($dotKeys, $result)) {
                $result[] = $dotKeys;
            }
        }
        return $result;
    }
}

if(!function_exists('dd')) {
    /**
     * dump and die
     *
     * @param $dump
     */
    function dd($dump)
    {
        var_dump($dump);
        die();
    }
}