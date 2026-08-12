<?php

namespace Vima\CodeIgniter\Tests\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use Vima\CodeIgniter\Tests\VimaTestCase;

class VimaAITest extends VimaTestCase
{
    use StreamFilterTrait;

    protected MockInputOutput $io;

    protected function setUp(): void
    {
        parent::setUp();

        $this->io = new MockInputOutput();
        CLI::setInputOutput($this->io);
    }

    protected function tearDown(): void
    {
        // Clean up generated files
        $cursorFile = ROOTPATH . '.cursor/rules/vima.md';
        if (file_exists($cursorFile)) {
            unlink($cursorFile);
            @rmdir(dirname($cursorFile));
            @rmdir(dirname(dirname($cursorFile)));
        }

        $skillFile = ROOTPATH . '.agents/skills/vima/SKILL.md';
        if (file_exists($skillFile)) {
            unlink($skillFile);
        }

        $workflowFile = ROOTPATH . '.agent/skills/vima/workflows/vima_feature_implementation.md';
        if (file_exists($workflowFile)) {
            unlink($workflowFile);
            @rmdir(dirname($workflowFile));
            @rmdir(dirname(dirname($workflowFile)));
            @rmdir(dirname(dirname(dirname($workflowFile))));
        }

        parent::tearDown();

        CLI::resetInputOutput();
    }

    public function testAIPublishCursor()
    {
        $this->io->setInputs([
            'cursor'
        ]);

        command('vima:ai publish --overwrite');

        $this->assertFileExists(ROOTPATH . '.cursor/rules/vima.md');
    }

    public function testAIPublishAntigravity()
    {
        $this->io->setInputs([
            'antigravity'
        ]);

        command('vima:ai publish --overwrite');

        $this->assertFileExists(ROOTPATH . '.agents/skills/vima/SKILL.md');
        $this->assertFileExists(ROOTPATH . '.agent/skills/vima/workflows/vima_feature_implementation.md');
    }
}
