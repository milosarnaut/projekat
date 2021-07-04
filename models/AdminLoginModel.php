<?php
    namespace App\Models;

    use App\Core\Model;
    use App\Core\Field;
    use App\Validators\StringValidator;
    use App\Validators\NumberValidator;
    use App\Validators\BitValidator;
    use App\Validators\DateTimeValidator;

    class AdminLoginModel extends Model {
        protected function getFields(): array {
            return [
                'admin_login_id'  => new Field((new NumberValidator())->setIntegerLength(11), false),

                'admin_id'        => new Field((new NumberValidator())->setIntegerLength(11) ),
                'ip_address'      => new Field((new \App\Validators\StringValidator(7, 255)) ),
                'created_at'      => new Field((new DateTimeValidator())->allowDate()->allowTime(), false )
            ];
        }
    }
