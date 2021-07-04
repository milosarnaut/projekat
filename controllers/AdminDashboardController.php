<?php
    namespace App\Controllers;

    class AdminDashboardController extends \App\Core\Role\AdminRoleController {
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
            
            $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());
            $students = $studentModel->getAll();
            $this->set('students', $students);
        }

        private function obrisiRezervacijuZaTermin($termId) {
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservations = $reservationModel->getAllByTermId($termId);
            if (!$reservations) {
                $this->set('message', 'Ne postoji rezervacija.');
                return null;
            }

            $reservation = $reservations[0];
            
            $reservations = $reservationModel->getAllByTermId($reservation->term_id);
            if (!$reservations) {
                $this->set('message', 'Ne postoji rezervacija!');
                return false;
            }

            $reservation = $reservations[0];

            $ok = $reservationModel->deleteByTermId($reservation->term_id);

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
    
        public function postEditTermAdmin() {
            $termId = \filter_input(INPUT_POST, 'term_id', FILTER_SANITIZE_NUMBER_INT);
    
            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $term = $termModel->getById($termId);
            
            #   Proveriti da li postoji term
            if(!$term){
                return false;
            }
            
            $this->set('term', $term);
            
            $cStatus = $term->status;
            $aktivnost = filter_input(INPUT_POST, 'radio', FILTER_SANITIZE_STRING);

            #   Ako je trazeno zakljucavanje:
            if ($cStatus == 'Z' && $aktivnost == 'Z') {
                #   Da li je vec zakljucan, ako jeste, prekid
                $this->set('message', 'Termin je već zaključan.');
                return;
            }

            #   Ako je trazeno slobodno:
            if ($cStatus == 'S' && $aktivnost == 'S') {
                #   Da li je vec slobodno, ako jeste, prekid
                $this->set('message', 'Termin je već slobodan.');
                return;
            }

            #   Ako je trazeno rezervisan:
            if ($cStatus == 'R' && $aktivnost == 'R') {
                #   Da li je vec rez, ako jeste, prekid
                $this->set('message', 'Termin je već rezervisan.');
                return;
            }
    
            $editData = [
                'status' => $aktivnost
            ];

            $ok = $termModel->editById($termId, $editData);

            if (!$ok) {
                $this->set('message', 'Rezervacija termina nije uspela.');
                return;
            }

            if ($aktivnost == 'R') {
                $indexNumber = \filter_input(INPUT_POST, 'index_number_input', FILTER_SANITIZE_NUMBER_INT);

                $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());
                $student = $studentModel->getByFieldName('index_number', $indexNumber);
                
                if(!property_exists($student, 'student_id')) {
                    $this->set('message', 'Nije uspelo rezervisanje termina');
                    return;
                }

                $studentId = $student->student_id;
                
                # Dodavanje rezervacije
                $addDataReservation = [
                    'student_id' => $studentId,
                    'term_id'    => $termId,
                    'admin_id'   => $this->getSession()->get('admin_id')
                ];

                $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
                $res = $reservationModel->add($addDataReservation);

                if (!$res) {
                    $this->set('message', 'ERROR');
                    return;
                }
            }

            if ($cStatus == 'R' && $aktivnost == 'S') {

                $ok = $this->obrisiRezervacijuZaTermin($termId);

                if (!$ok) {
                    return;
                }

                # pozivanje f-je za slanje e-maila
                /*$this->notifyStudentOOtkazivanju();*/

                $this->redirect( \Configuration::BASE . 'admin/profile');
            }

            # !
            if ($cStatus == 'Z' && $aktivnost == 'S') {
                
                $editData = [
                    'status' => $aktivnost
                ];
                
                $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
                $ok = $termModel->editById($termId, $editData);
                
                if (!$ok) {
                    $this->set('message', 'Nije uspelo oslobadjanje termina');
                    return;
                }
            }

            $this->redirect(\Configuration::BASE . 'admin/profile');
        }

        private function getCalendarData(int $year, int $month) {
            $firstDay = sprintf("%04d-%02d-%02d", $year, $month, 1);
            $fdts = strtotime($firstDay);
            $current = $fdts;

            $days = [];

            for ($i=0; $i<date('N', $current)-1; $i++) {
                $days[] = (object) [ 'show' => false, 'date' => null, 'dow' => $i+1 ];
            }

            do {
                $days[] = (object) [
                    'show' => true,
                    'date' => date('Y-m-d', $current),
                    'day' => date('d', $current),
                    'month' => date('m', $current),
                    'year' => date('Y', $current),
                    'dow' => date('N', $current)
                ];
                $current += 24 * 60 * 60;
                $cMonth = date('m', $current);
            } while ($cMonth == $month);

            for ($i=date('N', $current); $i<=7; $i++) {
                $days[] = (object) [ 'show' => false, 'date' => null, 'dow' => $i ];
            }
            return $days;
        }

        public function calendarAdmin($year = null, $month = null) {
            if (!$year) {
                $year = date('Y');
            }

            if (!$month) {
                $month = date('m');
            }

            if ($month < 1 || $month > 12) {
                $month = date('Y');
            }

            $calendar = $this->getCalendarData($year, $month);
            $this->set('calendar', $calendar);

            $this->set('year', $year);
            $this->set('month', $month);

            $this->set('meseci', [0, 'januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar']);

            $this->set('currentDate', date('Y-m-d'));
        }

        public function agendaAdmin($year = 0, $month = 0, $day = 0) {
            if ($year == 0) {
                $year = date('Y');
            }

            if ($month == 0) {
                $month = date('n');
            }

            if ($day == 0) {
                $day = date('j');
            }

            $this->set('year', intval($year));
            $this->set('month', intval ($month));
            $this->set('day', intval ($day));

            $this->set('currentDate', date('Y-m-d'));

            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $terms = $termModel->getAllByYearMonthAndDay($year, $month, $day);

            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservations = [];

            foreach ($terms as $term) {
                $inTermReservations = $reservationModel->getAllByTermId($term->term_id);

                $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());

                #najblize lamda f-jama, mapiranje.
                $inTermReservations = array_map(function($reservation) use($term, $studentModel) {
                    $reservation->term = $term;
                    $reservation->student = $studentModel->getById($reservation->student_id);
                    return $reservation;
                }, $inTermReservations);

                $reservations = array_merge($reservations, $inTermReservations);
            }

            $this->set('terms', $terms);
            $this->set('reservations', $reservations);
        }

        public function postDelete($id) {
            $reservationModel = new \App\Models\ReservationModel($this->getDatabaseConnection());
            $reservation = $reservationModel->getById($id);

            if (!$reservation) {
                $this->set('message', 'Ne postoji rezervacija.');
                return;
            }

            $termModel = new \App\Models\TermModel($this->getDatabaseConnection());
            $term = $termModel->getById($reservation->term_id);            

            $ok = $reservationModel->deleteById($id);

            if (!$ok) {
                $this->set('message', 'Došlo je do greške prilikom pokušaja brisanja rezervacije!');
                return;
            }

            $this->redirect(\Configuration::BASE . 'admin/agenda/' . sprintf('%4d/%02d/%02d', $term->year, $term->month, $term->day));
        }
    }