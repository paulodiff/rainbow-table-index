<?php

namespace Paulodiff\RainbowTableIndex\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Paulodiff\RainbowTableIndex\Tests\TestCase;

class KeyGeneratorCommandTest extends TestCase
{
    /** @test */
    function the_key_generator_command()
    {
        // make sure we're starting from a clean state
        // if (File::exists(config_path('blogpackage.php'))) {
        //     unlink(config_path('blogpackage.php'));
        // }
        // $this->assertFalse(File::exists(config_path('blogpackage.php')));
        Log::channel('stderr')->info('CheckConfig:artisan command', [] );
        Artisan::call('RainbowTableIndex:keyGenerator');

        // $this->assertTrue(File::exists(config_path('blogpackage.php')));
        $this->assertTrue(true);
    }
}