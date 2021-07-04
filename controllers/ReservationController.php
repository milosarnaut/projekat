<?php
    namespace App\Controllers;

    class ReservationController extends \App\Core\Controller{

        public function show($id){
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservation = $reservationModel->getById($id);

            if(!$category){
                header(\Configuration::BASE);
                exit;
            }

            $this->set('reservation', $reservation);
            
           $reservations = $reservationModel->getAll();
           $this->set('reservations', $reservations);
            
           #ADMIN MODEL
           $adminModel = new \App\Models\AdminModel($this->getDatabaseConnection());
            $admin = $adminModel->getById($id);
            if(!$admin){
                header(\Configuration::BASE);
                exit;
            }

            $this->set('admin', $admin);

            #STUDENT MODEL
            $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());
            $student = $studentModel->getById($id);
            if(!$student){
                header(\Configuration::BASE);
                exit;
            }

            $this->set('student', $student);

            #TERM MODEL
            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $termsInReservation = $termModel->getAllByReservationId($id);

            $this->set('termsInReservation', $termsInReservation);
        }

        public function reservations() {
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $this->set('reservations', $reservationModel->getAll());
        }
}