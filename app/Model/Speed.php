<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Speed extends Model
{
    protected $guarded = [];

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('speeds.created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeCountry($query, $country = 'AUS')
    {
        return $query->select(['*', 'speeds.created_at AS date'])
            ->join('asns', 'speeds.asn_id', '=', 'asns.id')
            ->where('asns.country', '=', $country);
    }
}
