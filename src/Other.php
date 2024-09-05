<?php

declare(strict_types=1);

namespace Inilim\FuncOther;

class Other
{
    function isEnum($value): bool
    {
        if (\is_object($value)) {
            return $value instanceof \UnitEnum;
        } elseif (\is_string($value)) {
            return \enum_exists($value);
        }
        return false;
    }

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

    /**
     * @template T
     * @param T $default
     * @return mixed|T
     */
    function tryCallMethod(object $obj, string $method_name, array $args = [], $default = null)
    {
        try {
            // $result = $obj->$method_name(...$args);
            $result = \call_user_func([$obj, $method_name], ...$args);
        } catch (\Throwable) {
            return $default;
        }
        return $result;
    }

    function prepareArrayForSerializeRecursive(array &$value): void
    {
        \array_walk_recursive($value, function (&$sub_val) {
            if (\is_object($sub_val)) {
                $sub_val = $this->prepareObjForSerialize($sub_val);
            } elseif (\is_resource($sub_val)) {
                $sub_val = \print_r($sub_val, true);
            }
        });
    }

    /**
     * @return mixed
     */
    function prepareObjForSerialize(object $obj)
    {
        if ($obj instanceof \JsonSerializable) {
            $v = $obj->jsonSerialize();
            // jsonSerialize return mixed
            if (\is_array($v)) {
                $this->prepareArrayForSerializeRecursive($v);
                return $v;
            } else {
                $this->prepareArrayForSerializeRecursive([$v]);
                return $v[0];
            }
        }

        if ($obj instanceof \Serializable) {
            $v = $this->tryCallMethod($obj, '__serialize');
            if (\is_array($v)) {
                $this->prepareArrayForSerializeRecursive($v);
                return $v;
            }
        }

        if ($obj instanceof \UnitEnum) {
            return $obj::class . '::' . $obj->name;
        }

        if (\method_exists($obj, 'toArray')) {
            $v = $this->tryCallMethod($obj, 'toArray');
            if (\is_array($v)) {
                $this->prepareArrayForSerializeRecursive($v);
                return $v;
            }
        }

        $v = (array)$obj;
        $this->prepareArrayForSerializeRecursive($v);
        return $v;
    }

    // ------------------------------------------------------------------
    // 
    // ------------------------------------------------------------------

    /**
     * @param object|class-string $class_of_obj
     */
    function getReflectionClass(object|string $class_of_obj, bool $throw = false): ?\ReflectionClass
    {
        if (\is_string($class_of_obj)) {
            if (!\class_exists($class_of_obj)) {
                return $throw
                    ? throw new \ReflectionException('class not found ' . $class_of_obj)
                    : null;
            }
        }
        return new \ReflectionClass($class_of_obj);
    }

    /**
     * @param object|class-string|\ReflectionClass $class_or_obj_or_ref
     * @param string[] $except_methods
     * @return \ReflectionMethod[]|array{}
     */
    function getRefMethodsFromObjOrClass(
        object|string $class_or_obj_or_ref,
        array $except_methods          = [],
        bool $throw                    = false,
        bool $except_magic_methods     = false,
        bool $except_private_methods   = false,
        bool $except_protected_methods = false,
        bool $except_public_methods    = false,
        bool $except_parent_methods    = false,
    ): array {

        if ($class_or_obj_or_ref instanceof \ReflectionClass) {
            $ref = $class_or_obj_or_ref;
        } else {
            $ref = $this->getReflectionClass($class_or_obj_or_ref, $throw);
        }
        $methods = $ref->getMethods();

        if (!$methods) {
            return [];
        }

        if ($methods && $except_parent_methods) {
            $refParent = $ref->getParentClass();
            if ($refParent) {
                $parent_class = $refParent->name;
                $methods = \array_filter($methods, static fn($m) => $m->class !== $parent_class);
            }
        }
        unset($refParent, $parent_class, $ref);

        if ($methods && $except_private_methods) {
            $methods = \array_filter($methods, static fn($m) => !$m->isPrivate());
        }

        if ($methods && $except_protected_methods) {
            $methods = \array_filter($methods, static fn($m) => !$m->isProtected());
        }

        if ($methods && $except_public_methods) {
            $methods = \array_filter($methods, static fn($m) => !$m->isPublic());
        }

        if ($methods && $except_methods) {
            $methods = \array_filter($methods, static fn($m) => !\in_array($m->name, $except_methods));
        }

        if ($methods && $except_magic_methods) {
            $magic_methods = \_data()->magicMethodsAsArray();
            $methods = \array_filter($methods, static fn($m) => !\in_array($m->name, $magic_methods));
            unset($magic_methods);
        }

        return $methods;
    }

    /**
     * @param object|class-string|\ReflectionClass $class_or_obj_or_ref
     * @param string[] $except_methods
     * @return string[]|array{}
     */
    function getNameMethodsFromObjOrClass(
        object|string $class_or_obj_or_ref,
        array $except_methods          = [],
        bool $throw                    = false,
        bool $except_magic_methods     = false,
        bool $except_private_methods   = false,
        bool $except_protected_methods = false,
        bool $except_public_methods    = false,
        bool $except_parent_methods    = false,
    ): array {
        return \array_column($this->getRefMethodsFromObjOrClass(
            class_or_obj_or_ref: $class_or_obj_or_ref,
            except_methods: $except_methods,
            throw: $throw,
            except_magic_methods: $except_magic_methods,
            except_private_methods: $except_private_methods,
            except_protected_methods: $except_protected_methods,
            except_public_methods: $except_public_methods,
            except_parent_methods: $except_parent_methods,
        ), 'name');
    }

    /**
     * @param object|class-string|\ReflectionClass $class_or_obj_or_ref
     * @return \ReflectionAttribute[]|array{}|null
     */
    function getRefAttrClass(object|string $class_or_obj_or_ref, bool $throw = false): ?array
    {
        if ($class_or_obj_or_ref instanceof \ReflectionClass) {
            $ref = $class_or_obj_or_ref;
        } else {
            $ref = $this->getReflectionClass($class_or_obj_or_ref, $throw);
        }
        return $ref?->getAttributes();
    }
}
