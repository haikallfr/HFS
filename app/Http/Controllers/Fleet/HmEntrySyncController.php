<?php

namespace App\Http\Controllers\Fleet;

use App\Actions\Fleet\PersistHmEntry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HmEntrySyncController extends Controller
{
    public function __invoke(Request $request, PersistHmEntry $persistHmEntry): JsonResponse
    {
        abort_unless($request->user()?->can('fleet.hm.input'), 403);

        $validated = $request->validate([
            'entries' => ['required', 'array', 'min:1'],
        ]);

        $results = [];

        foreach ($validated['entries'] as $index => $entry) {
            $entryValidator = Validator::make($entry, [
                'local_id' => ['required', 'string', 'max:255'],
                'unitId' => ['required', 'exists:units,id'],
                'workAreaId' => ['required', 'exists:work_areas,id'],
                'inputDate' => ['required', 'date'],
                'shift' => ['required', 'in:day,night'],
                'hmStart' => ['required', 'numeric', 'min:0'],
                'hmEnd' => ['required', 'numeric', 'gt:hmStart'],
                'fuelLiters' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'capturePayload' => ['required', 'string'],
                'captureTimestamp' => ['required', 'date'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ]);

            if ($entryValidator->fails()) {
                return response()->json([
                    'message' => "Validasi antrean offline gagal pada item ke-".($index + 1).'.',
                    'errors' => $entryValidator->errors(),
                ], 422);
            }

            $log = $persistHmEntry->handle([
                ...$entryValidator->validated(),
                'syncStatus' => 'server',
            ], $request->user());

            $results[] = [
                'local_id' => $entryValidator->validated()['local_id'],
                'server_id' => $log->id,
                'status' => 'synced',
            ];
        }

        return response()->json([
            'message' => 'Offline queue berhasil disinkronkan.',
            'results' => $results,
        ]);
    }
}
