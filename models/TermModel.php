<?php
    namespace App\Models;
    
    use App\Core\Model;
    use App\Core\Field;
    use App\Validators\NumberValidator;
    use App\Validators\DateTimeValidator;
    use App\Validators\StringValidator;

    class TermModel extends Model{
        protected function getFields(): array{
            return [
                'term_id'    => new Field((new NumberValidator())->setIntegerLength(11), false),

                'year'       => new Field((new NumberValidator())->setIntegerLength(4) ),
                'month'      => new Field((new NumberValidator())->setIntegerLength(2) ),
                'day'        => new Field((new NumberValidator())->setIntegerLength(2) ),
                'hour'       => new Field((new NumberValidator())->setIntegerLength(2) ),
                'status'     => new Field((new StringValidator())->setMaxLength(1))
            ];
        }

        public function getAllByTermId(int $termId): array {
            return $this->getAllByFieldName('term_id', $termId);
        }
        
        public function getAllByYearAndMonth(int $year, int $month): array {
            $items = [];
            $sql = 'SELECT * FROM `term` WHERE `year` = ? AND `month` = ?';
            $prep = $this->getConnection()->prepare($sql);
            $res = $prep->execute([$year, $month]);
            if($res){
                $items = $prep->fetchAll(\PDO::FETCH_OBJ);
            } 
            return $items;
        }
        
        public function getAllByYearMonthAndDay(int $year, int $month, int $day): array {
            $items = [];
            $sql = 'SELECT * FROM `term` WHERE `year` = ? AND `month` = ? AND `day` = ?';
            $prep = $this->getConnection()->prepare($sql);
            $res = $prep->execute([$year, $month, $day]);
            if($res){
                $items = $prep->fetchAll(\PDO::FETCH_OBJ);
            } 
            return $items;
        }
        
        public function getAllAfterDateNDays(string $date, int $days): array {
            $items = [];
            $sql = 'SELECT * FROM `term` WHERE CONCAT(`year`, "-", IF(`month` < 10, CONCAT("0", `month`), `month`), "-", IF(`day` < 10, CONCAT("0", `day`), `day`)) BETWEEN ? AND ? + INTERVAL ? DAY';
            $prep = $this->getConnection()->prepare($sql);
            $res = $prep->execute([$date, $date, $days]);
            if($res){
                $items = $prep->fetchAll(\PDO::FETCH_OBJ);
            } 
            return $items;
        }
    }