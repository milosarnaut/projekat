<?php
    namespace App\Models;
    
    use App\Core\Model;
    use App\Core\Field;
    use App\Validators\NumberValidator;
    use App\Validators\StringValidator;
    use App\Validators\DateTimeValidator;
    use App\Validators\BitValidator;

    class AdminModel extends Model{

        protected function getFields(): array{
            return [
                'admin_id'      => new Field((new NumberValidator())->setIntegerLength(11), false),

                'name'          => new Field((new \App\Validators\StringValidator)->setMaxLength(64) ),
                'surname'       => new Field((new \App\Validators\StringValidator)->setMaxLength(64) ),
                'email'         => new Field((new \App\Validators\StringValidator)->setMaxLength(255) ),
                'username'      => new Field((new \App\Validators\StringValidator)->setMaxLength(32) ),
                'password_hash' => new Field((new \App\Validators\StringValidator(0, 128)) ),
                'created_at'    => new Field((new DateTimeValidator())->allowDate()->allowTime(), false ),
                'is_active'     => new Field((new \App\Validators\BitValidator()))
            ];
        }

        public function getAllByAdminId(int $adminId): array {
            return $this->getAllByFieldName('admin_id', $adminId);
        }
    }