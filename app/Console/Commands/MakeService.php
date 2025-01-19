<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

     // php artisan make:service AuthUser --folder=Services/Auth 

    protected $signature = 'make:service {name} {--folder=Services : The folder where the service should be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

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
            $this->error("The service {$name} already exists in the {$folder} folder.");
            return Command::FAILURE;
        }

        // Generate the service file
        $namespace = "App\\" . str_replace('/', '\\', $folder);
        $content = <<<EOT
        <?php

        namespace {$namespace};

        class {$name}
        {
            //
        }
        EOT;

        File::put($filePath, $content);

        $this->info("Service {$name} has been created successfully in the {$folder} folder.");
        return Command::SUCCESS;
    }
}
