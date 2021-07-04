<?php
    namespace App\Core\Role;

    class StudentRoleController extends \App\Core\Controller {
        public function __pre() {
            parent::__pre();
            
            if ($this->getSession()->get('student_id') === null) {
                $this->redirect(\Configuration::BASE . 'user/login');
            }

            $this->set('role', 'student');
        }
    }
