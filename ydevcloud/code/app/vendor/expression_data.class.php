<?php
namespace app\vendor;
use \yangzie\YZE_Model;
use yangzie\YZE_Object;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Project_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Expression_Data extends YZE_Object {
    public $fromUuid;
    public $id;
    public $scope;
    public $path;
    public $name;
    public $modifier;
    public $literal;
    public $isExpression;
    public $type;

    public function __construct(array $datas) {
        foreach ($datas as $name => $value) {
            $this->$name = $value;
        }
    }
}?>
