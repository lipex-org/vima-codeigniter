<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;

class VimaAITest extends VimaTestCase
{
    protected function tearDown(): void
    {
        // Clean up generated files
        $cursorFile = ROOTPATH . '.cursor/rules/vima.md';
        if (file_exists($cursorFile)) {
            unlink($cursorFile);
            @rmdir(dirname($cursorFile));
            @rmdir(dirname(dirname($cursorFile)));
        }

        $skillFile = ROOTPATH . '.agent/skills/vima/SKILL.md';
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
    }

    public function testAIPublishCursor()
    {
        command('vima:ai publish --ide cursor --overwrite');

        $this->assertFileExists(ROOTPATH . '.cursor/rules/vima.md');
    }

    public function testAIPublishAntigravity()
    {
        command('vima:ai publish --ide antigravity --overwrite');

        $this->assertFileExists(ROOTPATH . '.agent/skills/vima/SKILL.md');
        $this->assertFileExists(ROOTPATH . '.agent/skills/vima/workflows/vima_feature_implementation.md');
    }
}
