<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * @var array
     */
    protected $guarded = [];

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'groups_units');
    }
}
