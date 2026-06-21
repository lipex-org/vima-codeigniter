<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Models;

use CodeIgniter\Model;

class RoleParentModel extends Model
{
    protected $table = '';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['role_id', 'parent_id'];

    public function __construct()
    {
        parent::__construct();
        $cols = service('vima_config')->columns->roleParents;
        $this->table = service('vima_config')->tables->roleParents;
        $this->allowedFields = [$cols->roleId, $cols->parentId];
    }
}
