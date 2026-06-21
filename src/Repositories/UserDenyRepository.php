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
use DateTimeInterface;
use Vima\CodeIgniter\Models\UserDenyModel;
use Vima\Core\Config\Schema\Columns;
use Vima\Core\User\Contracts\UserDenyRepositoryInterface;
use Vima\Core\User\Entities\UserDeny;


/**
 * Class UserDenyRepository
 *
 * CI4 implementation of UserDenyRepositoryInterface.
 */
class UserDenyRepository implements UserDenyRepositoryInterface
{
    protected Model $model;
    protected Columns $columns;

    public function __construct(?Model $model = null, ?Columns $columns = null)
    {
        $this->model = $model ?? new UserDenyModel();
        $this->columns = $columns ?? new Columns(); // This usually comes from global config
    }

    public function add(string|int $user_id, string|int $permission_id, ?string $reason = null, ?DateTimeInterface $expiresAt = null): void
    {
        $cols = $this->columns->userDenies;

        $exists = $this->model->where([
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
        ])->first();

        $data = [
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
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

    public function remove(string|int $user_id, string|int $permission_id): void
    {
        $cols = $this->columns->userDenies;

        $this->model->where([
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
        ])->delete();
    }

    public function isDenied(string|int $user_id, string|int $permission_id): bool
    {
        $cols = $this->columns->userDenies;

        $deny = $this->model->where([
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
        ])->first();

        if (!$deny) {
            return false;
        }

        if ($deny[$cols->expiresAt] && strtotime($deny[$cols->expiresAt]) < time()) {
            return false;
        }

        return true;
    }

    public function getDeniedPermissions(string|int $user_id): array
    {
        $cols = $this->columns->userDenies;
        $permCols = $this->columns->permissions;
        $tables = \config('Vima')->tables;

        $results = $this->model->db->table($tables->userDenies)
            ->select($tables->userDenies . '.*')
            ->where($tables->userDenies . '.' . $cols->userId, $user_id)
            ->get()
            ->getResult();

        $denies = [];
        foreach ($results as $row) {
            $denies[] = new UserDeny(
                id: (int) $row->id,
                userId: $row->{$cols->userId},
                permissionId: $row->{$cols->permissionId},
                reason: $row->{$cols->reason} ?? null,
                expiresAt: $row->{$cols->expiresAt} ?? null,
                createdAt: $row->{$cols->createdAt} ?? null
            );
        }

        return $denies;
    }
}
