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

use Vima\CodeIgniter\Models\RoleModel;
use Vima\Core\Role\Contracts\RoleRepositoryInterface;
use Vima\Core\Role\Entities\Role;

class RoleRepository implements RoleRepositoryInterface
{
    protected RoleModel $model;

    public function __construct()
    {
        $this->model = new RoleModel();
    }

    public function findById(int|string $id): ?Role
    {
        $cols = service('vima_config')->columns->roles;
        $data = $this->model->asArray()->find($id);
        if (!$data) {
            return null;
        }

        return new Role(
            id: $data[$cols->id],
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            description: $data[$cols->description] ?? null,
            context: isset($data[$cols->context]) ? json_decode($data[$cols->context], true) : [],
        );
    }

    public function findByName(string $name): ?Role
    {
        $cols = service('vima_config')->columns->roles;
        $namespace = null;
        if (str_contains($name, ':')) {
            [$namespace, $name] = explode(':', $name, 2);
        }

        $query = $this->model->asArray()->where($cols->name, $name);

        if ($namespace) {
            $query->where($cols->namespace, $namespace);
        } else {
            $query->groupStart()
                ->where($cols->namespace, null)
                ->orWhere($cols->namespace, '')
                ->groupEnd();
        }

        $data = $query->first();
        if (!$data) {
            return null;
        }

        return new Role(
            id: $data[$cols->id],
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            description: $data[$cols->description] ?? null,
            context: isset($data[$cols->context]) ? json_decode($data[$cols->context], true) : [],
        );
    }

    public function all(?string $namespace = null): array
    {
        $cols = service('vima_config')->columns->roles;
        $query = $this->model->asArray();

        if ($namespace !== null) {
            $query->where($cols->namespace, $namespace);
        }

        $all = $query->findAll();

        return array_map(fn($data) => new Role(
            id: $data[$cols->id],
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            description: $data[$cols->description] ?? null,
            context: isset($data[$cols->context]) ? json_decode($data[$cols->context], true) : [],
        ), $all);
    }

    public function save(Role $role): Role
    {
        $cols = service('vima_config')->columns->roles;
        $data = [
            $cols->name => $role->name,
            $cols->namespace => $role->namespace,
            $cols->description => $role->description,
            $cols->context => empty($role->context) ? null : json_encode($role->context),
        ];

        if ($role->id && $this->findById($role->id)) {
            $this->model->update($role->id, $data);
        } else {
            // Check for existing by name/namespace if no ID
            $fullName = $role->namespace ? "{$role->namespace}:{$role->name}" : $role->name;
            $existing = $this->findByName($fullName);
            if ($existing) {
                $role->id = $existing->id;
                $this->model->update($role->id, $data);
            } else {
                $role->id = null; // Clear ID to ensure it is inserted as a new row
                $id = $this->model->insert($data);
                $role->id = $id;
            }
        }

        return $role;
    }

    public function delete(Role $role): void
    {
        $cols = service('vima_config')->columns->roles;
        if ($role->id) {
            $this->model->delete($role->id);
        } else {
            $query = $this->model->where($cols->name, $role->name);
            if ($role->namespace) {
                $query->where($cols->namespace, $role->namespace);
            } else {
                $query->groupStart()
                    ->where($cols->namespace, null)
                    ->orWhere($cols->namespace, '')
                    ->groupEnd();
            }
            $query->delete();
        }
    }

    public function deleteAll(): void
    {
        $this->model->where('1=1')->delete();
    }
}
