<?php

if (! function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        $delimiter = '/';
        $escapedPattern = str_replace($delimiter, '\\'.$delimiter, $pattern);
        $result = preg_split($delimiter.$escapedPattern.$delimiter.'u', $string, $limit);

        return $result === false ? false : $result;
    }
}
