<?php
    namespace App\Models;
    
    use App\Core\Model;
    use App\Core\Field;
    use App\Validators\NumberValidator;
    use App\Validators\StringValidator;
    use App\Validators\DateTimeValidator;
    use App\Validators\BitValidator;

    class EventModel extends Model {
        protected function getFields(): array {
            return [
                'event_id'    => new Field((new NumberValidator())->setIntegerLength(11), false),
                'created_at'  => new Field((new DateTimeValidator())->allowDate()->allowTime(), false ),

                'term_id'     => new Field((new NumberValidator())->setIntegerLength(11) ),
                'student_id'  => new Field((new NumberValidator())->setIntegerLength(11) ),
                'message'     => new Field((new \App\Validators\StringValidator)->setMaxLength(65535) ),
                'is_sent'     => new Field((new \App\Validators\BitValidator()))
            ];
        }
        public function getAllByStatus(int $isSent):array {
            $sql = 'SELECT * FROM `event` WHERE `is_sent` = 0 ORDER BY `created_at` ASC;';
            $prep = $this->getConnection()->prepare($sql);
            $res = $prep->execute([$isSent]);

            if (!$res) {
                return [];
            }

            return $prep->fetchAll(\PDO::FETCH_OBJ);
        }
    }
