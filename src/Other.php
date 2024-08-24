<?php

namespace Inilim\FuncOther;

class Other
{
    /**
     * @return array{message:string,line:int,code:int,file:string,trace_string:string,class_string:class-string}
     */
    function getExceptionDetails(\Throwable $e): array
    {
        return [
            'message'      => $e->getMessage(),
            'line'         => $e->getLine(),
            'code'         => $e->getCode(),
            'file'         => $e->getFile(),
            'trace_string' => $e->getTraceAsString(),
            'class_string' => \get_class($e),
        ];
    }
}
