<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vima\CodeIgniter\Models;

use CodeIgniter\Model;

/**
 * Class UserRoleDenyModel
 *
 * Model for the user_role_denies table.
 */
class UserRoleDenyModel extends Model
{
    protected $table = '';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['user_id', 'role_id', 'reason', 'expires_at', 'created_at'];

    public function __construct()
    {
        parent::__construct();

        $config = config('Vima');
        $this->table = $config->tables->userRoleDenies ?? 'user_role_denies';

        $cols = $config->columns->userRoleDenies;
        $this->allowedFields = [
            $cols->userId,
            $cols->roleId,
            $cols->reason,
            $cols->expiresAt,
            $cols->createdAt,
        ];
    }
}
