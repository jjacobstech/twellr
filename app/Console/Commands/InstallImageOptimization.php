<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallImageOptimization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install-image-optimization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This installs all the necessary packages for image optimization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔧 Installing image optimization tools. . .");

        $shellScript = <<<BASH
#!/bin/bash
sudo apt-get update &&
sudo apt-get install -y   jpegoptim optipng pngquant gifsicle webp libavif-bin &&
sudo apt-get autoremove
BASH;
        $output = shell_exec($shellScript);
        $this->line($output);
        $this->info("✅ Image optimization tools installed successfully.");

        $this->info("🔧 Installing npm svg optimization tool. . .");
        $npmScript = <<<BASH
npm install -g svgo
BASH;
        $output = shell_exec($npmScript);
        $this->line($output);
        $this->info("✅ All image optimization tools installed successfully.");
    }
}
