<?php

namespace Vima\CodeIgniter\Database\Migrations;

use CodeIgniter\Database\Migration;
use Vima\Core\Support\Framework\FrameworkIntegration;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        $schema = FrameworkIntegration::getSchema();
        $tables = FrameworkIntegration::requiredTables();

        $table = $schema->getTable($tables->auditLogs);
        if ($table && !$this->db->tableExists($tables->auditLogs)) {
            $forgeFields = [];
            foreach ($table->fields as $field) {
                $ciField = [
                    'type' => $this->getCI4Type($field->type),
                ];
                if ($field->length) $ciField['constraint'] = $field->length;
                if ($field->type === 'integer' && !isset($field->length)) $ciField['constraint'] = 11;
                if ($field->nullable) $ciField['null'] = true;
                if ($field->unsigned) $ciField['unsigned'] = true;
                if ($field->autoIncrement) $ciField['auto_increment'] = true;
                
                $forgeFields[$field->name] = $ciField;
            }

            $this->forge->addField($forgeFields);
            foreach ($table->primaryKeys as $pk) $this->forge->addKey($pk, true);
            foreach ($table->uniqueKeys as $uk) $this->forge->addUniqueKey($uk);
            foreach ($table->foreignKeys as $fk) {
                $this->forge->addForeignKey($fk->column, $fk->onTable, $fk->onColumn, $fk->onUpdate, $fk->onDelete);
            }
            $this->forge->createTable($tables->auditLogs, true);
        }
    }

    private function getCI4Type(string $type): string
    {
        return match ($type) {
            'integer' => 'INT',
            'string' => 'VARCHAR',
            'text', 'json' => 'TEXT',
            'datetime' => 'DATETIME',
            default => 'VARCHAR',
        };
    }

    public function down()
    {
        $tables = FrameworkIntegration::requiredTables();
        $this->forge->dropTable($tables->auditLogs, true);
    }
}
