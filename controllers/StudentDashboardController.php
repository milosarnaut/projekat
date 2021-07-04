<?php
    namespace App\Controllers;

    class StudentDashboardController extends \App\Core\Role\StudentRoleController {
        private function getDayName(int $dayNumber) {
            switch ($dayNumber) {
                case 1: return 'Ponedeljak';
                case 2: return 'Utorak';
                case 3: return 'Sreda';
                case 4: return 'Četvrtak';
                case 5: return 'Petak';
                case 6: return 'Subota';
                case 7: return 'Nedelja';
            }
        }
        
        private function prepareTermStructure(array $terms, $useSingleMonth = false) {
            $years = [];

            foreach($terms as $term) {
                $useMonth = $term->month;

                if ($useSingleMonth) {
                    $useMonth = date('m');
                }

                if(!isset($years[$term->year])){
                    $years[$term->year] = [];
                }
                
                if(!isset($years[$term->year][$useMonth])){
                    $years[$term->year][$useMonth] = [];
                }
    
                $date = sprintf('%4d-%02d-%02d', $term->year, $term->month, $term->day);
                $timestamp = strtotime($date);
                $dow = date('N', $timestamp);
                $dowName = $this->getDayName($dow);
    
                if (!isset($years[$term->year][$useMonth][$term->day])){
                    $years[$term->year][$useMonth][$term->day] = [
                        "dow"   => $dowName,
                        "date"  => $date,
                        "terms" => []
                    ];
                }
    
                $years[$term->year][$useMonth][$term->day]['terms'][] = $term;
            }

            return $years;
        }

        public function home() {
            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $terms = $termModel->getAllAfterDateNDays(date('Y-m-d'), 30);
            $termStructure = $this->prepareTermStructure($terms);
            $this->set('termStructure', $termStructure);          
        }

        #---------------------------------------------------------------------------------------
        private function rezervacija($termId) {
            if ($this->isTermClosed($termId)) {
                $this->set('message', 'Ovaj termin je zatvoren.');
                return false;
            }

            if ($this->isTermReserved($termId)) {
                $this->set('message', 'Ovaj termin je vec rezervisan.');
                return false;
            }

            $addDataReservation = [
                'student_id' => $this->getSession()->get('student_id'),
                'term_id'    => $termId,
                'admin_id'   => null
            ];

            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $res = $reservationModel->add($addDataReservation);

            if (!$res) {
                $this->set('message', 'Rezervacija termina nije uspela. Pokušajte ponovo.');
                return false;
            }

            return true;
        }

        private function getTerminStatus($termId) {
            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $term = $termModel->getById($termId);
            if (!$term) {
                return null;
            }
            $cStatus = $term->status; #status u bazi
            return $cStatus;
        }

        private function getTerminReservationStudentId($termId) {
            # 1 uzmi model za rez
            # 2 uzmes rez za taj termId iz modela rez
            # 2.1 ako ne postoji, return null
            # 2.2 ako postoji, vrati student_id te rezervacije
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservations = $reservationModel->getAllByTermId($termId);
            if (!$reservations) {
                return null;
            }
            return $reservations[0]->student_id;
        }

        private function obrisiRezervacijuZaTermin($termId) {

            # proverad a li je term id za trenutnog studenta!!!!
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservations = $reservationModel->getAllByTermId($termId);
            if (!$reservations) {
                $this->set('message', 'Ne postoji rezervacija!');
                return null;
            }

            $reservation = $reservations[0];

            # Da li je za termin koji korisnik zeli da otkaze rezervacija u vlasnistvu tog korisnika? -||-
            if ($reservation->student_id !== $this->getSession()->get('student_id')) {
                $this->set('message', 'Rezervacija za ovaj termin nije Vaša!');
                return false;
            }
            #...
            
            $reservations = $reservationModel->getAllByTermId($reservation->term_id);
            if (!$reservations) {
                $this->set('message', 'Ne postoji rezervacija.');
                return false;
            }

            $reservation = $reservations[0];

            $ok = $reservationModel->deleteByTermId($reservation->term_id); // napravi fu-ju koja brise sve rezervacije za dati term_id

            if (!$ok) {
                $this->set('message', 'Došlo je do greške prilikom pokušaja brisanja rezervacije!');
                return false;
            }

            return true;
        }

        private function otkazivanje($reservationId) {
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservation = $reservationModel->getById($reservationId);

            if (!$reservation) {
                $this->set('message', 'Nema rezervacije za otkazivanje u ovom terminu... O_o\'');
                return false;
            }

            $termId = $reservation->term_id;

            # Da li je termin koji korisnik zeli da otkaze uopste zauzet? Ako nije, ne idi dalje, prikazi poruku.
            if ($reservation->status !== 'R') {
                $this->set('message', 'Ovaj termin nije rezervisan! Ne može se otkazati rezervacija.');
                return false;
            }

            if ($reservation->status == 'Z') {
                $this->set('message', 'Nema rezervacije za otkazivanje u ovom terminu... O_o\'');
                return false;
            }

            # Obrisi "rezervaciju" za taj termin. Termin ce da promeni okidac nad reservation tabelom.
            $ok = $this->obrisiRezervacijuZaTermin($termId);

            if (!$ok) {
                $this->set('message', 'Došlo je do greške prilikom brisanja rezervacije!');
                return false;
            }

            return true;
        }

        private function isTermClosed($termId) {
            return $this->getTerminStatus($termId) == 'Z';
        }

        private function isTermFree($termId) {
            return $this->getTerminStatus($termId) == 'S';
        }

        private function isTermReserved($termId) {
            return $this->getTerminStatus($termId) == 'R';
        }
        #-------------------------------------------------------------------------------------------------

        public function postEditTermStudent() {
            $termId = \filter_input(INPUT_POST, 'term_id', FILTER_SANITIZE_NUMBER_INT);
            $aktivnost = \filter_input(INPUT_POST, 'radio', FILTER_SANITIZE_STRING);

            if ($aktivnost == 'R') {

               $ok = $this->rezervacija($termId);

                if (!$ok) {
                    return;
                }

                $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
                $reservations = $reservationModel->getAllByTermId($termId);
                if (!$reservations) {
                    $this->set('message', 'Ne postoji rezervacija!');
                    return null;
                }

                $reservation = $reservations[0];

                $editData = [
                    'status' => $aktivnost
                ];
    

                $termId = \filter_input(INPUT_POST, 'term_id', FILTER_SANITIZE_NUMBER_INT);
                $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
                $term = $termModel->getById($termId);
                $ok = $termModel->editById($termId, $editData);
                if (!$ok) {
                    $this->set('message', 'Rezervacija termina nije uspela.');
                    return;
                }

                # pozivanje f-je za slanje e-maila o uspesnoj rezervaciji
                $this->notifyStudentORezervaciji($reservation->reservation_id);
                $this->redirect( \Configuration::BASE . 'student/profile');
            }

            if ($aktivnost == 'S') {
                $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
                $reservations = $reservationModel->getAllByTermId($termId);

                $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
                $term = $termModel->getById($termId);
                $cStatus = $term->status;

                if ($aktivnost == 'S' && $cStatus == 'Z') {
                    $this->set('message', 'Ovaj termin je zatvoren! Ne možete ga menjati!');
                    return;
                }

                if ($aktivnost == 'S' && $cStatus == 'S') {
                    $this->set('message', 'Nema rezervacije za ovaj termin!');
                    return;
                }

                if (!$reservations) {
                    $this->set('message', 'Ne postoji rezervacija!');
                    return null;
                }

                $ok = $this->obrisiRezervacijuZaTermin($termId);

                if (!$ok) {
                    return;
                }

                $reservation = $reservations[0];

                # pozivanje f-je za slanje e-maila o uspesnom otkazivanju rezervacije
                $this->notifyStudentOOtkazivanju($reservation);

                $this->redirect( \Configuration::BASE . 'student/profile');
            }

            $this->set('message', 'Ne menjajte zahtev rucno!!! >_<#');
        }
        
        #funkcija za pripremu i slanje maila
        public function notifyStudentORezervaciji($reservationId) {
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservation = $reservationModel->getById($reservationId);

            $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());
            $student = $studentModel->getById($reservation->student_id);

            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $termId = $reservation->term_id;
            $term = $termModel->getById($termId);

            $html = '<!doctype html><html><meta charset="utf-8"</head></body>';
            $html .= 'Vaša rezervacija termina &quot;';
            $html .= \htmlspecialchars($term->day);
            $html .= '. ';
            $html .= \htmlspecialchars($term->month);
            $html .= '. ';
            $html .= \htmlspecialchars($term->year);
            $html .= '.&quot; u ';
            $html .= \htmlspecialchars($term->hour);
            $html .= ' časova je uspešno izvršena';
            $html .= '</body></html>';

            $event = new \App\Core\EventHandlers\EmailEventHandler();

            $event->setSubject('Nova poruka');
            $event->setBody($html);
            $event->addAddress($student->email);
            $res = $event->handle();

            if ($res) {
                $eventModel = new \App\Models\EventModel($this->getDatabaseConnection());
                $eventModel->add([
                    'message' => $event->getMsg()
                ]);
            }

            $this->set('message', 'Poruka nije poslata.');
        }

        public function notifyStudentOOtkazivanju($reservation) {
            $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());
            $student = $studentModel->getById($reservation->student_id);
            
            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $termId = $reservation->term_id;
            $term = $termModel->getById($termId);
            
            $html = '<!doctype html><html><meta charset="utf-8"</head></body>';
            $html .= 'Vaša rezervacija za termin &quot;';
            $html .= \htmlspecialchars($term->day);
            $html .= '.';
            $html .= \htmlspecialchars($term->month);
            $html .= '.';
            $html .= \htmlspecialchars($term->year);
            $html .= '.&quot; u ';
            $html .= \htmlspecialchars($term->hour);
            $html .= ' časova je uspešno otkazana';
            $html .= '</body></html>';

            $event = new \App\Core\EventHandlers\EmailEventHandler();
            $event->setSubject('Nova poruka');
            $event->setBody($html);
            $event->addAddress($student->email);
            $res = $event->handle();

            if ($res) {
                $eventModel = new \App\Models\EventModel($this->getDatabaseConnection());
                $eventModel->add([
                    'message' => $event->getMsg()
                ]);
            }

            $this->set('message', 'Poruka nije poslata.');
        }
    }