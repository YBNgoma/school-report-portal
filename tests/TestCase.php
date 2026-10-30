<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Seed roles, permissions and demo users whenever the test database
    // is (re)migrated, since RefreshDatabase only runs `migrate:fresh`
    // once per process for the first test that triggers it.
    protected bool $seed = true;
}
