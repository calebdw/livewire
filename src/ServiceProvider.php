<?php

namespace Livewire;

use stdClass;

class AssociatedData {}

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Hey! Let me tell you a bit about how this codebase
     * works. Let's start with the high level philosophies,
     * then get sepecific.
     *
     * 1) The Single File Principle
     *
     *    When approaching a new feature, or maintaining an existing one,
     *    challenge yourself to contain it inside a single file rather
     *    than spreading its code all around the codebase.
     *
     *    This helps to colocate code concerned with the same job,
     *    which eases the maintenance burden & contains the messes.
     *
     * 2) Ease Of Deletion
     *
     *    Each feauture should ideally be "unpluggable". In other words,
     *    you should be able to remove an entire feauture by removing
     *    a single file or by commenting out a single line.
     *
     *    This makes maintenance and debugging easier by encouraging
     *    you to minimize concrete dependancies and instead rely
     *    on flexible abstractions.
     *
     * ## Specifics:
     *
     * 1) "Mechanisms" & "Features"
     *    Most new development will likely be contained in one of these
     *    two folders. "Mechanisms" are single files concerned with a
     *    single part of Livewire's core.
     *
     *    "Features" defer from "Mechanisms" in that they should be
     *    unpluggable. Meaning other parts of the system don't rely
     *    on them to function.
     *
     * 2) Hooks
     *    Within most Mechanisms or Features, you will find event listeners
     *    like: "->on('...', function(...) {". These are "hooks' and are
     *    crucial to successfully containing your code in single files.
     *
     * 3) Associated Component Data
     *    Sometimes "Mechanisms" or "Features" need to associate data with
     *    specific Livewire components. Rather than storing arbitrary
     *    data on the component objects themselves, you should
     *    instead use the "DataStore" class.
     *
     * Hoefully the above helped give you some context for the structure
     * of this codebase. We haven't even touched on the entire component
     * lifecycle and it's data structures. This topic is so complex
     * it deserves it's own codebase and has been isolated to "Synthetic".
     *
     * Thanks for reading,
     * - Caleb
     */

    public function register()
    {
        $this->registerLivewireSingleton();
        $this->registerConfig();
    }

    public function boot()
    {
        $this->bootConsoleCommands();
        $this->bootSynthesizers();
        $this->bootMechanisms();
        $this->bootFeatures();

        if (app()->environment('testing')) {
            \Tests\TestCase::onApplicationBoot();
        };
    }

    protected function registerLivewireSingleton()
    {
        $this->app->alias(Manager::class, 'livewire');
        $this->app->singleton(Manager::class);
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/livewire.php', 'livewire');
    }

    protected function bootConsoleCommands()
    {
        if (! $this->app->runningInConsole()) return;

        $this->commands([
            \Livewire\Commands\MakeLivewireCommand::class, // make:livewire
            \Livewire\Commands\MakeCommand::class,         // livewire:make
            \Livewire\Commands\TouchCommand::class,        // livewire:touch
            \Livewire\Commands\CopyCommand::class,         // livewire:copy
            \Livewire\Commands\CpCommand::class,           // livewire:cp
            \Livewire\Commands\DeleteCommand::class,       // livewire:delete
            \Livewire\Commands\RmCommand::class,           // livewire:rm
            \Livewire\Commands\MoveCommand::class,         // livewire:move
            \Livewire\Commands\MvCommand::class,           // livewire:mv
            \Livewire\Commands\StubsCommand::class,        // livewire:stubs
            \Livewire\Commands\DiscoverCommand::class,     // livewire:discover
            \Livewire\Commands\S3CleanupCommand::class,    // livewire:configure-s3-upload-cleanup
            \Livewire\Commands\PublishCommand::class,      // livewire:publish
        ]);
    }

    protected function bootSynthesizers()
    {
        app('synthetic')->registerSynth([
            \Livewire\LivewireSynth::class,
        ]);
    }

    protected function bootMechanisms()
    {
        foreach ([
            \Livewire\Mechanisms\HijackBlade\HijackBlade::class,
            \Livewire\Mechanisms\TrackCurrentComponent::class,
            \Livewire\Mechanisms\CompileLivewireTags::class,
            \Livewire\Mechanisms\RenderComponent::class,
            \Livewire\Mechanisms\FrontendAssets::class,
        ] as $mechanism) {
            if (in_array(\Livewire\Drawer\IsSingleton::class, class_uses($mechanism))) {
                $mechanism::getInstance()->boot();
            } else {
                (new $mechanism)->boot();
            }
        }
    }

    protected function bootFeatures()
    {
        foreach ([
            \Livewire\Features\SupportWireModelingNestedComponents\SupportWireModelingNestedComponents::class,
            \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class,
            \Livewire\Features\SupportJavaScriptOrderedArrays\SupportJavaScriptOrderedArrays::class,
            \Livewire\Features\SupportChecksumErrorDebugging\SupportChecksumErrorDebugging::class,
            \Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement::class,
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class,
            \Livewire\Features\SupportComputedProperties\SupportComputedProperties::class,
            \Livewire\Features\SupportNestingComponents\SupportNestingComponents::class,
            \Livewire\Features\SupportLockedProperties\SupportLockedProperties::class,
            \Livewire\Features\SupportPersistedLayouts\SupportPersistedLayouts::class,
            \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::class,
            \Livewire\Features\SupportPageComponents\SupportPageComponents::class,
            \Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks::class,
            \Livewire\Features\SupportDirtyDetection\SupportDirtyDetection::class,
            \Livewire\Features\SupportReactiveProps\SupportReactiveProps::class,
            \Livewire\Features\SupportFileDownloads\SupportFileDownloads::class,
            \Livewire\Features\SupportHotReloading\SupportHotReloading::class,
            \Livewire\Features\SupportLazyLoading\SupportLazyLoading::class,
            \Livewire\Features\SupportTeleporting\SupportTeleporting::class,
            \Livewire\Features\SupportUnitTesting\SupportUnitTesting::class,
            \Livewire\Features\SupportFileUploads\SupportFileUploads::class,
            \Livewire\Features\SupportValidation\SupportValidation::class,
            \Livewire\Features\SupportWireables\SupportWireables::class,
            \Livewire\Features\SupportRedirects\SupportRedirects::class,
            \Livewire\Features\SupportEntangle\SupportEntangle::class,
            \Livewire\Features\SupportLocales\SupportLocales::class,
            \Livewire\Features\SupportModels\SupportModels::class,
            \Livewire\Features\SupportTraits\SupportTraits::class,
            \Livewire\Features\SupportEvents\SupportEvents::class,
        ] as $feature) {
            if (in_array(\Livewire\Drawer\IsSingleton::class, class_uses($feature))) {
                $feature::getInstance()->boot();
            } else {
                (new $feature)->boot();
            }
        }
    }
}
