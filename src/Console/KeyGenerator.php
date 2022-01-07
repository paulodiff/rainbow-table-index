<?php

namespace Paulodiff\RainbowTableIndex\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class KeyGenerator extends Command
{
    protected $signature = 'RainbowTableIndex:keyGenerator';

    protected $description = 'Key generator for RainbowTableIndex encryption';

    public function handle()
    {
        $this->info('Key generator ...');

        $this->info('Publishing configuration...');
        $this->info('RAINBOW_TABLE_INDEX_KEY=' . sodium_bin2base64( $key , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
        $this->info('RAINBOW_TABLE_INDEX_NONCE=' . sodium_bin2base64( $nonce , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
        $this->info('Copy and paste this values in .env file');    
            

        /*
        if (! $this->configExists('blogpackage.php')) {
            $this->publishConfiguration();
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration($force = true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }
        */

        // $this->info('Installed BlogPackage');
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