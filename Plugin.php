<?php namespace HeathDutton\LogoStash;

use System\Classes\PluginBase;
use HeathDutton\LogoStash\Models\Settings;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function register()
    {
        $this->registerConsoleCommand('heathdutton.logostash', 'HeathDutton\LogoStash\Console\LogoUpdate');
    }

    /**
     * @param string $schedule
     */
    public function registerSchedule($schedule)
    {
        if (Settings::get('auto_update', true)) {
            $schedule->command('heathdutton:logostash --timeout=55')->everyMinute();
        }
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Settings',
                'description' => 'Configure Logo Stash settings.',
                'category'    => 'Logo Stash',
                'icon'        => 'icon-cloud',
                'class'       => 'HeathDutton\LogoStash\Models\Settings',
                'order'       => 500,
                'keywords'    => 'logo stash configure settings',
                'permissions' => ['heathdutton.logostash::logostash_edit_settings']
            ]
        ];
    }
}
