<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if (! $app->environment('testing')
            || $app['config']->get('database.default') !== 'mysql'
            || $app['config']->get('database.connections.mysql.database') !== 'evaluacion_fullstack_testing') {
            throw new \RuntimeException(
                'Pruebas detenidas: APP_ENV debe ser testing y la base debe ser evaluacion_fullstack_testing.'
            );
        }

        if ($app['db']->connection()->selectOne('SELECT DATABASE() AS nombre')->nombre !== 'evaluacion_fullstack_testing') {
            throw new \RuntimeException('Pruebas detenidas: la base efectiva no es evaluacion_fullstack_testing.');
        }

        return $app;
    }
}
