<?php
    use Milantex\DAW\DataBase as DataBase;

    class DataBaseTest extends PHPUnit_Framework_TestCase {
        private $database;

        public function setUp() {
            $this->database = new DataBase('localhost', 'bayou', 'root', '');
        }

        public function testConstructor() {
            $this->assertInstanceOf('\\Milantex\\DAW\\DataBase', $this->database);
        }

        public function testSelectOneOnBadConnection() {
            $this->database->disconnect();
            $data = $this->database->selectOne("SELECT 1 author_id, 'Milan' forename, 'Tair' surname FROM DUAL;");
            $this->assertTrue(is_null($data));
            $this->database->reconnect();
        }

        public function testSelectManyOnBadConnection() {
            $this->database->disconnect();
            $data = $this->database->selectMany("SELECT 1 product_id, 'Product A' name, 302.99 price FROM DUAL UNION
                                                 SELECT 2 product_id, 'Product B' name, 100.00 price FROM DUAL;");
            $this->assertTrue(is_array($data));
            $this->assertTrue(empty($data));
            $this->database->reconnect();
        }

        public function testExecuteOnBadConnection() {
            $this->database->disconnect();
            $res = $this->database->execute('CREATE TEMPORARY TABLE php_unit_test_table ( author_id INT(11) AUTO_INCREMENT PRIMARY KEY, name VARCHAR(32), email VARCHAR(255) );');
            $this->assertTrue(is_null($res));
            $this->database->reconnect();
        }

        public function testGetLastExecutionErrorOnBadConnection() {
            $this->database->disconnect();
            $res = $this->database->getLastExecutionError();
            $this->assertTrue(is_null($res));
            $this->database->reconnect();
        }

        public function testGetLastExecutionAffectedRownCountOnBadConnection() {
            $this->database->disconnect();
            $res = $this->database->getLastExecutionAffectedRownCount();
            $this->assertTrue(is_null($res));
            $this->database->reconnect();
        }

        public function testSelectOneWithBadQuery() {
            $data = $this->database->selectOne("SELECT * FROM no_table WHERE no_field = ':this_will_fail';");
            $this->assertTrue(is_null($data));
        }

        public function testSelectManyWithBadQuery() {
            $data = $this->database->selectMany("\\SELECT 1 author_id AND THE REST IS HISTORY, AS THEY SAY...';");
            $this->assertTrue(empty($data));
        }

        public function testSelectOneWithBadParameters() {
            $data = $this->database->selectOne("SELECT * FROM `user` WHERE `username` = ? AND `active` = ?;", ['only one parameter here :( two needed']);
            $this->assertTrue(is_null($data));
        }

        public function testSelectManyWithBadParameters() {
            $data = $this->database->selectMany("SELECT * FROM `post` WHERE `post_id` = ? AND `visible` = ?;", ['only one parameter here :( two needed']);
            $this->assertTrue(empty($data));
        }

        public function testSelectOne() {
            $data = $this->database->selectOne("SELECT 1 author_id, 'Milan' forename, 'Tair' surname FROM DUAL;");

            $this->assertTrue(is_object($data));

            $this->assertObjectHasAttribute('author_id', $data);
            $this->assertObjectHasAttribute('forename', $data);
            $this->assertObjectHasAttribute('surname', $data);

            $this->assertEquals(1, $data->author_id);
            $this->assertSame('Milan', $data->forename);
            $this->assertSame('Tair', $data->surname);
        }

        public function testSelectMany() {
            $data = $this->database->selectMany("SELECT 1 product_id, 'Product A' name, 302.99 price FROM DUAL UNION
                                                 SELECT 2 product_id, 'Product B' name, 100.00 price FROM DUAL;");

            $this->assertTrue(is_array($data));

            $this->assertTrue(count($data) == 2);

            $xepectedValues = [
                [ 1, 'Product A', 302.99 ],
                [ 2, 'Product B', 100.00 ]
            ];

            $index = 0;
            foreach ($data as $item) {
                $this->assertObjectHasAttribute('product_id', $item);
                $this->assertObjectHasAttribute('name', $item);
                $this->assertObjectHasAttribute('price', $item);

                $expected = $xepectedValues[$index++];

                $this->assertEquals($expected[0], $item->product_id);
                $this->assertEquals($expected[1], $item->name);
                $this->assertEquals($expected[2], $item->price);
            }
        }

        private function doCreateTemporaryTable() {
            $res1 = $this->database->execute('CREATE TEMPORARY TABLE php_unit_test_table ( author_id INT(11) AUTO_INCREMENT PRIMARY KEY, name VARCHAR(32), email VARCHAR(255) );');
            $this->assertTrue($res1, 'Method execute failed to create a temporary table.');
        }

        private function doInsertRecordIntoTemporaryTable() {
            $res2 = $this->database->execute("INSERT INTO php_unit_test_table VALUES (NULL, 'Milan Tair', 'milan.tair@gmail.com');");
            $this->assertTrue($res2, 'Method execute failed to insert a record into the temporary table.');
        }

        private function doDropTemporaryTable() {
            $res3 = $this->database->execute("DROP TEMPORARY TABLE php_unit_test_table;");
            $this->assertTrue($res3, 'Method execute failed to drop the temporary table.');
        }

        public function testExecute() {
            $this->doCreateTemporaryTable();
            $this->doInsertRecordIntoTemporaryTable();
            $this->doDropTemporaryTable();
        }

        public function testGetLastExecutionAffectedRownCount() {
            $this->doCreateTemporaryTable();
            $this->doInsertRecordIntoTemporaryTable();

            $res4 = $this->database->execute("DELETE FROM php_unit_test_table;");
            $this->assertTrue($res4, 'Method execute failed to delete records from the temporary table.');

            $this->assertTrue($this->database->getLastExecutionAffectedRownCount() != 0, 'There was supposed to be a record in the temporary table.');

            $this->doDropTemporaryTable();
        }

        public function testGetLastInsertId() {
            $this->doCreateTemporaryTable();
            $this->doInsertRecordIntoTemporaryTable();

            $lastId = $this->database->getLastInsertId();
            $this->assertTrue($lastId == 1, 'The first record in the temporary table was supposed to have the ID 1.');

            $this->doDropTemporaryTable();
        }

        public function testGetLastExecutionError() {
            $this->database->execute("INSERT INTO php_unit_test_table VALUES (NULL, 'Milan Tair', 'milan.tair@gmail.com');");

            $lastError = $this->database->getLastExecutionError();
            $this->assertNotNull($lastError, 'The result must not be NULL, because there should be an error.');
        }

        public function testResetLastExecutionError() {
            $this->database->resetLastExecutionError();
            $error = $this->database->getLastExecutionError();
            $this->assertTrue(is_null($error));
        }

        public function testResetLastExecutionAffectedRowCount() {
            $this->database->resetLastExecutionAffectedRowCount();
            $count = $this->database->getLastExecutionAffectedRownCount();
            $this->assertSame($count, 0);
        }
    }
