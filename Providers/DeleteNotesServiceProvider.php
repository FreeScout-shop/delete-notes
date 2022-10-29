<?php

namespace Modules\DeleteNotes\Providers;

use App\Thread;
use App\User;
use Illuminate\Support\ServiceProvider;

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

    // Action type.
    const ACTION_TYPE_DELETE = 183;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // JS messages
        \Eventy::addAction('js.lang.messages', function() {
            ?>
			    "delete": "<?php echo __("Delete") ?>",
			    "confirm_delete_note": "<?php echo __("Delete this note?") ?>",
            <?php
        });

        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = \Module::getPublicPath(DN_MODULE).'/js/laroute.js';
            $javascripts[] = \Module::getPublicPath(DN_MODULE).'/js/module.js';
            return $javascripts;
        });

        \Eventy::addFilter('user_permissions.list', function($list) {
            $list[] = DeleteNotesServiceProvider::PERM_DELETE_NOTE;
            return $list;
        });

        \Eventy::addFilter('user_permissions.name', function($name, $permission) {
            if ($permission != DeleteNotesServiceProvider::PERM_DELETE_NOTE) {
                return $name;
            }
            return __('Users are allowed to delete their own notes');
        }, 20, 2);

        // Show menu item.
        \Eventy::addAction('thread.menu', function($thread) {
            if (DeleteNotesServiceProvider::canDeleteNote($thread)) { ?>
					    <li>
						    <a href="#" onclick="clickDeleteNote(<?php echo $thread->id ?>)"><?php echo __("Delete") ?></a>
					    </li><?php
            }
        });

        // Show action description for the line item thread.
        \Eventy::addFilter('thread.action_text', function($did_this, $thread, $conversation_number, $escape) {
            switch($thread->action_type) {
                case self::ACTION_TYPE_DELETE:
                  $user = User::find($thread->user_id)->getFullName();
                  if ($escape) {
                      $user = htmlspecialchars($user);
                  }
									$did_this = __(':user has deleted a note', [
                    'user' => $user
									]);
                  break;
            }
            return $did_this;
        }, 20, 4);
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
				&& $user->hasPermission(DeleteNotesServiceProvider::PERM_DELETE_NOTE)
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
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
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
