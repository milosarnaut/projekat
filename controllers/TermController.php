<?php
    namespace App\Controllers;

    class TermController extends \App\Core\Controller{

        public function show($id){
            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $term = $termModel->getById($id);

            if(!$term){
                header(\Configuration::BASE);
                exit;
            }

            $this->set('term', $term);
    }

    public function terms() {
        $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
        $this->set('terms', $termModel->getAll());
    }
}