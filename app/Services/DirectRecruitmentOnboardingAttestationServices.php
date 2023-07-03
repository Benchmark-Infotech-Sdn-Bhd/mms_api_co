<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\OnboardingAttestation;
use App\Models\OnboardingDispatch;
use App\Models\OnboardingEmbassy;
use App\Models\EmbassyAttestationFileCosting;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class DirectRecruitmentOnboardingAttestationServices
{
    /**
     * @var OnboardingAttestation
     */
    private OnboardingAttestation $onboardingAttestation;

    /**
     * @var OnboardingDispatch
     */
    private OnboardingDispatch $onboardingDispatch;

    /**
     * @var OnboardingEmbassy
     */
    private OnboardingEmbassy $onboardingEmbassy;

    /**
     * @var EmbassyAttestationFileCosting
     */
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;

    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * DirectRecruitmentOnboardingAttestationServices constructor.
     * @param OnboardingAttestation $onboardingAttestation;
     * @param OnboardingDispatch $onboardingDispatch;
     * @param OnboardingEmbassy $onboardingEmbassy;
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting;
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param Storage $storage;
     */
    public function __construct(OnboardingAttestation $onboardingAttestation, OnboardingDispatch $onboardingDispatch, OnboardingEmbassy $onboardingEmbassy, EmbassyAttestationFileCosting $embassyAttestationFileCosting, DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, Storage $storage)
    {
        $this->onboardingAttestation = $onboardingAttestation;
        $this->onboardingDispatch = $onboardingDispatch;
        $this->onboardingEmbassy = $onboardingEmbassy;
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->storage = $storage;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'onboarding_country_id' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateDispatchValidation(): array
    {
        return [
            'onboarding_attestation_id' => 'required',
            'date' => 'required|date|date_format:d/m/Y',
            'time' => 'required',
            'reference_number' => 'required',
            'employee_id' => 'required',
            'from' => 'required',
            'calltime' => 'required',
            'area' => 'required',
            'employer_name' => 'required',
            'phone_number' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function uploadEmbassyFileValidation(): array
    {
        return [
            'onboarding_attestation_id' => 'required',
            'embassy_attestation_id' => 'required',
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->onboardingAttestation
        ->where([
            ['application_id', $request['application_id']],
            ['onboarding_country_id', $request['onboarding_country_id']],
        ])
        ->select('id', 'application_id', 'onboarding_country_id', 'item_name', 'status', 'submission_date', 'collection_date', 'created_at', 'updated_at')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->onboardingAttestation->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function create($request): bool|array
    {
        $onboardingAttestation = $this->onboardingAttestation->where([
            ['application_id', $request['application_id']],
            ['onboarding_country_id', $request['onboarding_country_id']],
        ])->first(['id', 'application_id', 'onboarding_country_id']);

        if(is_null($onboardingAttestation)){
            $validator = Validator::make($request, $this->createValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
            $this->onboardingAttestation->create([
                'application_id' => $request['application_id'] ?? 0,
                'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                'item_name' => 'Attestation Submission',
                'status' => 'Pending',
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0
            ]);

            $onBoardingStatus['application_id'] = $request['application_id'];
            $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
            $onBoardingStatus['onboarding_status'] = 3; //Agent Added
            $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);

            return true;
        }else{
            return false;
        }
        
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if (isset($request['submission_date']) && !empty($request['submission_date'])) {
            $request['status'] = 'Submitted';
        }
        if (isset($request['collection_date']) && !empty($request['collection_date'])) {
            $request['status'] = 'Collected';
        }
        $onboardingAttestation = $this->onboardingAttestation->findOrFail($request['id']);
        if(isset($request['submission_date']) && !empty($request['submission_date'])){
            $onboardingAttestation->submission_date =  $request['submission_date'];
        }
        if(isset($request['collection_date']) && !empty($request['collection_date'])){
            $onboardingAttestation->collection_date =  $request['collection_date'];
        }
        $onboardingAttestation->file_url =  $request['file_url'] ?? $onboardingAttestation->file_url;
        $onboardingAttestation->remarks =  $request['remarks'] ?? $onboardingAttestation->remarks;
        $onboardingAttestation->status =  $request['status'] ?? $onboardingAttestation->status;
        $onboardingAttestation->modified_by =  $request['modified_by'] ?? $onboardingAttestation->modified_by;
        $onboardingAttestation->save();
        return true;
    }
    /**
     * show Dispatch
     * @param $request
     * @return mixed
     */   
    public function showDispatch($request): mixed
    {
        return $this->onboardingDispatch->where('onboarding_attestation_id', $request['onboarding_attestation_id'])->get();
    }
    /**
     * update dispatch
     * @param $request
     * @return bool|array
     */
    public function updateDispatch($request): bool|array
    {
        $onboardingDispatch = $this->onboardingDispatch->where(
            'onboarding_attestation_id', $request['onboarding_attestation_id']
        )->first(['id', 'onboarding_attestation_id', 'date', 'time', 'reference_number', 'employee_id', 'from', 'calltime', 'area', 'employer_name', 'phone_number', 'remarks']);

        $request['reference_number'] = 'JO00000'.$this->onboardingDispatch->count() + 1;

        $validator = Validator::make($request, $this->updateDispatchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        
        if(is_null($onboardingDispatch)){
            $this->onboardingDispatch->create([
                'onboarding_attestation_id' => $request['onboarding_attestation_id'] ?? 0,
                'date' => $request['date'] ?? '',
                'time' => $request['time'] ?? '',
                'reference_number' => $request['reference_number'],
                'employee_id' => $request['employee_id'] ?? '',
                'from' => $request['from'] ?? '',
                'calltime' => $request['calltime'] ?? '',
                'area' => $request['area'] ?? '',
                'employer_name' => $request['employer_name'] ?? '',
                'phone_number' => $request['phone_number'] ?? '',
                'remarks' => $request['remarks'] ?? '',
                'created_by' =>  $request['created_by'] ?? 0,
                'modified_by' =>  $request['created_by'] ?? 0
            ]);
        }else{
            $onboardingDispatch->update([
                'date' => $request['date'] ?? '',
                'time' => $request['time'] ?? '',
                'employee_id' => $request['employee_id'] ?? '',
                'from' => $request['from'] ?? '',
                'calltime' => $request['calltime'] ?? '',
                'area' => $request['area'] ?? '',
                'employer_name' => $request['employer_name'] ?? '',
                'phone_number' => $request['phone_number'] ?? '',
                'remarks' => $request['remarks'] ?? '',
                'modified_by' =>  $request['created_by'] ?? 0
            ]);
        }
        
        return true;
    }
    /**
     * list embassy file
     * @param $request
     * @return mixed
     */   
    public function listEmbassy($request): mixed
    {
        $onboardingAttestation = $this->onboardingAttestation
        ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.id', 'onboarding_attestation.onboarding_country_id')
            ->where('onboarding_attestation.id', $request['onboarding_attestation_id'])
            ->select('directrecruitment_onboarding_countries.country_id')
            ->distinct('directrecruitment_onboarding_countries.country_id')
            ->get();
        $request['country_id'] = $onboardingAttestation[0]['country_id'] ?? 0;

        return $this->embassyAttestationFileCosting
        ->leftJoin('onboarding_embassy', function ($join) use ($request) {
            $join->on('onboarding_embassy.embassy_attestation_id', '=', 'embassy_attestation_file_costing.id')
            ->where([
                ['onboarding_embassy.onboarding_attestation_id', $request['onboarding_attestation_id']],
                ['onboarding_embassy.deleted_at', null],
            ]);
        })
        ->where([
            ['embassy_attestation_file_costing.country_id', $request['country_id']]
        ])
        ->select('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id', 'embassy_attestation_file_costing.title', 'embassy_attestation_file_costing.amount', 'onboarding_embassy.id as onboarding_embassy_id', 'onboarding_embassy.file_name as onboarding_embassy_file_name', 'onboarding_embassy.file_url as onboarding_embassy_file_url', 'onboarding_embassy.amount as onboarding_embassy_amount')
        ->distinct('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id')
        ->orderBy('embassy_attestation_file_costing.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * show embassy file
     * @param $request
     * @return mixed
     */   
    public function showEmbassyFile($request): mixed
    {
        return $this->embassyAttestationFileCosting
        ->leftJoin('onboarding_embassy', function ($join) use ($request) {
            $join->on('onboarding_embassy.embassy_attestation_id', '=', 'embassy_attestation_file_costing.id')
            ->where([
                ['onboarding_embassy.onboarding_attestation_id', $request['onboarding_attestation_id']],
                ['onboarding_embassy.deleted_at', null],
            ]);
        })
        ->where([
            ['embassy_attestation_file_costing.id', $request['embassy_attestation_id']]
        ])
        ->select('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id', 'embassy_attestation_file_costing.title', 'embassy_attestation_file_costing.amount', 'onboarding_embassy.id as onboarding_embassy_id', 'onboarding_embassy.file_name as onboarding_embassy_file_name', 'onboarding_embassy.file_url as onboarding_embassy_file_url', 'onboarding_embassy.amount as onboarding_embassy_amount')
        ->distinct('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id')
        ->first();
    }

    /**
     * upload embassy file 
     * @param $request
     * @return bool|array
     */
    public function uploadEmbassyFile($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->uploadEmbassyFileValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingEmbassy = $this->onboardingEmbassy->where([
            ['onboarding_attestation_id', $request['onboarding_attestation_id']],
            ['embassy_attestation_id', $request['embassy_attestation_id']],
            ['deleted_at', null],
        ])->first(['id', 'onboarding_attestation_id', 'embassy_attestation_id', 'file_name', 'file_type', 'file_url', 'amount']);
        
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/onboarding/embassyAttestationCosting/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                if(is_null($onboardingEmbassy)){
                    $this->onboardingEmbassy::create([
                        "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? 0,
                        "embassy_attestation_id" => $request['embassy_attestation_id'] ?? 0,
                        "file_name" => $fileName,
                        "file_type" => 'Embassy Attestation Costing',
                        "file_url" =>  $fileUrl,
                        "amount" => $request['amount'] ?? 0,
                        "created_by" =>  $params['created_by'] ?? 0,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]); 
                }else{
                    $onboardingEmbassy->update([
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl,
                        "amount" => $request['amount'] ?? $onboardingEmbassy->amount,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]); 
                }

            }
        }elseif( isset($request['amount'])){
            if(is_null($onboardingEmbassy)){
                $this->onboardingEmbassy::create([
                    "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? 0,
                    "embassy_attestation_id" => $request['embassy_attestation_id'] ?? 0,
                    "amount" => $request['amount'] ?? 0,
                    "created_by" =>  $params['created_by'] ?? 0,
                    "modified_by" =>  $params['created_by'] ?? 0
                ]); 
            }else{
                $onboardingEmbassy->update([
                    "amount" => $request['amount'],
                    "modified_by" =>  $params['created_by'] ?? 0
                ]); 
            }
        }else{
            return false;
        }
        return true;
    }


    /**
     * delete embassy file
     * @param $request
     * @return array
     */    
    public function deleteEmbassyFile($request): array
    {   
        $data = $this->onboardingEmbassy::find($request['onboarding_embassy_id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }

}