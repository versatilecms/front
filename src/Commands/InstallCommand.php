<?php

namespace Versatile\Front\Commands;

use Versatile\Front\Providers\VersatileFrontendServiceProvider;
use Versatile\Core\Traits\Seedable;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    use Seedable;

    protected $seedersPath = __DIR__ . '/../../database/seeds/';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'versatile-frontend:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Versatile Frontend package';

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd() . '/composer.phar')) {
            return '"' . PHP_BINARY . '" ' . getcwd() . '/composer.phar';
        }

        return 'composer';
    }

    public function fire(Filesystem $filesystem)
    {
        return $this->handle($filesystem);
    }


    /**
     * Execute the console command
     *
     * @param Filesystem $filesystem
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(Filesystem $filesystem)
    {
        // Clean Up
        $this->info('Deleting Laravel\'s default assets, to make way for ours');
        (new Filesystem)->deleteDirectory(resource_path('assets', true));

        $this->info('Remove default welcome page');
        (new Filesystem)->delete(resource_path('views/welcome.blade.php'));

        $this->info('Remove default web route');
        $routes_contents = (new Filesystem)->get(base_path('routes/web.php'));
        if (false !== strpos($routes_contents, "return view('welcome')")) {
            $routes_contents = str_replace("\n\nRoute::get('/', function () {\n    return view('welcome');\n});", '',
                $routes_contents);
            (new Filesystem)->put(base_path('routes/web.php'), $routes_contents);
        }

        // Use our files
        $this->info('Copying authentication views to main project');
        (new Filesystem)->copyDirectory(
            __DIR__ . '/../../stubs/views', resource_path('views')
        );

        $this->info('Publishing the Versatile assets, database, and config files');
        $this->call('vendor:publish', ['--provider' => VersatileFrontendServiceProvider::class]);

        $this->info('Successfully installed Versatile Frontend! Enjoy');
    }
}
