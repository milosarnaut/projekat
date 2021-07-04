<?php
    namespace App\Models;
    
    use App\Core\Model;
    use App\Core\Field;
    use App\Validators\NumberValidator;
    use App\Validators\DateTimeValidator;

    class ReservationModel extends Model{

        protected function getFields(): array{
            return [
                'reservation_id' => new Field((new NumberValidator())->setIntegerLength(11), false),

                'student_id'     => new Field((new NumberValidator())->setIntegerLength(11) ),
                'term_id'        => new Field((new NumberValidator())->setIntegerLength(11) ),
                'admin_id'       => new Field((new NumberValidator())->setIntegerLength(11) ),
                'created_at'     => new Field((new DateTimeValidator())->allowDate()->allowTime(), false )
            ];
        }

        public function getAllByAdminId(int $adminId): array {
            return $this->getAllByFieldName('admin_id', $adminId);
        }

        public function getAllByStudentId(int $studentId): array {
            return $this->getAllByFieldName('student_id', $studentId);
        }

        public function getAllByTermId(int $termId): array {
            return $this->getAllByFieldName('term_id', $termId);
        }

        public function deleteByTermId(int $termId) {
            $sql = 'DELETE FROM `reservation` WHERE `term_id` = ?;';
            $prep = $this->getConnection()->prepare($sql);
            return $prep->execute( [ $termId ] );
        }
    }