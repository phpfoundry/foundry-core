<?php
namespace foundry\core\database;
use \foundry\core\Model as Model;

/**
 * The database interface.
 *
 * This interface treats all databases as object-based. For a table-based database
 * this is done by mapping object fields to table fields.
 */
class MongoDatabaseService extends \Mongo implements DatabaseService {
    public static $required_options = array("host", "username", "password", "db");

    private $db;

    public function __construct($options) {
        \foundry\core\Service::validate($options, self::$required_options);
        $username = $options["username"];
        $password = $options["password"];
        $host = $options["host"];
        try {
            parent::__construct("mongodb://$username:$password@$host/" . $options["db"]);
            parent::connect();
            $this->db = parent::selectDB($options["db"]);
            
        } catch (\MongoConnectionException $exception) {
            throw new \foundry\core\exceptions\ServiceConnectionException("Unable to connect to MongoDB.");
        }
    }


    /**
     * Get an array of find conditions.
     *
     * @param array $rules
     */
    private function get_conditions(array $rules, Model $obj=NULL) {
        $condition = array();
        if (count($rules) > 0) {
            // Build where caluse
            foreach($rules as $key=>$value) {
                $key = strtolower($key);
                $op = "";
                if (is_array($value)) {
                    $op = $value[0];
                    $value = $value[1];
                }
                if ($obj !== NULL) {
                    $type = $obj->getFieldType($key);
                    if ($type == Model::INT) $value = intval($value);
                }
                if (empty($op)) {
                    $condition[$key] = $value;
                } else {
                    if ($op == '>') $op = '$gt';
                    else if ($op == '<') $op = '$lt';
                    if (!isset($condition[$key])) $condition[$key] = array();
                    $condition[$key][$op] = $value;
                }
            }
        }
        return $condition;
    }
    
    /**
     * Get an array of sort values.
     *
     * @param array $rules
     */
    private function get_sort(array $rules) {
        $sort = array();
        if (count($rules) > 0) {
            foreach ($rules as $key => $op) {
                $key = strtolower($key);
                
                if ($op == "DESC") $op = -1;
                else if ($op == "ASC") $op = 1;
                
                $sort[$key] = $op;
            }
        }
        return $sort;
    }
    
    /**
     * Load objects from a table in the database.
     *
     * @param string $classname  The name of the class type to instantiate and load data into.
     * @param string $collection_name     The name of the table or document key in the database.
     * @param string $key        The table column to use as the array key for the returned data.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @param array  $sort_rules An array of sorting rules in the form:
     *                             array("field" => "DESC"/"ASC", ...)
     * @param array  $limits     An array with limit conditions either in the form:
     *                              array("count")  or
     *                              array("start", "count")
     * @return object|boolean An array of $classname instances keyed by the $key field (if set),
     *                        false on failure.
     */
    public function load_objects($classname, $collection_name, $key = "",
                                 array $conditions = array(),
                                 array $sort_rules = array(),
                                 array $limits = array()) {
        if (!class_exists($classname)) {
            throw new \foundry\core\exceptions\ModelDoesNotExistException("Unable to load class $classname");
        }
        $key = strtolower($key);
        //print("\nCollection: $collection_name\n");

        $obj = new $classname();
        $fields = $obj->getFields();
        if (count($fields) == 0) return false;

        $objects = array();
        $collection = $this->db->selectCollection($collection_name);
        
        $condition = $this->get_conditions($conditions, $obj);
        $cursor = $collection->find();
        //print("\tPre-condition: " . $cursor->count() . "\n");
        
        //print("\tConditions:\n" . get_a($condition) . "\n");
        //print("\tSort:\n" . get_a($this->get_sort($sort_rules)) . "\n");
        
        $cursor = $collection->find($condition);
        
        //print("\tPre-sort: " . $cursor->count() . "\n");
        if (count($sort_rules) > 0) {
            $cursor = $cursor->sort($this->get_sort($sort_rules));
        }
        
        $start = 0;
        if (count($limits) == 1) {
            $cursor = $cursor->limit($limits[0]);
        }
        if (count($limits) == 2) {
            $start = $limits[0];
            $cursor = $cursor->limit(($start + 1) + $limits[1]);
        }
        $i = 0;
        if (count($cursor) > 0) {
            foreach ($cursor as $record) {
                if ($i++ < $start) continue;
                
                $obj = new $classname();
                foreach ($fields as $field => $type) {
                    try {
                        $obj->set($field, $record[$field]);
                    } catch (FieldDoesNotExistException $exception) {
                        // Field doesn't exist
                        throw FieldDoesNotExistException("Field $field doesn't exist in $classname");
                    }
                }
                if (!empty($key)) {
                    $key_value = $record[$key];
                    if (!empty($key_value)) {
                        $objects[$key_value] = $obj;
                    } else {
                        $objects[] = $obj;
                    }
                } else {
                    $objects[] = $obj;
                }
            }
        }
        //print_a($objects);
        return $objects;        
    }

    /**
     * Get a count of objects in a table.
     *
     * @param string $collection_name The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return integer|boolean The count on success, false on failure.
     */
    public function count_objects($collection_name, array $conditions = array()) {
        $collection = $this->db->selectCollection($collection_name);
        
        $condition = $this->get_conditions($conditions);
        $cursor = $collection->find($condition);
        return $cursor->count(true);
    }

    /**
     * Load an object from the database.
     *
     * @param string $classname The name of the class type to instantiate and load data into.
     * @param string $collection_name The name of the table in the database.
     * @param array  $conditions The conditions for the database query in an array where keys
     *                           represent the field name, and the associated value is the condition.
     * @return object An instance of $classname on success, false on failure.
     */
    public function load_object($classname, $collection_name,
                                array $conditions = array()) {
        $objects = $this->load_objects($classname, $collection_name, "", $conditions, array(), array(1));
        if (count($objects) > 0) {
            return $objects[0];
        } else {
            return false;
        }
    }

    /**
     * Writes the values from the object into the database.
     *
     * @param object $object The object with the data to write into the database.
     * @param string $collection_name The name of the table in the database.
     * @return boolean true on success, false on failure.
     */
    public function write_object(Model $object, $collection_name) {
        $collection = $this->db->selectCollection($collection_name);
        $array = $object->getAsArray();
        
        try {
            $collection->insert($array, true);
            
        } catch (MongoCursorException $exception) {
            return false;
        }
        return true;
    }

    /**
     * Update an object in the database.
     *
     * @param $object The object to write to the database.
     * @param $collection_name The database object/table reference.
     * @param $conditions The conditions to match to updated the database
     * @param $updatefields The fields to update.
     * @return boolean true on success, false on failure.
     */
    public function update_object(Model $object, $collection_name,
                                  array $conditions,
                                  array $updatefields) {
        if (count($updatefields) == 0) return false;
        
        $collection = $this->db->selectCollection($collection_name);
        $array = $object->getAsArray();
        $condition = $this->get_conditions($conditions, $object);
        
        $data = array();
        foreach ($updatefields as $field) {
            $field = strtolower($field);
            $data[$field] = $object->get($field);
        }
        
        try {
            $collection->update($condition, array('$set'=>$data), array('multiple'=>true, 'safe'=>true));
            
        } catch (MongoCursorException $exception) {
            return false;
        }
        return true;
    }

    /**
     * Delete object[s] from the database.
     *
     * @param $collection_name The database object/table reference (tablename, key, etc...)
     * @param $conditions The delete conditions.
     * @return boolean true on success, false on failure.
     */
    public function delete_object($collection_name, array $conditions) {
        $collection = $this->db->selectCollection($collection_name);
        $condition = $this->get_conditions($conditions);
        try {
            $collection->remove($condition, array("safe" => true));
        } catch (MongoCursorException $exception) {
            return false;
        }
        return true;
    }

}

?>
