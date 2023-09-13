<?php

namespace App\Http\Controllers;

use App\Models\GroupUnit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Membership;

class MembershipStatsController extends Controller
{
    private Membership $membershipModel;

    /**
     * @param Membership $membershipModel
     */
    public function __construct(Membership $membershipModel)
    {
        $this->membershipModel = $membershipModel;
    }

    public function downloadCSV(Request $request): StreamedResponse
    {
        $date = $request->input('date');
        $type = $request->input('type');
        $regionId = $request->input('region_id');
        $groupId = $request->input('group_id');

        $groupUnitIds = !empty($groupId) ? GroupUnit::query()->where('group_id', $groupId)->pluck('unit_id')->toArray() : [];
        $excludedMembershipIds = Membership::getExcludedIdsForMembershipStats($groupUnitIds, $regionId);

        if ($type === 'members') {
            $headersArray = ['Member ID', 'GL Ref'];
            $data = $this->membershipModel->currentMembersByDate($date, $excludedMembershipIds, $regionId, $groupUnitIds);
        } else {
            $headersArray = ['Member ID', 'GL Ref', 'Unit Name', 'Unit No'];
            $data = $this->membershipModel->currentMembershipsByDate($date, $excludedMembershipIds, $regionId, $groupUnitIds);
        }

        $filename = $type . '-' . $date . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Create and return a streamed response to download the CSV file
        return new StreamedResponse(function () use ($data, $headersArray) {
            $handle = fopen('php://output', 'w');

            // Add CSV header
            fputcsv($handle, $headersArray);

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
