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

use Vima\CodeIgniter\Models\UserRoleModel;
use Vima\Core\User\Contracts\UserRoleRepositoryInterface;
use Vima\Core\User\Entities\UserRole;


class UserRoleRepository implements UserRoleRepositoryInterface
{
    protected UserRoleModel $model;

    public function __construct()
    {
        $this->model = new UserRoleModel();
    }

    public function getRolesForUser(int|string $user_id): array
    {
        $cols = service('vima_config')->columns->userRoles;
        $data = $this->model->asArray()->where($cols->userId, $user_id)->findAll();

        return array_map(fn($row) => new UserRole(
            id: $row[$cols->id] ?? null,
            userId: $row[$cols->userId],
            roleId: $row[$cols->roleId]
        ), $data);
    }

    public function assign(UserRole $userRole): void
    {
        $cols = service('vima_config')->columns->userRoles;

        $existing = $this->model->asArray()->where([
            $cols->userId => $userRole->userId,
            $cols->roleId => $userRole->roleId
        ])->first();

        if ($existing) {
            return;
        }

        $data = [
            $cols->userId => $userRole->userId,
            $cols->roleId => $userRole->roleId
        ];
        $id = $this->model->insert($data);

        if ($id === false) {
            throw new \RuntimeException(
                "UserRole insert failed! Data: " . json_encode($data) . 
                ", Model Errors: " . json_encode($this->model->errors()) . 
                ", DB Error: " . json_encode($this->model->db->error())
            );
        }

        $userRole->id = $id;
    }

    public function revoke(UserRole $userRole): void
    {
        $cols = service('vima_config')->columns->userRoles;

        $this->model->where([
            $cols->userId => $userRole->userId,
            $cols->roleId => $userRole->roleId
        ])->delete();
    }
}
