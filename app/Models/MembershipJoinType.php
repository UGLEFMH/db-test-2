<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipJoinType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'membership_join_types';

    /**
     * @var array
     */
    protected $guarded = [];

    const EXCLUDED_JOIN_TYPES_FOR_MEMBERSHIP_STATS = [
        'HONORARY',
        'HONORARY(NS)'
    ];
}
