<?php

declare(strict_types=1);

function test(callable $call) {}


class Test
{
    function func() {}
}

$a = new Test;

test([$a, 'func']);
