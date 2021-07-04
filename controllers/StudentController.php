<?php
    namespace App\Controllers;

    class StudentController extends \App\Core\Controller{

        public function show($id){
            $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());
            $student = $studentModel->getById($id);

            if(!$student){
                header(\Configuration::BASE);
                exit;
            }

            $this->set('student', $student);
    }
}