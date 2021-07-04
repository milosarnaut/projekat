<?php
    namespace App\Core;

    class DatabaseConfiguration {
        private $host;
        private $user;
        private $pass;
        private $name;

        public function __construct(string $host, string $user, string $pass, string $name) {
            $this->host = $host;
            $this->name = $name;
            $this->user = $user;
            $this->pass = $pass;
        }

        public function getSourceString(): string {
            return "mysql:host={$this->host};dbname={$this->name};charset=utf8";
        }

        public function getUsername(): string {
            return $this->user;
        }

        public function getPassword(): string {
            return $this->pass;
        }
    }