<?php
/**
 * A model for user groups.
 *
 * @package DataModel
 */

/**
 * A model class for user groups.
 *
 * @package DataModel
 */
class Group extends BaseModel {

    private $fields = array("name"=>Model::STR, "description"=>Model::STR, "users"=>Model::LST, "subgroups"=>Model::LST);
    private $key_field = "name";

    function __construct() {
        parent::__construct($this->fields, $this->key_field);
    }
}
?>