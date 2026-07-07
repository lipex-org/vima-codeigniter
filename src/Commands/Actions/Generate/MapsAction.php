<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Generate;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\VimaActionInterface;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\Core\Support\Mapping\MappingService;
use Vima\Core\Support\Mapping\MapGenerator;

class MapsAction extends BaseAction
{
    public function execute(array $params): void
    {
        $config = service('vima_config');
        $rootPath = defined('ROOTPATH') ? ROOTPATH : getcwd() . DIRECTORY_SEPARATOR;

        $vimaDir = $rootPath . '.vima' . DIRECTORY_SEPARATOR;
        $mappingFile = $vimaDir . 'mapping.json';

        $outputDir = APPPATH . 'Mappers' . DIRECTORY_SEPARATOR . 'Vima' . DIRECTORY_SEPARATOR;
        $namespace = 'App\Mappers\Vima';

        $tsDir = CLI::getOption('ts-dir') ?? $rootPath . 'resources' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'vima';
        $generateTs = !!CLI::getOption('ts');

        CLI::write('Initializing Vima Mapper...', 'yellow');

        try {
            $mappingService = new MappingService($mappingFile);
            $generator = new MapGenerator($mappingService);

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Generate Roles
            CLI::write('Generating Roles mapper...', 'yellow');
            $rolesClass = $generator->generateRoles($config->setup, $namespace);
            file_put_contents($outputDir . 'Roles.php', $rolesClass);

            // Generate Permissions
            CLI::write('Generating Permissions mapper...', 'yellow');
            $permsClass = $generator->generatePermissions($config->setup, $namespace);
            file_put_contents($outputDir . 'Permissions.php', $permsClass);

            // Generate Namespaces
            CLI::write('Generating Namespaces mapper...', 'yellow');
            $namespacesClass = $generator->generateNamespaces($namespace);
            file_put_contents($outputDir . 'Namespaces.php', $namespacesClass);

            if ($generateTs) {
                CLI::write('Generating TypeScript maps...', 'yellow');
                $mappingService->generateTypeScriptFiles($tsDir);
                CLI::write("TypeScript maps generated in: {$tsDir}", 'green');
            }

            CLI::write('Mapper classes generated successfully in ' . $namespace, 'green');
            CLI::write('Mapping file updated: ' . $mappingFile, 'green');
        } catch (\Throwable $e) {
            CLI::error('Generation failed: ' . $e->getMessage());
        }
    }

    public function getDescription(): string
    {
        return 'Generate PHP mapper classes for roles and permissions';
    }

    public function getUsage(): string
    {
        return 'vima generate maps [options]';
    }

    public function getOptions(): array
    {
        return [
            '--ts'     => 'Generate TypeScript map files as well.',
            '--ts-dir' => 'Custom output directory for TypeScript files.',
        ];
    }
}
