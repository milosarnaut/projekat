<?php
    namespace App\Controllers;

    class EventHandlerController extends \App\Core\Controller {
        public function handle(){
            $host = \filter_input(INPUT_SERVER, 'HTTP_HOST');

            if ($host !== 'localhost') {
                return;
            }

            $eventModel = new \App\Models\EventModel($this->getDatabaseConnection);
            $events = $eventModel->getAllByStatus();

            if (!count($events)){
                return;
            }

            $this->handleEmails($events);
        }

        private function handleEmails(array $events) {
            foreach ($events as $event) {
                $this->handleEmailEvent($event);
            }
        }

        private function handleEmailEvent($event) {
            $emailEventHandler = new \App\EventHandlers\EmailEventHandler();
            $emailEventHandler->setMsg($event->data);
            $newStatus = $emailEventHandler->handle();

            $eventModel = new \App\Models\EventModel($this->getDatabaseConnection());
            $eventModel->editById($event->event_id, [
                'is_sent' => $newStatus
            ]);
        }
    }
