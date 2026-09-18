<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Repositories;

use Vima\CodeIgniter\Models\RoleParentModel;
use Vima\Core\Role\Contracts\RoleParentRepositoryInterface;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Role\Entities\RoleParent;

class RoleParentRepository implements RoleParentRepositoryInterface
{
    protected RoleParentModel $model;

    public function __construct()
    {
        $this->model = new RoleParentModel();
    }

    public function assign(RoleParent $relationship): void
    {
        $cols = service('vima_config')->columns->roleParents;

        $roleId = $relationship->roleId;
        if (! is_numeric($roleId)) {
            $role = service('vima_roles')->findByName((string) $roleId);
            if ($role && $role->id) {
                $roleId = (int) $role->id;
            }
        }

        $parentId = $relationship->parentId;
        if (! is_numeric($parentId)) {
            $pRole = service('vima_roles')->findByName((string) $parentId);
            if ($pRole && $pRole->id) {
                $parentId = (int) $pRole->id;
            }
        }

        $data = [
            $cols->roleId => $roleId,
            $cols->parentId => $parentId,
        ];

        // Check for existing relationship to avoid duplicates
        $exists = $this->model->where($data)->first();

        if (!$exists) {
            $this->model->insert($data);
        }
    }

    public function remove(RoleParent $relationship): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $this->model
            ->where($cols->roleId, $relationship->roleId)
            ->where($cols->parentId, $relationship->parentId)
            ->delete();
    }

    public function clearParents(Role $role): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $this->model->where($cols->roleId, $role->id)->delete();
    }

    public function getParents(Role $role): array
    {
        $cols = service('vima_config')->columns->roleParents;
        $relationships = $this->model->where($cols->roleId, $role->id)->findAll();

        return array_map(fn($data) => new RoleParent(
            id: $data[$cols->id] ?? null,
            roleId: $data[$cols->roleId],
            parentId: $data[$cols->parentId]
        ), $relationships);
    }

    public function getChildren(Role $role): array
    {
        $cols = service('vima_config')->columns->roleParents;
        $relationships = $this->model->where($cols->parentId, $role->id)->findAll();

        return array_map(fn($data) => new RoleParent(
            id: $data[$cols->id] ?? null,
            roleId: $data[$cols->roleId],
            parentId: $data[$cols->parentId]
        ), $relationships);
    }

    public function deleteAll(): void
    {
        $this->model->where('1=1')->delete();
    }
}
