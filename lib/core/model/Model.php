<?php

require 'lib/core/common/Validation.php';
require 'lib/core/model/Connection.php';
require 'lib/core/model/Table.php';
require 'lib/core/model/Behavior.php';

class Model extends Hookable {
    protected $behaviors = array();

    protected $getters = array();
    protected $setters = array();

    protected $displayField;
    protected $table;
    protected $connection = 'default';

    protected $defaultScope = array(
        'recursion' => 0
    );

    protected $perPage = 20;
    public $pagination = array();

    protected $validates = array();
    protected $errors = array();

    public $data = array();

    protected $beforeSave = array();
    protected $beforeCreate = array();
    protected $beforeUpdate = array();
    protected $beforeDelete = array();
    protected $beforeValidate = array();

    protected $afterSave = array();
    protected $afterCreate = array();
    protected $afterUpdate = array();
    protected $afterDelete = array();
    protected $afterValidate = array();

    protected $_models = array();
    protected $_behaviors = array();

    protected static $instances = array();

    public function __construct($data = null) {
        if(!is_null($data)) {
            $this->data = $data;
        }
        if(!array_key_exists('id', $this->data)) {
            $this->data['id'] = null;
        }
        $this->loadBehaviors($this->behaviors);
    }

    public function __call($method, $args) {
        $regex = '/(?P<method>first|all)By(?P<fields>[\w]+)/';
        if(preg_match($regex, $method, $output)) {
            $fields = Inflector::underscore($output['fields']);
            $fields = explode('_and_', $fields);

            $conditions = array_slice($args, 0, count($fields));
            $params = array();

            if(array_key_exists(count($fields), $args)) {
                $params = $args[count($fields)];
            }

            $params['conditions'] = array_combine($fields, $conditions);

            return $this->$output['method']($params);
        }

        throw new BadMethodCallException(get_class($this) . '::' . $method . ' does not exist.');
    }

    public function __set($name, $value) {
        // @todo shouldn't fail silently
        $this->data[$name] = $value;
    }

    public function __get($name) {
        $attrs = array('data', '_models', '_behaviors');

        foreach($attrs as $attr) {
            if(array_key_exists($name, $this->{$attr})) {
                return $this->{$attr}[$name];
            }
        }

        if(array_key_exists($name, $this->schema())) {
            return null;
        }

        throw new RuntimeException(get_class($this) . '->' . $name . ' does not exist.');
    }

    public function hasAttribute($attr) {
        return array_key_exists($attr, $this->data);
    }

    public function hasGetter($attr) {
        return in_array($attr, $this->getters);
    }

    public function hasSetter($attr) {
        return in_array($attr, $this->setters);
    }

    public static function load($name) {
        if(!array_key_exists($name, Model::$instances)) {
            $filename = 'app/models/' . Inflector::underscore($name) . '.php';
            if(!class_exists($name) && Filesystem::exists($filename)) {
                require_once $filename;
            }

            if(class_exists($name)) {
                Model::$instances[$name] = new $name();
            }
            else {
                throw new RuntimeException('The model "' . $name . '" was not found.');
            }
        }

        return Model::$instances[$name];
    }

    public function getConnection() {
        return $this->connection;
    }

    public function connection() {
        return Table::load($this)->connection();
    }

    protected function table() {
        return Table::load($this)->name();
    }

    public function getTable() {
        return $this->table;
    }

    public function schema() {
        return Table::load($this)->schema();
    }

    public function primaryKey() {
        return Table::load($this)->primaryKey();
    }

    protected function loadBehaviors($behaviors) {
        foreach($this->behaviors as $key => $behavior):
            $options = array();
            if(!is_numeric($key)):
                $options = $behavior;
                $behavior = $key;
            endif;
            $this->loadBehavior($behavior, $options);
        endforeach;
    }

    protected function loadBehavior($behavior, $options = array()) {
        $behavior = Inflector::camelize($behavior);
        Behavior::load($behavior);
        return $this->_behaviors[$behavior] = new $behavior($this, $options);
    }

    public function query($query) {
        return $this->connection()->query($query);
    }

    public function fetch($query) {
        return $this->connection()->fetchAll($query);
    }

    public function begin() {
        return $this->connection()->begin();
    }

    public function commit() {
        return $this->connection()->commit();
    }

    public function rollback() {
        return $this->connection()->rollback();
    }

    public function transactionStarted() {
        return $this->connection()->transactionStarted();
    }

    public function insertId() {
        return $this->connection()->insertId();
    }

    public function affectedRows() {
        return $this->connection()->affectedRows();
    }

    public function escape($value) {
        return $this->connection()->escape($value);
    }

    protected function scope($scope, $params, $defaults = array()) {
        if(is_array($scope)) {
            $params = $scope;
            $scope = 'default';
        }

        if(is_null($scope)) {
            $scope = 'default';
        }

        if($scope !== false) {
            $scope_name = $scope . 'Scope';
            $scope = $this->{$scope_name};
        }
        else {
            $scope = array();
        }

        return array_merge($defaults, $scope, $params);
    }

    public function all($scope = null, $params = array()) {
        $defaults = array( 'table' => $this->table() );
        $params = $this->scope($scope, $params, $defaults);

        $query = $this->connection()->read($params);

        $results = array();
        while($result = $query->fetch()) {
            $self = get_class($this);
            $results []= new $self($result);
        }

        return $results;
    }

    public function first($scope = null, $params = array()) {
        $params['limit'] = 1;
        $results = $this->all($scope, $params);

        return empty($results) ? null : $results[0];
    }

    public function count($scope = null, $params = array()) {
        $defaults = array( 'table' => $this->table() );
        $params = $this->scope($scope, $params, $defaults);
        unset($params['offset'], $params['limit']);

        return $this->connection()->count($params);
    }

    public function paginate($scope = null, $params = array()) {
        $count = $this->count($scope, $params);

        $defaults = array(
            'perPage' => $this->perPage,
            'page' => 1
        );
        $params = $this->scope($scope, $params, $defaults);

        $params['offset'] = ($params['page'] - 1) * $params['perPage'];
        $params['limit'] = $params['perPage'];

        $this->pagination = array(
            'totalRecords' => $count,
            'totalPages' => ceil($count / $params['perPage']),
            'perPage' => $params['perPage'],
            'offset' => $params['offset'],
            'page' => $params['page']
        );

        return $this->all(false, $params);
    }

    public function toList($scope = null, $params = array()) {
        $defaults = array(
            'key' => $this->primaryKey(),
            'displayField' => $this->displayField,
            'table' => $this->table()
        );
        $params = $this->scope($scope, $params, $defaults);

        if(!array_key_exists('fields', $params)) {
            $params['fields'] = array_merge(
                (array) $params['key'],
                (array) $params['displayField']
            );
        }

        $all = $this->connection()->read($params);

        $results = array();
        while($result = $all->fetch()) {
            if(is_array($params['displayField'])) {
                $keys = array_flip($params['displayField']);
                $value = array_intersect_key($result, $keys);
            }
            else {
                $value = $result[$params['displayField']];
            }

            $results[$result[$params['key']]] = $value;
        }

        return $results;
    }

    public function exists($conditions) {
        return (bool) $this->count(array(
            'conditions' => $conditions
        ));
    }

    public function updateAttributes($data) {
        $this->data = array_merge($this->data, $data);
    }

    public function insert($data) {
        $params = array(
            'values' => $data,
            'table' => $this->table()
        );

        return $this->connection()->create($params);
    }

    public function update($params, $data) {
        $params += array(
            'values' => $data,
            'table' => $this->table()
        );

        return $this->connection()->update($params);
    }

    public function save($data = array()) {
        if(!empty($data)) {
            $this->data = $data;

            if(!array_key_exists('id', $this->data)) {
                $this->data['id'] = null;
            }
        }

        // apply modified timestamp
        $date = date('Y-m-d H:i:s');
        $this->data['modified'] = $date;

        $pk = $this->primaryKey();

        // verify if the record exists
        if(array_key_exists($pk, $this->data) && !is_null($this->data[$pk])) {
            $exists = $this->exists(array(
                $pk => $this->data[$pk]
            ));
        }
        else {
            $exists = false;
        }

        // apply created timestamp
        if(!$exists):
            $this->data['created'] = $date;
        endif;

        // apply beforeSave filter
        $this->data = $this->fireFilter('beforeSave', $this->data);
        if(!$this->data):
            return false;
        endif;

        // filter fields that are not in the schema
        $data = array_intersect_key($this->data, $this->schema());

        // update a record if it already exists...
        if($exists):
            $save = $this->update(array(
                'conditions' => array(
                    $pk => $this->data[$pk]
                ),
                'limit' => 1
            ), $data);
        // or insert a new one if it doesn't
        else:
            $save = $this->insert($data);
            $this->data[$pk] = $this->insertId();
        endif;

        // fire afterSave action
        $this->fireAction('afterSave', array(!$exists));

        return $save;
    }

    public function validate($data = array()) {
        if(!empty($data)) {
            $this->data = $data;
        }

        $this->errors = array();
        $defaults = array(
            'required' => false,
            'allowEmpty' => false,
            'message' => null,
            'on' => false
        );

        $this->data = $this->fireFilter('beforeValidate', $this->data);
        if(!$this->data):
            return false;
        endif;

        foreach($this->validates as $field => $rules):
            if(!is_array($rules) || (is_array($rules) && isset($rules['rule']))):
                $rules = array($rules);
            endif;
            foreach($rules as $rule):
                if(!is_array($rule)):
                    $rule = array('rule' => $rule);
                endif;
                $rule = array_merge($defaults, $rule);
                if($rule['allowEmpty'] && empty($this->data[$field])):
                    continue;
                elseif($rule['on'] == 'create' && (array_key_exists('id', $this->data) && $this->data['id'] != null)):
                    continue;
                endif;
                $required = !isset($this->data[$field]) && $rule['required'];
                if($required):
                    $this->errors[$field] = is_null($rule['message']) ? $rule['rule'] : $rule['message'];
                elseif(isset($this->data[$field])):
                    if(!$this->callValidationMethod($rule['rule'], $this->data[$field])):
                        $message = is_null($rule['message']) ? $rule['rule'] : $rule['message'];
                        $this->errors[$field] = $message;
                        break;
                    endif;
                endif;
            endforeach;
        endforeach;
        return empty($this->errors);
    }

    public function callValidationMethod($params, $value) {
        $method = is_array($params) ? $params[0] : $params;
        $class = method_exists($this, $method) ? $this : 'Validation';
        if(is_array($params)):
            $params[0] = $value;
            return call_user_func_array(array($class, $method), $params);
        else:
            if($class == 'Validation'):
                return Validation::$params($value);
            else:
                return $this->$params($value);
            endif;
        endif;
    }

    public function errors() {
        return $this->errors;
    }

    public function data() {
        return $this->data;
    }

    public function delete($id, $dependent = true) {
        $params = array(
            'conditions' => array(
                $this->primaryKey() => $id
            ),
            'limit' => 1
        );

        $delete = false;

        if($this->exists(array($this->primaryKey() => $id))) {
            if(!$this->fireFilter('beforeDelete', $id)) return false;
            $delete = (bool) $this->deleteAll($params);

            $this->fireAction('afterDelete', $id);
        }

        return $delete;
    }

    public function deleteAll($params = array()) {
        $db = $this->connection();
        $params += array(
            'table' => $this->table()
        );
        return $db->delete($params);
    }
}
