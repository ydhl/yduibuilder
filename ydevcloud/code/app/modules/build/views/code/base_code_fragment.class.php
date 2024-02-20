<?php
namespace app\modules\build\views\code;


abstract class Base_Code_Fragment{
    public abstract function merge(Base_Code_Fragment $fragment);
}
