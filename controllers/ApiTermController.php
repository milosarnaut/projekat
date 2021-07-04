<?php

    namespace App\Controllers;

    class ApiTermController extends \App\Core\ApiController{
        public function getTermStates(){
            $jsonArrayString = filter_input(INPUT_POST, 'ids');
            $arrayOfIds = json_decode($jsonArrayString);

            if (!$arrayOfIds){
                $this->set('error', -1);
                return;
            }
            $results = [];

            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());

            foreach($arrayOfIds as $id){
                $results[] = $termModel->getById($id);
            }
            $this->set('terms', $results);
        }

        
    }