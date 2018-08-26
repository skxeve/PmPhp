<?php
namespace PmPhp;

abstract class AbstractClass
{
    public function __set($name, $value)
    {
        throw new PmPhpException('Cannot set class property ' . get_class($this) . '->' . $name . '(' . json_encode($value) . ')');
    }

    public function __get($name)
    {
        throw new PmPhpException('Cannot get class property ' . get_class($this) . '->' . $name);
    }
}
