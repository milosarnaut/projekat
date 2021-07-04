<?php
    namespace App\Controllers;

    class AdminController extends \App\Core\Controller{

        public function show($id){
            $adminModel = new \App\Models\AdminModel($this->getDatabaseConnection());
            $admin = $adminModel->getById($id);

            if(!$admin){
                header(\Configuration::BASE);
                exit;
            }

            $this->set('admin', $admin);
    }
}