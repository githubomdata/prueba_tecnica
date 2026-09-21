<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! app()->environment('testing') || config('database.connections.mysql.database') !== 'evaluacion_fullstack_testing') {
            throw new \RuntimeException(
                'Pruebas detenidas: APP_ENV debe ser testing y la base debe ser evaluacion_fullstack_testing.'
            );
        }
    }
}
