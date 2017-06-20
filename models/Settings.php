<?php namespace HeathDutton\LogoStash\Models;

use BackendMenu;
use Model;
use System\Classes\SettingsManager;

/**
 * Class GlobalSettings
 * @package HeathDutton\LogoStash\Models
 */
class Settings extends Model
{
    /**
     * @var array
     */
    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string
     */
    public $settingsCode = 'heathdutton_logostash_settings';

    /**
     * @var string
     */
    public $settingsFields = 'fields.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('HeathDutton.LogoStash', 'settings');
    }
}
