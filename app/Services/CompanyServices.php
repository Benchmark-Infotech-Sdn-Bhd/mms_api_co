<?php

namespace App\Services;

use App\Models\Company;
use App\Models\UserCompany;
use App\Models\User;
use App\Models\FeeRegistration;
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
     * @var User
     */
    private User $user;
    /**
     * @var feeRegistration
     */
    private FeeRegistration $feeRegistration;

    /**
     * CompanyServices constructor
     * @param Company $company
     * @param UserCompany $userCompany
     * @param User $user
     * @param FeeRegistration $feeRegistration
     */
    public function __construct(Company $company, UserCompany $userCompany, User $user, FeeRegistration $feeRegistration) 
    {
        $this->company = $company;
        $this->userCompany = $userCompany;
        $this->user = $user;
        $this->feeRegistration = $feeRegistration;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->company
            ->where(function ($query) use ($request) {
                $query->where('company_name', 'like', '%'.$request['search'].'%')
                ->orWhere('register_number', 'like', '%'.$request['search'].'%')
                ->orWhere('pic_name', 'like', '%'.$request['search'].'%');
            })
            ->select('id', 'company_name', 'register_number', 'country', 'state', 'pic_name', 'status', 'parent_id', 'parent_flag')
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
        $companyDetails = $this->company->create([
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
        if(isset($request['parent_id']) && !empty($request['parent_id'])) {
            $this->company->where('id', $request['parent_id'])->update(['parent_flag' => 1]);
        }
        foreach(Config::get('services.STANDARD_FEE_NAMES') as $index => $fee ) {
            $this->feeRegistration::create([
                'item_name' => $fee, 
                'cost' => Config::get('services.STANDARD_FEE_COST')[$index], 
                'fee_type' => 'Standard', 
                'created_by' => $request["created_by"], 
                'company_id' => $companyDetails->id
            ]);
        }
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
            ->where('parent_flag', '!=', 1)
            ->select('id', 'company_name')
            ->get();
    }
    /**
     * @param $request
     * @return bool
     */
    public function assignSubsidiary($request): bool
    {
        $this->company->whereIn('id', $request['subsidiary_company'])
        ->update([
            'parent_id' => $request['parent_company_id'],
            'modified_by' => $request['modified_by']
        ]);
        $this->company->where('id', $request['parent_company_id'])->update(['parent_flag' => 1]);
        return true;
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
    /**
     * @param $request
     * @return bool
     */
    public function updateCompanyId($request): bool
    {
        $userDetails = $this->user->findOrFail($request['user_id']);
        $userDetails->company_id = $request['company_id'] ?? $userDetails->company_id;
        $userDetails->save();
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function subsidiaryDropdownBasedOnParent($request): mixed
    {
        return $this->company
            ->where('parent_id', $request['company_id'])
            ->select('id', 'company_name')
            ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function dropdown($request): mixed
    {
        return $this->company
            ->where('status', 1)
            ->select('id', 'company_name')
            ->get();
    }
}