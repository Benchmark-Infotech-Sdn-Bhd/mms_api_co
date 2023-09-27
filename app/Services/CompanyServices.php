<?php

namespace App\Services;

use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class CompanyServices
{
    /**
     * @var Company
     */
    private Company $company;
    /**
     * @var UserCompany
     */
    private $userCompany;

    /**
     * CompanyServices constructor,
     * 
     * @param Company $company
     */
    public function __construct(Company $company, UserCompany $userCompany) 
    {
        $this->company = $company;
        $this->userCompany = $userCompany;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->company
            ->whereIn('id', $request['company_id'])
            ->where(function ($query) use ($request) {
                $query->where('company_name', 'like', '%'.$request['search'].'%')
                ->orWhere('register_number', 'like', '%'.$request['search'].'%')
                ->orWhere('pic_name', 'like', '%'.$request['search'].'%');
            })
            ->select('id', 'company_name', 'register_number', 'country', 'state', 'pic_name', 'status', 'parent_id')
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->company->findOrFail($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request, $this->company->rules);
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $this->company->create([
            'company_name' => $request['company_name'] ?? '',
            'register_number' => $request['register_number'] ?? '',
            'country' => $request['country'] ?? '',
            'state' => $request['state'] ?? '',
            'pic_name' => $request['pic_name'] ?? '',
            'role' => $request['role'] ?? 'Admin',
            'status' => $request['status'] ?? 1,
            'parent_id' => $request['parent_id'] ?? 0,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->company->updationRules($request['id']));
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $company = $this->company->findOrFail($request['id']);
        $company->company_name = $request['company_name'] ?? $company->company_name;
        $company->register_number = $request['register_number'] ?? $company->register_number;
        $company->country = $request['country'] ?? $company->country;
        $company->state = $request['state'] ?? $company->state;
        $company->pic_name = $request['pic_name'] ?? $company->pic_name;
        $company->status = $request['status'] ?? $company->status;
        $company->modified_by = $request['modified_by'] ?? $company->modified_by;
        $company->save();
        return true;
    }
    /**
     * @param $request
     * @return bool
     */
    public function updateStatus($request): bool
    {
        $company = $this->company->findOrFail($request['id']);
        $company->status = $request['status'] ?? $company->status;
        $company->modified_by = $request['modified_by'] ?? $company->modified_by;
        $company->save();
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function subsidiaryDropDown($request): mixed
    {
        return $this->company
            ->where('parent_id', 0)
            ->where('id', '!=', $request['current_company_id'])
            ->select('id', 'company_name')
            ->get();
    }
    /**
     * @param $request
     * @return bool
     */
    public function assignSubsidiary($request): bool
    {
        return $this->company->whereIn('id', $request['subsidiary_company'])
        ->update([
            'parent_id' => $request['parent_company_id'],
            'modified_by' => $request['modified_by']
        ]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function parentDropDown($request): mixed
    {
        return $this->company
            ->where('parent_id', 0)
            ->select('id', 'company_name')
            ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function listUserCompany($request): mixed
    {
        return $this->userCompany
                ->with(['company' => function ($query) {
                    $query->select(['id', 'company_name']);
                }])
                ->where('user_id', $request['user_id'])
                ->select('company_id')
                ->get();
    }
}