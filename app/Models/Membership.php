<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'memberships';

    /**
     * @var array
     */
    protected $guarded = [];

    const IN_OUT_VALUE_IN = 1;
    const IN_OUT_VALUE_OUT = 2;
    const IN_OUT_VALUE_PENDING = 3;

    const JOIN_TYPES_ARRAY = [
        "INITIATE",
        "JOINER",
        "RE-JOINER",
        "FOUNDER",
        "HONORARY",
        "HONORARY(NS)",
    ];

    const MEMBERSHIP_STATUS_CURRENT = 'CURRENT';

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @param $groupUnitIds
     * @param $regionId
     * @return array
     */
    public static function getExcludedIdsForMembershipStats($groupUnitIds, $regionId): array
    {
        $query = Membership::query()
            ->join('membership_join_types', 'memberships.membership_join_type_id', '=', 'membership_join_types.id')
            ->whereIn('membership_join_types.name', MembershipJoinType::EXCLUDED_JOIN_TYPES_FOR_MEMBERSHIP_STATS);

        if (!empty($groupUnitIds)) {
            $query->whereIn('memberships.unit_id', $groupUnitIds);
        }

        if (!empty($regionId)) {
            $query->join('units', 'units.id', '=', 'memberships.unit_id')
                ->join('regions', 'regions.id', '=', 'units.region_id')
                ->where('regions.id', $regionId);
        }

        return $query->pluck('memberships.id')
            ->toArray();
    }

    /**
     * @param $query
     * @param $date
     * @param $excludedMembershipIds
     * @param $regionId
     * @param $groupUnitIds
     * @return mixed
     */
    private static function commonMembershipQuery($query, $date, $excludedMembershipIds, $regionId, $groupUnitIds): mixed
    {
        $query->join('units', 'units.id', '=', 'memberships.unit_id');
        if (!empty($regionId)) {
            $query->join('regions', 'regions.id', '=', 'units.region_id')
                ->where('regions.id', $regionId);
        }

        if (!empty($groupUnitIds)) {
            $query->whereIn('memberships.unit_id', $groupUnitIds);
        }

        return $query
            ->where('date_effective', '<=', $date)
            ->where('in_out', Membership::IN_OUT_VALUE_IN)
            ->whereNotIn('memberships.id', function ($subQuery) use ($excludedMembershipIds, $date, $groupUnitIds) {
                $subQuery->select('parent_id')
                    ->from('memberships')
                    ->where('date_effective', '<=', $date)
                    ->where('in_out', Membership::IN_OUT_VALUE_OUT)
                    ->whereNotIn('parent_id', $excludedMembershipIds);

                if (!empty($regionId)) {
                    $subQuery->join('units', 'units.id', '=', 'memberships.unit_id')
                        ->join('regions', 'regions.id', '=', 'units.region_id')
                        ->where('regions.id', $regionId);
                }

                if (!empty($groupUnitIds)) {
                    $subQuery->whereIn('memberships.unit_id', $groupUnitIds);
                }
            })
            ->whereNotIn('memberships.id', $excludedMembershipIds);
    }

    /**
     * @param $date
     * @param $excludedMembershipIds
     * @param $regionId
     * @param $groupUnitIds
     * @return array
     */
    public function currentMembersByDate($date, $excludedMembershipIds, $regionId, $groupUnitIds): array
    {
        return self::commonMembershipQuery(
            Membership::query()
                ->select('memberships.member_id', 'members.glref')
                ->join('members', 'members.id', '=', 'memberships.member_id'),
            $date,
            $excludedMembershipIds,
            $regionId,
            $groupUnitIds
        )
            ->distinct('member_id')
            ->get()
            ->toArray();
    }

    /**
     * @param $date
     * @param $excludedMembershipIds
     * @param $regionId
     * @param $groupUnitIds
     * @return array
     */
    public static function currentMembershipsCountsByDate($date, $excludedMembershipIds, $regionId, $groupUnitIds): array
    {
        $query = self::commonMembershipQuery(Membership::query(), $date, $excludedMembershipIds, $regionId, $groupUnitIds);

        return ['memberships' => $query->count(), 'members' => $query->distinct('member_id')->count()];
    }

    /**
     * @param $date
     * @param $excludedMembershipIds
     * @param $regionId
     * @param $groupUnitIds
     * @return array
     */
    public static function currentMembershipsByDate($date, $excludedMembershipIds, $regionId, $groupUnitIds): array
    {
        return self::commonMembershipQuery(
            Membership::query()
                ->select('memberships.member_id', 'members.glref', 'units.name as unit_name', 'units.unit_no')
                ->join('members', 'members.id', '=', 'memberships.member_id'),
            $date,
            $excludedMembershipIds,
            $regionId,
            $groupUnitIds
        )
            ->get()
            ->toArray();
    }
}
