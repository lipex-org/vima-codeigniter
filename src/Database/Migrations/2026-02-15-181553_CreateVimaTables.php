<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Database\Migrations;

use CodeIgniter\Database\Migration;
use Vima\Core\Support\Framework\FrameworkIntegration;

class CreateVimaTables extends Migration
{
    public function up()
    {
        $schema = FrameworkIntegration::getSchema();

        foreach ($schema->getTables() as $tableName => $table) {
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

            $this->forge->createTable($tableName, true);
        }
    }

    public function down()
    {
        $schema = FrameworkIntegration::getSchema();
        $tables = array_keys($schema->getTables());

        foreach (array_reverse($tables) as $tableName) {
            $this->forge->dropTable($tableName, true);
        }
    }
}
