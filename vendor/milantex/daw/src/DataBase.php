<?php
    namespace Milantex\DAW;

    /**
     * The DataBase interface wrapper class
     */
    final class DataBase {
        /**
         * The PDO object for the open connection
         * @var PDO
         */
        private $connection = FALSE;

        /**
         * Stores the error recorded after the last execute method was ran
         * @var array
         */
        private $lastExecutionError = NULL;

        /**
         * Stores the number of rows affected by the last execute method
         * @var int
         */
        private $lastAffectedRowCount = 0;

        /**
         * Database host name
         * @var string
         */
        private $dbHost;
        
        /**
         * Database name
         * @var string
         */
        private $dbName;
        
        /**
         * Database user
         * @var string
         */
        private $dbUser;
        
        /**
         * Database user's password
         * @var string
         */
        private $dbPass;

        /**
         * The DataBase class constructor function
         * @param string $dbHost
         * @param string $dbName
         * @param string $dbUser
         * @param string $dbPass
         */
        public function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPass) {
            $this->dbHost = $dbHost;
            $this->dbName = $dbName;
            $this->dbUser = $dbUser;
            $this->dbPass = $dbPass;

            $this->reconnect();
        }

        /**
         * Executes the select method of this class specifying the expectation
         * of a single record return value.
         * @param string $sql
         * @param array $parameters
         * @return NULL|stdClass
         */
        public function selectOne($sql, $parameters = []) {
            if (!$this->connection) {
                return NULL;
            }

            $prep = $this->connection->prepare($sql);

            try {
                $res = $prep->execute($parameters);
                if (!$res) {
                    return NULL;
                }
            } catch (\Exception $e) {
                return NULL;
            }

            $ret = $prep->fetch(\PDO::FETCH_OBJ);
            
            if ($ret === FALSE) {
                return NULL;
            }
            
            return $ret;
        }

        /**
         * Executes the select method of this class specifying the expectation
         * of an array of records being returned.
         * @param string $sql
         * @param array $parameters
         * @return array
         */
        public function selectMany($sql, $parameters = []) {
            if (!$this->connection) {
                return [];
            }

            $prep = $this->connection->prepare($sql);

            try {
                $res = $prep->execute($parameters);
                if (!$res) {
                    return [];
                }
            } catch (\Exception $e) {
                return [];
            }

			$ret = $prep->fetchAll(\PDO::FETCH_OBJ);
            
            if ($ret === FALSE) {
                return [];
            }
            
            return $ret;
        }

        /**
         * Performs an execution of an SQL statement for the selected connection
         * and returns the result of the execution for processing to the caller.
         * @param string $sql
         * @param array $parameters
         * @param string $connection
         * @return type
         */
        public function execute($sql, $parameters = []) {
            if (!$this->connection) {
                return NULL;
            }

            $prep = $this->connection->prepare($sql);

            $res = $prep->execute($parameters);

            $this->setLastError($prep, $res);

            return $res;
        }

        /**
         * This method sets the value of the last execution error's result state
         * @param \PDOStatement $prep
         * @param string $res
         */
        private function setLastError(\PDOStatement $prep, $res) {
            if (!$res) {
                $this->lastExecutionError = $prep->errorInfo();
                $this->lastAffectedRowCount = NULL;
                return;
            }

            $this->lastExecutionError = NULL;
            $this->lastAffectedRowCount = $prep->rowCount();
        }

        /**
         * Returns the error recorded after the last execute method failure.
         * One error can be retrieved once. After it is returned, it is reset.
         * @return array|NULL
         */
        public function getLastExecutionError() {
            if (!$this->connection || !isset($this->lastExecutionError)) {
                $this->lastExecutionError = NULL;
            }

            $error = $this->lastExecutionError;

            $this->lastExecutionError = NULL;

            return $error;
        }

        /**
         * Returns the affected row count after the last execute method success.
         * This method returns NULL if there was an error or if the execute
         * method was never ran. It can return 0 if no rows were affected.
         * @return int|NULL
         */
        public function getLastExecutionAffectedRownCount() {
            if (!$this->connection || !isset($this->lastAffectedRowCount)) {
                $this->lastAffectedRowCount = NULL;
            }

            $error = $this->lastAffectedRowCount;

            $this->lastAffectedRowCount = NULL;

            return $error;
        }

        /**
         * Returns the last insert ID after INSERT on this connection
         * @return int
         */
        public function getLastInsertId() {
            return $this->connection->lastInsertId();
        }

        /**
         * Force disconnect from the database
         */
        public function disconnect() {
            $this->connection = NULL;
        }

        /**
         * Try to reconnect to the database using stored connection parameters
         */
        public function reconnect() {
            $this->connection = new \PDO('mysql:hostname=' . $this->dbHost . ';dbname=' . $this->dbName . ';charset=utf8', $this->dbUser, $this->dbPass);
            $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        }

        /**
         * This method resets the last execution error to back NULL
         */
        public function resetLastExecutionError() {
            $this->lastExecutionError = NULL;
        }

        /**
         * This method resets the last execution affected row count back to zero
         */
        public function resetLastExecutionAffectedRowCount() {
            $this->lastAffectedRowCount = 0;
        }
    }
