<?php

namespace Inilim\FuncOther;

class Other
{
    /**
     * Possibles values for the returned string are: "boolean" "integer" "float" "string" "array" "object" "object exception" "enum" "resource" "null" "unknown type" "resource (closed)"
     */
    function gettype($value): string
    {
        $r = \gettype($value);
        return match ($r) {
            'NULL'   => 'null',
            'double' => 'float',
            'object' => (static function ($value) {
                if ($value instanceof \UnitEnum) {
                    return 'enum';
                } elseif ($value instanceof \Throwable) {
                    return 'object exception';
                }
                return 'object';
            })->__invoke($value),

            default  => $r,
        };
    }

    /**
     * @return array{message:string,line:int,code:int,file:string,trace:string|array,class:class-string}
     */
    function getExceptionDetails(\Throwable $e, bool $trace_as_array = false): array
    {
        return [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'trace'   => $trace_as_array ? $e->getTrace() : $e->getTraceAsString(),
            'class'   => \get_class($e),
        ];
    }
}
