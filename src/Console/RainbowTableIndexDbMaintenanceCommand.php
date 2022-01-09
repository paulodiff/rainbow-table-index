<?php
namespace Paulodiff\RainbowTableIndex\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

use Paulodiff\RainbowTableIndex\RainbowTableIndexEncrypter;
use Paulodiff\RainbowTableIndex\RainbowTableIndexService;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

use Paulodiff\RainbowTableIndex\Tests\Models\Author;
use Paulodiff\RainbowTableIndex\Tests\Models\Post;

class RainbowTableIndexDbMaintenanceCommand extends Command
{
    use RainbowTableIndexTrait;
    protected $signature = 'RainbowTableIndex:dbMaintenance {id}';

    protected $description = 'dbMaintenance for RainbowTableIndex';

    public function handle()
    {
        $this->info('RainbowTableIndex dbMaintenance - search/update testE ');

        $id = $this->argument('id');

        $a = Author::where('id', $id)->first();
        Log::channel('stderr')->info('Maintenance:dbMaintenance', [$a::$rainbowTableIndexConfig]);

        $r1 = $a->rebuildRainbowIndex();
        

        
        Log::channel('stderr')->info('dbMaintenance finished!:', []);
        
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig()
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "JohnDoe\BlogPackage\BlogPackageServiceProvider",
            '--tag' => "config"
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

       $this->call('vendor:publish', $params);
    }
}