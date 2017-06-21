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
    public function scopeNew($query, $limit = 20, $attempts = 5)
    {
        $query->where('status', 1)
            ->where('auto_update', 1)
            ->whereNull('logo_location')
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
            ->whereNotNull('logo_location')
            ->where('attempts', '<', $attempts)
            ->orderBy('updated_at', 'asc')
            ->orderBy('attempts', 'asc');
        if ($limit) {
            $query->take($limit);
        }
        return $query;
    }

}