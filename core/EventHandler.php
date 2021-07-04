<?php
    namespace App\Core;

    interface EventHandler {
        public function getMsg(): string;
        public function setMsg(string $serialisedData);
        public function handle():string;
    }
