<?php

namespace Paulodiff\RainbowTableIndex\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Paulodiff\RainbowTableIndex\Tests\TestCase;

class DbMaintenanceCommandTest extends TestCase
{
    /** @test */
    function the_db_maintenance_command()
    {
        // make sure we're starting from a clean state
        // if (File::exists(config_path('blogpackage.php'))) {
        //     unlink(config_path('blogpackage.php'));
        // }
        // $this->assertFalse(File::exists(config_path('blogpackage.php')));
        Log::channel('stderr')->info('dbMaintenance Author:artisan command', [] );
        Artisan::call('RainbowTableIndex:dbMaintenance 1');

        // $this->assertTrue(File::exists(config_path('blogpackage.php')));
        $this->assertTrue(true);
    }
}