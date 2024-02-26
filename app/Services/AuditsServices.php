<?php

namespace App\Services;

use App\Models\Audits;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuditsServices
{
    /**
     * @var audits
     */
    private Audits $audits;

    /**
     * AuditsServices constructor.
     * @param Audits $audits
     */
    public function __construct(Audits $audits)
    {
        $this->audits = $audits;
    }

    /**
     * Perform search validation.
     *
     * @return array
     *     The validation rules for the search parameters.
     *
     * @example
     *     [
     *         'from_date' => 'required|date|date_format:Y-m-d',
     *         'to_date' => 'required|date|date_format:Y-m-d',
     *     ]
     */
    public function searchValidation(): array
    {
        return [
            'from_date' => 'required|date|date_format:Y-m-d',
            'to_date' => 'required|date|date_format:Y-m-d',
        ];
    }


    /**
     * List audits based on the given request parameters.
     *
     * @param array $request The request parameters.
     * @return Arrayable|LengthAwarePaginator|array Returns the list of audits or an error array if validation fails.
     */
    public function list($request)
    {
        if (!empty($request['from_date']) && !empty($request['to_date'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        $from = empty($request['from_date']) ? Carbon::now()->subDays(10)->startOfDay() : Carbon::parse($request['from_date'])->startOfDay();
        $to = empty($request['to_date']) ? Carbon::now()->endOfDay() : Carbon::parse($request['to_date'])->endOfDay();

        $query = $this->audits->with(['user' => function ($query) {
            $query->select(['id', 'name']);
        }])
            ->select('id', 'user_type', 'user_id', 'event', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'url', 'created_at', 'updated_at')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('id', 'desc');

        return empty($request['export']) ? $query->paginate(Config::get('services.paginate_worker_row')) : $query->get();
    }

    /**
     * Delete old audit records.
     *
     * @return bool Returns true if the deletion is successful, otherwise returns false.
     */
    public function delete(): bool
    {
        try {
            $conditionDate = Carbon::now()->subYear(3)->toDateTimeString();
            $this->audits->where('created_at', '<=', $conditionDate)->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Exception in delete' . $e);
            return false;
        }
    }
}
