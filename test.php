<?php

require_once __DIR__ . '/vendor/autoload.php';

use Inilim\Dump\Dump;

Dump::init();

class ParentClass
{
    function dawdwd()
    {
        return '123123';
    }
    function func() {}

    function parent_pub() {}
    protected function parent_prot() {}
    private function parent_priv() {}

    static function parent_static_pub() {}
    static protected function parent_static_prot() {}
    static private function parent_static_priv() {}
}


$obj = new ParentClass;


$res = ;

// $res = _other()->tryCallMethod($obj, 'dawdwd');
dde($res);





de();

#[gegege]
#[gegege]
#[gegege]
class ChildClass extends ParentClass
{
    function func() {}

    function child_pub() {}
    protected function child_prot() {}
    private function child_priv() {}

    static function child_static_pub() {}
    static protected function child_static_prot() {}
    static private function child_static_priv() {}
}



// $obj = new ChildClass;
// $class = \Inilim\FuncOther\Other::class;


$m = \_other()->getRefAttrClass(ChildClass::class);

// $m = \_other()->getNameMethodsFromObjOrClass($m);
de($m);
