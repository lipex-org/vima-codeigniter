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

use Vima\CodeIgniter\Models\RolePermissionModel;
use Vima\Core\Permission\Entities\Permission;
use Vima\Core\Role\Contracts\RolePermissionRepositoryInterface;
use Vima\Core\Role\Entities\Role;
use Vima\Core\Role\Entities\RolePermission;

class RolePermissionRepository implements RolePermissionRepositoryInterface
{
    protected RolePermissionModel $model;

    public function __construct()
    {
        $this->model = new RolePermissionModel();
    }

    public function getRolePermissions(Role $role): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->where($cols->roleId, $role->id)->findAll();

        return array_map(fn($row) => new RolePermission(
            id: $row['id'] ?? null,
            roleId: $row[$cols->roleId],
            permissionId: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function getPermissionRoles(Permission $permission): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->where($cols->permissionId, $permission->id)->findAll();

        return array_map(fn($row) => new RolePermission(
            id: $row['id'] ?? null,
            roleId: $row[$cols->roleId],
            permissionId: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function all(): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->findAll();
        return array_map(fn($row) => new RolePermission(
            id: $row['id'] ?? null,
            roleId: $row[$cols->roleId],
            permissionId: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function assign(RolePermission $permission): void
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $existing = $this->model->where([
            $cols->roleId => $permission->roleId,
            $cols->permissionId => $permission->permissionId
        ])->first();

        if ($existing) {
            $this->model->update($existing['id'], [
                $cols->constraints => $permission->constraints ? json_encode($permission->constraints) : null
            ]);
            return;
        }

        try {
            $this->model->insert([
                $cols->roleId => $permission->roleId,
                $cols->permissionId => $permission->permissionId,
                $cols->constraints => $permission->constraints ? json_encode($permission->constraints) : null
            ]);
        } catch (\Throwable $e) {
            $db = \Config\Database::connect();
            $roleExists = $db->table(service('vima_config')->tables->roles)->where('id', $permission->roleId)->countAllResults();
            $permExists = $db->table(service('vima_config')->tables->permissions)->where('id', $permission->permissionId)->countAllResults();
            
            $allRoles = $db->table(service('vima_config')->tables->roles)->get()->getResultArray();
            $allPerms = $db->table(service('vima_config')->tables->permissions)->get()->getResultArray();
            
            throw new \RuntimeException(
                "RolePermission insert failed! roleId: {$permission->roleId} (exists: {$roleExists}), permissionId: {$permission->permissionId} (exists: {$permExists}).\n" .
                "All Roles: " . json_encode($allRoles) . "\n" .
                "All Perms: " . json_encode($allPerms) . "\n" .
                "Details: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function revoke(RolePermission $permission): void
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $this->model->where([
            $cols->roleId => $permission->roleId,
            $cols->permissionId => $permission->permissionId
        ])->delete();
    }

    public function deleteAll(): void
    {
        $this->model->where('1=1')->delete();
    }
}
