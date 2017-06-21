<?php namespace HeathDutton\LogoStash\Models;

use HeathDutton\LogoStash\Classes\EmployerHelper;
use Model;

/**
 * Class Logo
 * @package HeathDutton\LogoStash\Models
 */
class Logo extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'heathdutton_logostash_logo';

    /**
     * Find logos that have never been stored.
     *
     * @param $query
     * @param int $limit
     * @param int $attempts
     * @return mixed
     */
    public function scopeNew($query, $limit = 20, $attempts = 5)
    {
        $query->where('status', 1)
            ->where('auto_update', 1)
            ->where('logo_location', '')
            ->orWhereNull('logo_location')
            ->where('attempts', '<', $attempts)
            ->orderBy('updated_at', 'asc')
            ->orderBy('attempts', 'asc');
        if ($limit) {
            $query->take($limit);
        }
        return $query;
    }

    /**
     * Find old logos that may need updating.
     *
     * @param $query
     * @param int $limit
     * @param int $attempts
     * @return mixed
     */
    public function scopeOld($query, $limit = 20, $attempts = 5)
    {
        $query->where('status', 1)
            ->where('auto_update', 1)
            ->where('logo_location', '!=', '')
            ->whereNotNull('logo_location')
            ->where('attempts', '<', $attempts)
            ->orderBy('updated_at', 'asc')
            ->orderBy('attempts', 'asc');
        if ($limit) {
            $query->take($limit);
        }
        return $query;
    }

    /**
     * Given an employer, get the URL of the most relevant logo.
     *
     * Store missing employers for cron pulling.
     *
     * @param string $employer
     * @param string $logo_location
     *      The default location for the logo to fall back on, if one cannot be found.
     * @return mixed
     */
    public function getLocation($employer, $logo_location)
    {
        // Normalize the employer name.
        $employer = EmployerHelper::normalize($employer);
        if ($employer) {
            // Check if we already have this logo in the database.
            $logo = $this->where('employer_name', $employer)->first();
            if (count($logo)) {
                // Logo entry exists, but may not have a location yet, or may be disabled.
                if (!empty($logo->logo_location) && $logo->status) {
                    // Logo location is known and active, we can redirect to it permanently.
                    $logo_location = $logo->logo_location;
                }
            } else {
                // No entry for this logo, let's create one.
                $logo = new Logo();
                $logo->setAttribute('employer_name', $employer);
                $logo->save();
            }
        }
        return $logo_location;
    }

}