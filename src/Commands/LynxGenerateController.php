<?php

namespace Lynx\Commands;

use Illuminate\Console\Command;

class LynxGenerateController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lynx:api
    {name : The name of the Api controller}
    {--policy= : Generate a policy class}
    {--resource= : Generate a resource class}
    {--model= : selected model}
    {--module= : Module Selected }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API scaffolding including controllers, policies, and resources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = $this->argument('name');
        $policy = $this->option('policy');
        $resource = $this->option('resource');
        $model = $this->option('model');
        $module = $this->option('module');

        $stubPath = base_path('app/Console/Commands/stubs');

        if ($controller) {
            $this->generateFile('api_controller', $controller, $module, $stubPath);
        }

        if ($policy) {
            $this->generateFile('policy', $controller, $module, $stubPath);
        }

        if ($resource) {
            $this->generateFile('resource', $controller, $module, $stubPath);
        }

        $this->info($controller . ' Lyux generated successfully.');
    }

    protected function generateFile($type, $name, $module, $stubPath)
    {
        $namespace = $module ? 'Modules\\' . $module . '\App\Http\Controllers\Api' : 'App\Http\Controllers\Api';
        $stub = file_get_contents(str_replace('\\', '/', $stubPath) . '/' . $type . '.stub');
        $stub = str_replace('{{namespace}}', $namespace, $stub);
        $stub = str_replace('{{class}}', $name, $stub);

        // Add Model Namespace
        if ($this->option('module')) {
            $entity = '\\Modules\\' . $this->option('module') . '\\App\\Models\\' . ($this->option('model')??$name);
        } else {
            $entity = 'App\\Models\\' . ($this->option('model')??$name);
        }
        $stub = str_replace('{{entity}}', $entity, $stub);

        // Add Policy Namespace
        if ($this->option('module')) {
            $policy = '\\Modules\\' . $this->option('module') . '\\App\\Policies\\' . ($this->option('policy')??$name);
        } else {
            $policy = 'App\\Policies\\' . ($this->option('policy')??$name);
        }

        $stub = str_replace('{{policy}}', $policy, $stub);

        // resource
        if ($this->option('module')) {
            $resource = '\\Modules\\' . $this->option('module') . "\App\\resources\\" .  ($this->option('resource')??$name);
        } else {
            $resource = 'App\\resources\\' . ($this->option('resource')??$name);
        }
        $stub = str_replace('{{resourcesJson}}', $resource . 'Resource', $stub);

        $path = $namespace . '/' . $name . ($type === 'api_controller' ? 'Controller.php' : ($type === 'policy' ? 'Policy.php' : 'Resource.php'));

        if ($type == 'api_controller') {
            $this->directory($namespace);
            file_put_contents(str_replace('\\', '/', $path), $stub);
        }
    }

    public function directory($path)
    {
        if (!is_dir(str_replace('\\', '/', $path))) {
            mkdir(str_replace('\\', '/', $path), 0777, true);
        }
    }
}