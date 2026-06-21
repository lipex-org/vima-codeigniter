<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions\Publish;

use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Commands\Actions\VimaActionInterface;
use Vima\CodeIgniter\Commands\Actions\BaseAction;

class AIAction extends BaseAction
{
    protected array $locations = [
        'cursor' => '.cursor/rules/vima.md',
        'windsurf' => '.windsurf/rules/vima.md',
        'vscode' => '.github/rules/vima.md',
        'cline' => '.cline/rules/vima.md',
        'roocode' => '.roo/rules/vima.md',
        'antigravity' => '.agent/skills/vima/SKILL.md',
        'jetbrains' => '.jetbrains/rules/vima.md',
    ];

    public function execute(array $params): void
    {
        $ide = CLI::getOption('ide');
        $overwrite = !!CLI::getOption('overwrite');

        if (empty($ide)) {
            $options = array_keys($this->locations);
            sort($options);
            $options[] = 'all';

            $ide = CLI::prompt('Choose target IDE/Agent to publish guidance for', $options);
        }

        if ($ide === 'all') {
            foreach ($this->locations as $key => $path) {
                $this->publish($key, $path, $overwrite);
            }
        } elseif (isset($this->locations[$ide])) {
            $this->publish($ide, $this->locations[$ide], $overwrite);
        } else {
            CLI::error("Unknown IDE/Agent: {$ide}. Supported: " . implode(', ', array_keys($this->locations)) . ', all');
        }

        CLI::write('AI Guidance publishing completed!', 'green');
    }

    protected function publish(string $id, string $filename, bool $overwrite)
    {
        $source = __DIR__ . '/../../../AI/SKILL.md';

        if (!file_exists($source)) {
            CLI::error("Source guidance file not found at: {$source}");
            return;
        }

        $dest = ROOTPATH . $filename;

        $dir = dirname($dest);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                CLI::error("Failed to create directory: {$dir}");
                return;
            }
        }

        if (file_exists($dest)) {
            if (!$overwrite) {
                if (CLI::prompt("File '{$filename}' already exists for {$id}. Overwrite?", ['n', 'y']) !== 'y') {
                    CLI::write("Skipping {$id}...", 'yellow');
                    return;
                }
            }
        }

        if (copy($source, $dest)) {
            CLI::write("Published AI guidance for {$id} to {$filename}", 'green');

            if ($id === 'vima') {
                $this->publishWorkflows();
            }
        } else {
            CLI::error("Failed to publish guidance for {$id} to {$filename}");
        }
    }

    protected function publishWorkflows()
    {
        $sourceDir = __DIR__ . '/../../../AI/workflows';
        $destDir = ROOTPATH . '.agent/skills/vima/workflows';

        if (!is_dir($sourceDir)) {
            return;
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $files = scandir($sourceDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (copy($sourceDir . DIRECTORY_SEPARATOR . $file, $destDir . DIRECTORY_SEPARATOR . $file)) {
                CLI::write("Published workflow: {$file}", 'green');
            }
        }
    }

    public function getDescription(): string
    {
        return 'Publishes AI agent guidance to the project root for various IDEs and agents.';
    }

    public function getUsage(): string
    {
        return 'vima publish ai {options}';
    }
}
