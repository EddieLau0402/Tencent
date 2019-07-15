<?php

namespace Eddie\TencentIm\Tests;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    protected $config;

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
//        $app['config']->set('database.default', 'sqlite');
//        $app['config']->set('database.connections.sqlite.database', ':memory:');


        return $app;
    }

    /**
     * Setup DB before each test.
     */
    public function setUp()
    {
        parent::setUp();

        if ( empty($this->config))  {
            $this->config = require __DIR__.'/../config/im.php';
        }

        $this->app['config']->set('im', $this->config);
    }
}