<?php
namespace Paulodiff\RainbowTableIndex\Tests;

use Paulodiff\RainbowTableIndex\RainbowTableIndexServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    // additional setup
  }

  protected function getPackageProviders($app)
  {
    return [
      RainbowTableIndexServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
    $app['config']->set('rainbowtableindex.key','DjDLn1H7V1zWDQA7oJ+LMqJ+LQguZgGMO8v/wNei5zs=');
    $app['config']->set('rainbowtableindex.nonce','cxM2LpMbuIRwn4pP8IQbym7pyM25lQSw');
    $app['config']->set('rainbowtableindex.encrypt',true);
    $app['config']->set('database-encryption.enabled', true);
    $app['config']->set('LOG_LEVEL', 'debug');
  }
}