<?php
    namespace App\Core;

    class Controller {
        private $dbc;
        private $session;
        private $data = [];

        public function __pre() {
            $this->set('role', 'none');

            if($this->getSession()->get('sudent_id') !== null){
                $this->set('role', 'student');
            }
            if($this->getSession()->get('admin_id') !== null){
                $this->set('role', 'admin');
            }
        }

        final public function __construct(\App\Core\DatabaseConnection &$dbc){
            $this->dbc = $dbc;
        }

        #GET
        final public function &getSession(): \App\Core\Session\Session {
            return $this->session;
        }

        #SET
        final public function setSession(\App\Core\Session\Session &$session) {
            $this->session = $session;
        }

        final public function &getDatabaseConnection():\App\Core\DatabaseConnection{
            return $this->dbc;
        }

        final protected function set(string $name, $value): bool{
            $result = false;

            if(preg_match('/^[a-z][a-z0-9]+(?:[A-Z][a-z0-9]+)*$/', $name)){
                $this->data[$name] = $value;
                $result = true;
            }
            return $result;
        }

        final public function getData(): array{
            return $this->data;
        }

        #preusmeriti korisnika GET metodom
        final protected function redirect(string $path, int $code = 303) {
            ob_clean();
            header('Location: ' . $path, true, $code);
            exit;
        }
    }
