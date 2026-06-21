<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Models\UserDenyModel;
use Vima\CodeIgniter\Models\UserRoleDenyModel;

class VimaCleanup extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Vima';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'vima:cleanup';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Purges expired denials and expired direct permissions from the database.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:cleanup';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $now = date('Y-m-d H:i:s');
        $config = config('Vima');

        CLI::write('Cleaning up expired Vima records...', 'yellow');

        // 1. User Denies
        $userDenyModel = new UserDenyModel();
        $userDenyModel->setTable($config->tables->userDenies);
        $cols = $config->columns->userDenies;

        $userDenyModel->where($cols->expiresAt . ' <', $now)
            ->where($cols->expiresAt . ' IS NOT NULL', null, false)
            ->delete();
        $countUserDenies = $userDenyModel->db->affectedRows();

        // 2. User Role Denies
        $userRoleDenyModel = new UserRoleDenyModel();
        $userRoleDenyModel->setTable($config->tables->userRoleDenies);
        $colsRole = $config->columns->userRoleDenies;

        $userRoleDenyModel->where($colsRole->expiresAt . ' <', $now)
            ->where($colsRole->expiresAt . ' IS NOT NULL', null, false)
            ->delete();
        $countRoleDenies = $userRoleDenyModel->db->affectedRows();

        CLI::write("Removed " . $countUserDenies . " expired user denials.", 'green');
        CLI::write("Removed " . $countRoleDenies . " expired user role denials.", 'green');

        CLI::write('Vima cleanup completed!', 'green');
    }
}
