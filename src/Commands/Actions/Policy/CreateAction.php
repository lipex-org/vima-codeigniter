<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Policy;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\BaseAction;
use Vima\CodeIgniter\Commands\Traits\ModuleGeneratorTrait;

class CreateAction extends BaseAction
{
    use ModuleGeneratorTrait;

    public function execute(array $params): void
    {
        CLI::write('Executing Policy CreateAction...', 'cyan');
        $resource = $this->getOption('resource', $params) ?? 'App\Entities\Resource';
        $force = $this->getOption('force', $params) !== null;

        $name = $params[0] ?? null;
        if ($name === null) {
            $name = CLI::prompt('Policy name');
        }

        [$namespace, $className] = $this->parseClassName($name, 'App\Policies');

        CLI::write("Creating policy: {$className} in namespace: {$namespace} for resource: {$resource}", 'cyan');

        // Ensure suffix
        if (!str_ends_with($className, 'Policy')) {
            $className .= 'Policy';
        }

        $destDir = $this->resolveNamespacePath($namespace);
        if (!$destDir) {
            CLI::error("Could not resolve path for namespace: {$namespace}");
            return;
        }

        $dest = $destDir . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($dest) && !$force) {
            CLI::error("File '{$className}.php' already exists at {$destDir}");
            return;
        }

        $templatePath = realpath(__DIR__ . '/../../Generators/Views/policy.tpl.php');

        if (!file_exists($templatePath)) {
            CLI::error("Template file not found at: {$templatePath}");
            return;
        }

        $template = file_get_contents($templatePath);

        // Sanitize backslashes in resource class
        $resource = str_replace('\\\\', '\\', $resource);

        // Extract class name from full namespace
        $parts = explode('\\', $resource);
        $resourceClass = end($parts);
        $resourceVar = lcfirst($resourceClass);

        $replacements = [
            '<@php' => '<?php',
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{resourceFullClass}' => $resource,
            '{resourceClass}' => $resourceClass,
            '{resourceVar}' => $resourceVar,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $template);

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (file_put_contents($dest, $content)) {
            CLI::write("Created policy: " . CLI::color($dest, 'green'));
        } else {
            CLI::error("Failed to create policy file.");
        }
    }

    public function getDescription(): string
    {
        return 'Generates a new Vima Policy file.';
    }

    public function getUsage(): string
    {
        return 'vima:policy create [name] [options]';
    }
}
