<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\EnsuresInstallationState;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use EnsuresInstallationState;

    protected function setUp(): void
    {
        $this->prepareInstallationArtifacts();
        parent::setUp();

        $this->ensureInstallationState();
    }
}
