<?php

use Inilim\FuncOther\Other;

if (!\function_exists('_other')) {
    function _other(): Other
    {
        static $o = new Other;
        return $o;
    }
}
