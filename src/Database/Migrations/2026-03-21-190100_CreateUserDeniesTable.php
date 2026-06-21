<?php

namespace Vima\CodeIgniter\Database\Migrations;

use CodeIgniter\Database\Migration;
use Vima\Core\Support\Framework\FrameworkIntegration;

class CreateUserDeniesTable extends Migration
{
    public function up()
    {
        $schema = FrameworkIntegration::getSchema();
        $tables = FrameworkIntegration::requiredTables();
        $table  = $schema->getTable($tables->userDenies);

        if (!$table) {
            return;
        }

        $forgeFields = [];

        foreach ($table->fields as $field) {
            $ciField = [];
            $ciType = 'VARCHAR';

            switch ($field->type) {
                case 'integer':
                    $ciType = 'INT';
                    if (!isset($field->length)) {
                        $ciField['constraint'] = 11;
                    }
                    break;
                case 'string':
                    $ciType = 'VARCHAR';
                    $ciField['constraint'] = $field->length ?? 100;
                    break;
                case 'text':
                case 'json':
                    $ciType = 'TEXT';
                    break;
                case 'datetime':
                    $ciType = 'DATETIME';
                    break;
            }

            $ciField['type'] = $ciType;

            if ($field->nullable) {
                $ciField['null'] = true;
            }

            if ($field->unsigned) {
                $ciField['unsigned'] = true;
            }

            if ($field->autoIncrement) {
                $ciField['auto_increment'] = true;
            }

            $forgeFields[$field->name] = $ciField;
        }

        $this->forge->addField($forgeFields);

        foreach ($table->primaryKeys as $pk) {
            $this->forge->addKey($pk, true);
        }

        foreach ($table->uniqueKeys as $uk) {
            $this->forge->addUniqueKey($uk);
        }

        foreach ($table->foreignKeys as $fk) {
            $this->forge->addForeignKey($fk->column, $fk->onTable, $fk->onColumn, $fk->onUpdate, $fk->onDelete);
        }

        $this->forge->createTable($tables->userDenies, true);
    }

    public function down()
    {
        $this->forge->dropTable(FrameworkIntegration::requiredTables()->userDenies, true);
    }
}
