<?php
namespace App\Controllers;

class MainController extends \App\Core\Controller{
    
    public function home() {          
    }

    #REGISTER USER
    public function getRegister() {
            
    }

    public function postRegister() {
        $name        = \filter_input(INPUT_POST, 'reg_name', FILTER_SANITIZE_STRING);
        $surname     = \filter_input(INPUT_POST, 'reg_surname', FILTER_SANITIZE_STRING);
        $indexNumber = \filter_input(INPUT_POST, 'index_number', FILTER_SANITIZE_NUMBER_INT);
        $email       = \filter_input(INPUT_POST, 'reg_email', FILTER_SANITIZE_EMAIL);
        $username    = \filter_input(INPUT_POST, 'reg_username', FILTER_SANITIZE_STRING);
        $password1   = \filter_input(INPUT_POST, 'reg_password_1', FILTER_SANITIZE_STRING);
        $password2   = \filter_input(INPUT_POST, 'reg_password_2', FILTER_SANITIZE_STRING);
          
        if ($password1 !== $password2) {
            $this->set('message', 'Doslo je do greške: Niste uneli dva puta istu lozinku.');
            return;
        }

        $validanPassword = (new \App\Validators\StringValidator())
            ->setMinLength(7)
            ->setMaxLength(120)
            ->isValid($password1);

        if ( !$validanPassword) {
            $this->set('message', 'Doslo je do greške: Lozinka nije ispravnog formata.');
            return;
        }

        $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());

        $student = $studentModel->getByFieldName('email', $email);
        if ($student) {
            $this->set('message', 'Doslo je do greške: Već postoji korisnik sa tom adresom e-pošte.');
            return;
        }

        $student = $studentModel->getByFieldName('username', $username);
        if ($student) {
            $this->set('message', 'Doslo je do greške: Već postoji korisnik sa tim korisničkim imenom.');
            return;
        }

        $student = $studentModel->getByFieldName('index_number', $indexNumber);
        if ($student) {
            $this->set('message', 'Doslo je do greške: Već postoji korisnik sa tim brojem indeksa.');
            return;
        }

        $passwordHash = \password_hash($password1, PASSWORD_DEFAULT);

        $studentId = $studentModel->add([
            'name'          => $name,
            'surname'       => $surname,
            'index_number'  => $indexNumber,
            'email'         => $email,
            'username'      => $username,
            'password_hash' => $passwordHash,     
            ]);
    
        if (!$studentId) {
            $this->set('message', 'Doslo je do greške: Nije bilo uspešno registrovanje naloga.');
            return;
        }

        $this->set('message', 'Napravljen je novi nalog. Sada možete da se prijavite.');
    }
	#REGISTER USER.
		
	#USER LOGIN
    public function getLogin() {
    }

    public function postLogin() {
        $username = \filter_input(INPUT_POST, 'login_username', FILTER_SANITIZE_STRING);
        $password = \filter_input(INPUT_POST, 'login_password', FILTER_SANITIZE_STRING);

        $validanPassword = (new \App\Validators\StringValidator())
            ->setMinLength(7)
            ->setMaxLength(120)
            ->isValid($password);

        if ( !$validanPassword) {
            $this->set('message', 'Doslo je do greške: Lozinka nije ispravnog formata.');
            return;
        }

        $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());

        $student = $studentModel->getByFieldName('username', $username);
        if (!$student) {
            $this->set('message', 'Doslo je do greške: Ne postoji korisnik sa tim korisničkim imenom.');
            return;
        }

        if (!password_verify($password, $student->password_hash)) {
            sleep(1); #usporava brute force attack, tako sto ceka 1sec na msg za neispravnu pswd
            $this->set('message', 'Doslo je do greške: Lozinka nije ispravna.');
            return;
        }

        $this->getSession()->put('student_id', $student->student_id);
        $this->getSession()->save();

        /*$studentLoginModel = new \App\Models\StudentLoginModel($this->getDatabaseConnection());
            
            $ipAddress = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
            
            $log = $studentLoginModel->add(
                [
                    'student_id' => $this->getSession()->get('student_id'),
                    'ip_address' => $ipAddress
                ]
            );*/

        $this->redirect(\Configuration::BASE . 'student/profile');
    }

    public function getLogout() {
        $this->getSession()->remove('student_id');
        $this->getSession()->save();

        $this->redirect(\Configuration::BASE);
    }
	#USER LOGIN.
    
    #RESTART PASS
    private function generatePass(): string {
        $supported = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#!@?~";
        $pass = "";
        for ($i=0; $i<rand(8, 12); $i++) {
            $pass .= $supported[rand(0, strlen($supported)-1)];
        }
        return $pass;
    }

    public function getRestartPass() {
    }

    public function postRestartPass() {
        $email = \filter_input(INPUT_POST, 'input_email', FILTER_SANITIZE_STRING);
        
        $studentModel = new \App\Models\StudentModel($this->getDatabaseConnection());

        $student = $studentModel->getByFieldName('email', $email);
        if (!$student) {
            sleep(1);
            $this->set('message', 'Ukoliko postoji e-mail, nova lozinka će biti prosleđena na isti.');
            return;
        }

        $password = $this->generatePass();
        $passwordHash = \password_hash($password, PASSWORD_DEFAULT);

        $res = $studentModel->editById($student->student_id, [
            'password_hash' => $passwordHash
        ]);

        if (!$res) {
            sleep(1);
            $this->set('message', 'Ukoliko postoji e-mail, nova lozinka će biti prosleđena na isti.');
            return;
        }
    
        #slanje mejla
        $this->notifyStudentOfPasswordChange($email, $password);
        $this->set('message', 'Ukoliko postoji e-mail, nova lozinka će biti prosleđena na isti.');
    }

    public function notifyStudentOfPasswordChange($email, $password) {
        $html = '<!doctype html><html><meta charset="utf-8"</head></body>';
        $html .= 'Uspešno ste promenili lozinku. Vaša nova lozinka je: &quot;';
        $html .= $password;
        $html .= '&quot;';
        $html .= '</body></html>';

        $event = new \App\Core\EventHandlers\EmailEventHandler();
        $event->setSubject('Nova poruka');
        $event->setBody($html);
        $event->addAddress($email);
        $res = $event->handle();

        if ($res) {
            $eventModel = new \App\Models\EventModel($this->getDatabaseConnection());
            $eventModel->add([
                'message' => $event->getMsg()
            ]);
        }

        $this->set('message', 'Poruka nije poslata.');
    }
    #RESTART PASS.
    
	#ADMIN REGISTER
	public function getAdminRegister() {
			
	}

    public function postRegisterAdmin() {
        $name       = \filter_input(INPUT_POST, 'reg_name', FILTER_SANITIZE_STRING);
        $surname    = \filter_input(INPUT_POST, 'reg_surname', FILTER_SANITIZE_STRING);
        $email      = \filter_input(INPUT_POST, 'reg_email', FILTER_SANITIZE_EMAIL);
        $username   = \filter_input(INPUT_POST, 'reg_username', FILTER_SANITIZE_STRING);
        $password1  = \filter_input(INPUT_POST, 'reg_password_1', FILTER_SANITIZE_STRING);
        $password2  = \filter_input(INPUT_POST, 'reg_password_2', FILTER_SANITIZE_STRING);
        
        if ($password1 !== $password2) {
            $this->set('message', 'Doslo je do greške: Niste uneli dva puta istu lozinku.');
            return;
        }

        $validanPassword = (new \App\Validators\StringValidator())
            ->setMinLength(7)
            ->setMaxLength(120)
            ->isValid($password1);

        if ( !$validanPassword) {
            $this->set('message', 'Doslo je do greške: Lozinka nije ispravnog formata.');
            return;
        }

        $adminModel = new \App\Models\AdminModel($this->getDatabaseConnection());

        $admin = $adminModel->getByFieldName('email', $email);
        if ($admin) {
            $this->set('message', 'Doslo je do greške: Već postoji admin sa tom adresom e-pošte.');
            return;
        }

        $admin = $adminModel->getByFieldName('username', $username);
        if ($admin) {
            $this->set('message', 'Doslo je do greške: Već postoji admin sa tim korisničkim imenom.');
            return;
        }

        $passwordHash = \password_hash($password1, PASSWORD_DEFAULT);

        $adminId = $adminModel->add([
            'name'          => $name,
            'surname'       => $surname,
            'email'         => $email,
            'username'      => $username,
            'password_hash' => $passwordHash 
        ]);

        if (!$adminId) {
            $this->set('message', 'Doslo je do greške: Nije bilo uspešno registrovanje naloga.');
            return;
        }

        $this->set('message', 'Napravljen je novi nalog. Sada možete da se prijavite.');
    }
    
	#ADMIN REGISTER.
	
	#ADMIN LOGIN
	public function getLoginAdmin() {

        }

    public function postLoginAdmin() {
        $username = \filter_input(INPUT_POST, 'login_username', FILTER_SANITIZE_STRING);
        $password = \filter_input(INPUT_POST, 'login_password', FILTER_SANITIZE_STRING);

        $validanPassword = (new \App\Validators\StringValidator())
            ->setMinLength(7)
            ->setMaxLength(120)
            ->isValid($password);

        if ( !$validanPassword) {
            $this->set('message', 'Doslo je do greške: Lozinka nije ispravnog formata.');
            return;
        }

        $adminModel = new \App\Models\AdminModel($this->getDatabaseConnection());

        $admin = $adminModel->getByFieldName('username', $username);
        if (!$admin) {
            $this->set('message', 'Doslo je do greške: Ne postoji ADMIN sa tim korisničkim imenom.');
            return;
        }

        if (!password_verify($password, $admin->password_hash)) {
            sleep(1); #usporava brute force attack, tako sto ceka 1sec na msg za neispravnu pswd
            $this->set('message', 'Doslo je do greške: Lozinka nije ispravna.');
            return;
        }

        $this->getSession()->put('admin_id', $admin->admin_id);
        $this->getSession()->save();

        /*$adminLoginModel = new \App\Models\AdminLoginModel($this->getDatabaseConnection());
            
            $ipAddress = \filter_input(INPUT_SERVER, 'REMOTE_ADDR');
            
            $log = $adminLoginModel->add(
                [
                    'admin_id' => $this->getSession()->get('admin_id'),
                    'ip_address' => $ipAddress
                ]
            );*/

        $this->redirect(\Configuration::BASE . 'admin/profile');
    }
    #ADMIN LOGIN.
        public function getLogoutAdmin() {
            $this->getSession()->remove('admin_id');
            $this->getSession()->save();

            $this->redirect(\Configuration::BASE);
        }
    }