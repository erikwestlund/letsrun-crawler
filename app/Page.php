<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{

    protected $guarded = ['id'];

    /**
     * Scope a query to only include archive pages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchive($query)
    {
        return $query->where('archive', true);
    }

}
