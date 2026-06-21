<?php

namespace Vima\CodeIgniter\Repositories;

use Vima\Core\Audit\Contracts\AuditRepositoryInterface;
use Vima\Core\Audit\Entities\BareAuditLog;
use CodeIgniter\Database\BaseConnection;

class AuditRepository implements AuditRepositoryInterface
{
    protected BaseConnection $db;
    protected string $table;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->table = service('vima_config')->tables->auditLogs;
    }

    public function log(BareAuditLog|array $data): void
    {
        if ($data instanceof BareAuditLog) {
            $cols = service('vima_config')->columns->auditLogs;
            $data = [
                $cols->userId => $data->userId,
                $cols->permission => $data->permission,
                $cols->namespace => $data->namespace,
                $cols->result => $data->result,
                $cols->reason => $data->reason,
                $cols->arguments => $data->arguments,
                $cols->createdAt => $data->created_at ?? date('Y-m-d H:i:s'),
            ];
        }
        $this->db->table($this->table)->insert($data);
    }
}
