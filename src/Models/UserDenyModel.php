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
 * Class UserDenyModel
 *
 * Model for the user_denies table.
 */
class UserDenyModel extends Model
{
    protected $table = ''; // Default, will be overridden by repository/config
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['user_id', 'permission_id'];

    public function __construct()
    {
        parent::__construct();

        $config = config('Vima');
        $this->table = $config->tables->userDenies ?? 'user_denies';
        $cols = $config->columns->userDenies;
        $this->allowedFields = [
            $cols->userId,
            $cols->permissionId,
            $cols->namespace,
            $cols->reason,
            $cols->expiresAt,
            $cols->createdAt
        ];
    }
}
