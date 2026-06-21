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

use Vima\Core\Config\VimaConfig;
use Vima\CodeIgniter\Models\PermissionModel;
use Vima\Core\Permission\Contracts\PermissionRepositoryInterface;
use Vima\Core\Permission\Entities\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    protected PermissionModel $model;
    protected VimaConfig $config;

    public function __construct()
    {
        $this->model = new PermissionModel();
        $this->config = service('vima_config');
    }

    public function findById(int|string $id): ?Permission
    {
        $cols = $this->config->columns->permissions;
        $data = $this->model->asArray()->find($id);
        if (!$data) {
            return null;
        }

        return new Permission(
            id: $data[$cols->id],
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            description: $data[$cols->description] ?? null
        );
    }

    public function findByName(string $name): ?Permission
    {
        $cols = $this->config->columns->permissions;
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

        return new Permission(
            id: $data[$cols->id],
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            description: $data[$cols->description] ?? null
        );
    }

    public function all(?string $namespace = null, bool $onlyGlobal = false): array
    {
        $cols = $this->config->columns->permissions;
        $query = $this->model->asArray();

        if ($namespace !== null) {
            $query->where($cols->namespace, $namespace);
        } else {
            if ($onlyGlobal) {
                $query->where($cols->namespace, null);
            }
        }

        $all = $query->findAll();
        return array_map(fn($data) => new Permission(
            id: $data[$cols->id],
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            description: $data[$cols->description] ?? null
        ), $all);
    }

    public function save(Permission $permission): Permission
    {
        $cols = $this->config->columns->permissions;
        $data = [
            $cols->name => $permission->name,
            $cols->namespace => $permission->namespace,
            $cols->description => $permission->description,
        ];

        if ($permission->id && $this->findById($permission->id)) {
            $this->model->update($permission->id, $data);
        } else {
            // Check for existing by name/namespace if no ID
            $fullName = $permission->namespace ? "{$permission->namespace}:{$permission->name}" : $permission->name;
            $existing = $this->findByName($fullName);
            if ($existing) {
                $permission->id = $existing->id;
                $this->model->update($permission->id, $data);
            } else {
                $permission->id = null; // Clear ID to ensure it is inserted as a new row
                $id = $this->model->insert($data);
                if ($id === false) {
                    throw new \RuntimeException("Failed to insert permission: " . json_encode($this->model->errors()) . " DB error: " . json_encode($this->model->db->error()));
                }
                $permission->id = $id;
            }
        }

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $cols = $this->config->columns->permissions;
        if ($permission->id) {
            $this->model->delete($permission->id);
        } else {
            $query = $this->model->where($cols->name, $permission->name);
            if ($permission->namespace) {
                $query->where($cols->namespace, $permission->namespace);
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
