<?php namespace HeathDutton\LogoStash\Models;

use Model;

/**
 * Model
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
    public function scopeNew($query, $limit = 100, $attempts = 5)
    {
        return $query->where('status', 1)
            ->where('auto_update', 1)
            ->where('logo_location', '')
            ->where('attempts', '<', $attempts)
            ->orderBy('updated_at', 'asc')
            ->orderBy('attempts', 'asc')
            ->take($limit);
    }

    /**
     * Find old logos that may need updating.
     *
     * @param $query
     * @param int $limit
     * @param int $attempts
     * @return mixed
     */
    public function scopeOld($query, $limit = 100, $attempts = 5)
    {
        return $query->where('status', 1)
            ->where('auto_update', 1)
            ->where('logo_location', '!=', '')
            ->where('attempts', '<', $attempts)
            ->orderBy('updated_at', 'asc')
            ->orderBy('attempts', 'asc')
            ->take($limit);
    }

    public function byEmployer($employer) {

    }

}