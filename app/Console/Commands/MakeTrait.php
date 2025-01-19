<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeTrait extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

     // php artisan make:trait AuthUser --folder=Traits/Auth 

     
    protected $signature = 'make:trait {name} {--folder=Traits : The folder where the trait should be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $folder = $this->option('folder');
        $basePath = app_path($folder);
        $filePath = "{$basePath}/{$name}.php";

        // Ensure the folder exists
        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        // Check if the file already exists
        if (File::exists($filePath)) {
            $this->error("The trait {$name} already exists in the {$folder} folder.");
            return Command::FAILURE;
        }

        // Generate the trait file
        $namespace = "App\\" . str_replace('/', '\\', $folder);
        $content = <<<EOT
        <?php

        namespace {$namespace};

        trait {$name}
        {
            //
        }
        EOT;

        File::put($filePath, $content);

        $this->info("Trait {$name} has been created successfully in the {$folder} folder.");
        return Command::SUCCESS;
    }
}
