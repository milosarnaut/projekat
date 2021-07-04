<?php
    namespace App\Core;

    abstract class Model{
        private $dbc;

        final public function __construct(DatabaseConnection &$dbc) {
            $this->dbc = $dbc;
        }

        protected function getFields(): array{
            return [];
        }

        final protected function getConnection(){
            return $this->dbc->getDatabaseConnection();
        }

        private function getTableName(): string{
            $matches = [];
            preg_match('|^.*\\\((?:[A-Z][a-z]+)+)Model$|', static::class, $matches);
            $underscoredClassName = preg_replace('|[A-Z]|', '_$0', $matches[1] ?? '');
            $lowerCaseUndersocredClassName = strtolower($underscoredClassName);
            return substr($lowerCaseUndersocredClassName, 1);
        }

        final public function getById(int $id) {
            $tableName = $this->getTableName();
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $tableName . '_id = ?;';
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            $res = $prep->execute( [ $id ] );

            $item = NULL;
            if ( $res ) {
                $itemObject = $prep->fetch(\PDO::FETCH_OBJ);
                if ($itemObject !== FALSE) {
                    $item = $itemObject;
                }
            }

            return $item;
        }

        final public function getAll(): array {
            $tableName = $this->getTableName();
            $sql = 'SELECT * FROM ' . $tableName . ';';
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            $res = $prep->execute();
            $items = [];
            if ( $res ) {
                $items = $prep->fetchAll(\PDO::FETCH_OBJ);
            }

            return $items;
        }

        final public function isFieldValueValid(string $fieldName, $fieldValue): bool{
            $fields = $this->getFields();
            $supportedFieldNames = array_keys($fields);

            if(!in_array($fieldName, $supportedFieldNames)){
                return false;
            }

            return $fields[$fieldName]->isValid($fieldValue);
        }

        final public function getByFieldName(string $fieldName, $value) {
            if(!$this->isFieldValueValid($fieldName, $value)){
                throw new \Exception('Invalid field name or value: ' . $fieldName);
            }

            $tableName = $this->getTableName();
            $sql = 'SELECT * FROM `' . $tableName . '` WHERE `' . $fieldName . '` = ?;';
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            $res = $prep->execute( [ $value ] );
            $item = NULL;
            if ( $res ) {
                $item = $prep->fetch(\PDO::FETCH_OBJ);
            }
            return $item;
        }

        final public function getAllByFieldName(string $fieldName, $value): array {
            if(!$this->isFieldValueValid($fieldName, $value)){
                throw new \Exception('Invalid field name or value: ' . $fieldName);
            }

            $tableName = $this->getTableName();
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE `' . $fieldName . '` = ?;';
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            $res = $prep->execute( [ $value ] );
            $items = [];
            if ( $res ) {
                $items = $prep->fetchAll(\PDO::FETCH_OBJ);
            }
            return $items;
        }

        private function checkFieldList(array $data){
            $fields = $this->getFields();

            $supportedFieldNames = array_keys($fields);
            $requestedFieldNames = array_keys($data);
            
            foreach( $requestedFieldNames as $requestedFieldName ){
                if(! in_array($requestedFieldName, $supportedFieldNames)){
                    throw new \Exception('Field ' . $requestedFieldName . ' is not supported!');
                }
                if(!$fields[$requestedFieldName]->isEditable()){
                    throw new \Exception('Field ' . $requestedFieldName . ' is not editable!');
                }
                if (!isset($data[$requestedFieldName])) {
                    continue;
                }
                if(!$fields[$requestedFieldName]->isValid($data[$requestedFieldName])){
                    throw new \Exception('The value for the field' . $requestedFieldName . ' is not valid!');
                }
            }

        }

        final public function add(array $data){
            $this->checkFieldList($data);

            $tableName = $this->getTableName();
            $sqlFieldNames = implode(', ', array_keys($data));
            
            $questionMarks = str_repeat('?,', count($data));
            $questionMarks = substr($questionMarks, 0, -1);

            $sql = "INSERT INTO `{$tableName}` ({$sqlFieldNames}) VALUES ({$questionMarks});";
            
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            $res = $prep->execute(array_values($data));
            if(!$res){
                return false;
            }
            return $this->dbc->getDatabaseConnection()->lastInsertId();
        }

        final public function editById(int $id, array $data){
            $this->checkFieldList($data); 

            $tableName = $this->getTableName();

            $editList = [];
            $values   = [];

            foreach ($data as $fieldName=>$value){
                $editList[] = "`{$fieldName}` = ?";
                $values[] = $value;
            }

            $editString = implode(', ', $editList);

            $values[] = $id;

            $sql = "UPDATE `{$tableName}` SET {$editString} WHERE `{$tableName}_id` = ?;";
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            $res = $prep->execute($values);
            return $res;
        }

        final public function deleteById(int $id) {
            $tableName = $this->getTableName();
            $sql = 'DELETE FROM `' . $tableName . '` WHERE `' . $tableName . '_id` = ?;';
            $prep = $this->dbc->getDatabaseConnection()->prepare($sql);
            return $prep->execute( [ $id ] );
        }
    }