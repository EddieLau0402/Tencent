<?php
namespace Eddie\TencentIm\Tests;

use Eddie\TencentIm\Im;

class TecentImSignatureTest extends TestCase
{

    protected $signature;


    public function setUp()
    {
        parent::setUp();

        /*
         * IM_SDK_APPID=1400131907
         * IM_SDK_ACOUNT=admin
         * IM_SDK_ACOUNTTYPE=36362
         */

        $this->app['config']->set('im.appid', '1400131907');
        $this->app['config']->set('im.private_key', __DIR__ . '/test_private_key');
        $this->app['config']->set('im.public_key', __DIR__ . '/test_public_key');

        $this->signature = (new Im())->signature();
    }

    public function testSignatureGenerate()
    {
        $identifier = 'admin';

        $sig = $this->signature->generate($identifier);

        $verify = $this->signature->verify($sig, $identifier);

        echo "\nsign: {$sig}\n";

        $this->assertTrue($verify);
    }
}