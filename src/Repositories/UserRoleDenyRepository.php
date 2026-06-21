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

namespace Vima\CodeIgniter\Repositories;

use CodeIgniter\Model;
use Vima\CodeIgniter\Models\UserRoleDenyModel;
use DateTimeInterface;
use Vima\Core\Config\Schema\Columns;
use Vima\Core\User\Contracts\UserRoleDenyRepositoryInterface;
use Vima\Core\User\Entities\UserRoleDeny;

/**
 * Class UserRoleDenyRepository
 *
 * CI4 implementation of UserRoleDenyRepositoryInterface.
 */
class UserRoleDenyRepository implements UserRoleDenyRepositoryInterface
{
    protected Model $model;
    protected Columns $columns;

    public function __construct(?Model $model = null, ?Columns $columns = null)
    {
        $this->model = $model ?? new UserRoleDenyModel();
        $this->columns = $columns ?? \config('Vima')->columns;
    }

    public function add(string|int $user_id, string|int $role_id, ?string $reason = null, ?DateTimeInterface $expiresAt = null): void
    {
        $cols = $this->columns->userRoleDenies;

        $exists = $this->model->where([
            $cols->userId => $user_id,
            $cols->roleId => $role_id,
        ])->first();

        $data = [
            $cols->userId => $user_id,
            $cols->roleId => $role_id,
            $cols->reason => $reason,
            $cols->expiresAt => $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : null,
        ];

        if (!$exists) {
            $data[$cols->createdAt] = date('Y-m-d H:i:s');
            $this->model->insert($data);
        } else {
            $this->model->update($exists['id'], $data);
        }
    }

    public function remove(string|int $user_id, string|int $role_id): void
    {
        $cols = $this->columns->userRoleDenies;

        $this->model->where([
            $cols->userId => $user_id,
            $cols->roleId => $role_id,
        ])->delete();
    }

    public function isDenied(string|int $user_id, string|int $role_id): bool
    {
        $cols = $this->columns->userRoleDenies;

        $deny = $this->model->where([
            $cols->userId => $user_id,
            $cols->roleId => $role_id,
        ])->first();

        if (!$deny) {
            return false;
        }

        if ($deny[$cols->expiresAt] && strtotime($deny[$cols->expiresAt]) < time()) {
            return false;
        }

        return true;
    }

    public function getDeniedRoles(string|int $user_id): array
    {
        $cols = $this->columns->userRoleDenies;
        $roleCols = $this->columns->roles;
        $tables = \config('Vima')->tables;

        $results = $this->model->db->table($tables->userRoleDenies)
            ->select($tables->userRoleDenies . '.*')
            ->where($tables->userRoleDenies . '.' . $cols->userId, $user_id)
            ->get()
            ->getResult();

        $denies = [];
        foreach ($results as $row) {
            $denies[] = new UserRoleDeny(
                id: (int) $row->id,
                userId: $row->{$cols->userId},
                roleId: $row->{$cols->roleId},
                reason: $row->{$cols->reason} ?? null,
                expiresAt: $row->{$cols->expiresAt} ?? null,
                createdAt: $row->{$cols->createdAt} ?? null
            );
        }

        return $denies;
    }
}
