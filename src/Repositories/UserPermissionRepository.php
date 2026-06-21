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

use Vima\CodeIgniter\Models\UserPermissionModel;
use Vima\Core\User\Contracts\UserPermissionRepositoryInterface;
use Vima\Core\User\Entities\UserPermission;


class UserPermissionRepository implements UserPermissionRepositoryInterface
{
    protected UserPermissionModel $model;

    public function __construct()
    {
        $this->model = new UserPermissionModel();
    }

    public function findByUserId(int|string $userId): array
    {
        $cols = service('vima_config')->columns->userPermissions;
        $data = $this->model->where($cols->userId, $userId)->findAll();
        return array_map(fn($row) => new UserPermission(
            id: $row[$cols->id] ?? null,
            userId: $row[$cols->userId],
            permissionId: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function add(UserPermission $userPermission): void
    {
        $cols = service('vima_config')->columns->userPermissions;
        $existing = $this->model->asArray()->where([
            $cols->userId => $userPermission->userId,
            $cols->permissionId => $userPermission->permissionId
        ])->first();

        if ($existing) {
            $this->model->update($existing['id'], [
                $cols->constraints => $userPermission->constraints ? json_encode($userPermission->constraints) : null
            ]);
            return;
        }

        $id = $this->model->insert([
            $cols->userId => $userPermission->userId,
            $cols->permissionId => $userPermission->permissionId,
            $cols->constraints => $userPermission->constraints ? json_encode($userPermission->constraints) : null
        ]);

        $userPermission->id = $id;
    }

    public function remove(UserPermission $userPermission): void
    {
        $cols = service('vima_config')->columns->userPermissions;
        $this->model->where([
            $cols->userId => $userPermission->userId,
            $cols->permissionId => $userPermission->permissionId
        ])->delete();
    }
}
