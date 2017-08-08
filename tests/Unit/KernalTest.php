<?php

namespace Rareloop\Lumberjack\Test;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\LoadConfiguration;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Http\Kernal;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

class KernalTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function creating_kernal_should_bind_action_to_after_theme_setup_action()
    {
        $app = new Application();

        $kernal = new Kernal($app);

        $this->assertTrue(has_action('after_theme_setup', [$kernal, 'bootstrap']));
    }

    /** @test */
    public function bootstrap_should_pass_bootstrappers_to_app()
    {
        $app = Mockery::mock(Application::class.'[bootstrapWith]');
        $app->shouldReceive('bootstrapWith')->with([
            LoadConfiguration::class,
            RegisterFacades::class,
            RegisterProviders::class,
            BootProviders::class,
        ])->once();

        $kernal = new Kernal($app);
        $kernal->bootstrap();
    }
}