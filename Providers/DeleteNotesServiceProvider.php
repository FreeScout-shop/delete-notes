<?php

namespace Modules\DeleteNotes\Providers;

use App\Thread;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

// Module alias.
define('DN_MODULE', 'deletenotes');

class DeleteNotesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    // User permission.
    const PERM_DELETE_NOTE = 18;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = \Module::getPublicPath(DN_MODULE).'/js/laroute.js';
            $javascripts[] = \Module::getPublicPath(DN_MODULE).'/js/module.js';
            return $javascripts;
        });

        \Eventy::addFilter('user_permissions.list', function($list) {
            $list[] = AvatarsServiceProvider::PERM_DELETE_NOTE;
            return $list;
        });

        \Eventy::addFilter('user_permissions.name', function($name, $permission) {
            if ($permission != AvatarsServiceProvider::PERM_DELETE_NOTE) {
                return $name;
            }
            return __('Users are allowed to delete their own notes');
        }, 20, 2);

        // Show menu item.
        \Eventy::addAction('thread.menu', function($thread) {
            if (AvatarsServiceProvider::canDeleteNote($thread)) { ?>
					    <li>
						    <a href="#" onclick="clickDeleteNote(<?php echo $thread->id ?>)"><?php echo __("Delete") ?></a>
					    </li><?php
            }
        });

        // JS messages
        \Eventy::addAction('js.lang.messages', function() {
            ?>
			    "delete": "<?php echo __("Delete") ?>",
			    "confirm_delete_note": "<?php echo __("Delete this note?") ?>",
            <?php
        });
    }

    public static function canDeleteNote($thread, $user = null)
    {
			if ($thread->type != Thread::TYPE_NOTE) {
				return false;
      }

      if (!$user) {
          $user = auth()->user();
      }

      if (!$user) {
          return false;
      }

			if ($user->isAdmin() || (
				\Auth::user()->can('edit', $thread)
				&& $user->hasPermission(AvatarsServiceProvider::PERM_DELETE_NOTE)
			)) {
				return true;
      }

      return false;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('deletenotes.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'deletenotes'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/deletenotes');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/deletenotes';
        }, \Config::get('view.paths')), [$sourcePath]), 'deletenotes');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
