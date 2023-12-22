<?php

namespace App\Services;

use App\Models\InvoiceItemsTemp;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceItemsTempServices
{
    /**
     * @var InvoiceItemsTemp
     */
    private InvoiceItemsTemp $invoiceItemsTemp;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * InvoiceServices constructor.
     * @param InvoiceItemsTemp $invoiceItemsTemp
     * @param ValidationServices $validationServices
     */
    public function __construct(
            InvoiceItemsTemp        $invoiceItemsTemp,
            ValidationServices      $validationServices
    )
    {
        $this->invoiceItemsTemp = $invoiceItemsTemp;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        if(!($this->validationServices->validate($request->toArray(),$this->invoiceItemsTemp->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        $invoiceItemsTempCount = $this->invoiceItemsTemp->where('created_by', $user['id'])->count(); 
        
        if(isset($invoiceItemsTempCount) && ($invoiceItemsTempCount != 0)){

            $invoiceItemsTempChk = $this->invoiceItemsTemp->where('crm_prospect_id', $request['crm_prospect_id'])->where('service_id', $request['service_id'])->where('created_by', $user['id'])->count();

            if(isset($invoiceItemsTempChk) && ($invoiceItemsTempChk != $invoiceItemsTempCount)){
                return [
                    "isExists" => false,
                    "message" => "Please complete the Pending Invoice before raising a new one. Proceed to Pending Invoice?"
                ];
            }
        }

        $lineItems = json_decode($request['invoice_items']);
        foreach($lineItems as $item){
            $invoiceItemsTemp = $this->invoiceItemsTemp::create([
                'crm_prospect_id' => $request['crm_prospect_id'],
                'service_id' => $request['service_id'],
                'expense_id' => $item->expense_id,
                'invoice_number' => $request['invoice_number'],
                'item' => $item->item ?? '',
                'description' => $item->description ?? '',
                'quantity' => $item->quantity ?? '',
                'price' => $item->price ?? 0,
                'account' => $item->account ?? '',
                'tax_rate' => $item->tax_rate ?? 0,
                'total_price' => $item->total_price ?? 0,
                'created_by'    => $user['id'],
                'modified_by'   => $user['id']
            ]);
        }

        return $invoiceItemsTemp;
    } 

    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        if(!($this->validationServices->validate($request->toArray(),$this->invoiceItemsTemp->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $invoiceItemsTemp = $this->invoiceItemsTemp->where('created_by', $user['id'] )->findOrFail($request['id']);
        if(is_null($invoiceItemsTemp)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

        if(($invoiceItemsTemp->crm_prospect_id != $request['crm_prospect_id']) || ($invoiceItemsTemp->service_id != $request['service_id'])){
            return [
                "isExists" => false,
                "message" => "Please complete the Pending Invoice before raising a new one. Proceed to Pending Invoice?"
            ];
        }

        $invoiceItemsTemp->crm_prospect_id = $request['crm_prospect_id'] ?? $invoiceItemsTemp->crm_prospect_id;
        $invoiceItemsTemp->service_id = $request['service_id'] ?? $invoiceItemsTemp->service_id;
        $invoiceItemsTemp->expense_id = $request['expense_id'] ?? $invoiceItemsTemp->expense_id;
        $invoiceItemsTemp->invoice_number = $request['invoice_number'] ?? $invoiceItemsTemp->invoice_number;
        $invoiceItemsTemp->item = $request['item'] ?? $invoiceItemsTemp->item;
        $invoiceItemsTemp->description = $request['description'] ?? $invoiceItemsTemp->description;
        $invoiceItemsTemp->quantity = $request['quantity'] ?? $invoiceItemsTemp->quantity;
        $invoiceItemsTemp->price = $request['price'] ?? $invoiceItemsTemp->price;
        $invoiceItemsTemp->account = $request['account'] ?? $invoiceItemsTemp->account;
        $invoiceItemsTemp->tax_rate = $request['tax_rate'] ?? $invoiceItemsTemp->tax_rate;
        $invoiceItemsTemp->total_price = $request['total_price'] ?? $invoiceItemsTemp->total_price;
        $invoiceItemsTemp->created_by = $request['created_by'] ?? $user['id'];
        $invoiceItemsTemp->modified_by = $user['id'];
        $invoiceItemsTemp->save();

        return true;
    }
    
    
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $data = $this->invoiceItemsTemp->with(['crm_prospect' => function ($query) {
                $query->select(['id', 'company_name']);
            }])->where('created_by',$user['id'])->find($request['id']);

        if(is_null($data)){
            return [
                "message" => "Data not found"
            ];
        }

        return $data;

    }
    
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->invoiceItemsTemp
        ->with(['crm_prospect' => function ($query) {
                $query->select(['id', 'company_name']);
            }])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('item', 'like', "%{$request['search_param']}%")
                ->orWhere('description', 'like', '%'.$request['search_param'].'%');
            }            
        })
        ->where('created_by',$user['id'])->select('id','crm_prospect_id','service_id','expense_id','invoice_number','item','description','quantity','price','account','tax_rate','total_price','created_by','modified_by', 'created_at')
        ->distinct()
        ->orderBy('created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * delete the data.
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $invoiceItemsTemp = $this->invoiceItemsTemp::where('created_by',$user['id'])->find($request['id']);

        if(is_null($invoiceItemsTemp)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $invoiceItemsTemp->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * delete all the data for the login user when use back button from invoice without submitting.
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAll(): mixed
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $invoiceItemsTemp = $this->invoiceItemsTemp->where('created_by', $user['id'])->count();
        
        if(isset($invoiceItemsTemp) && ($invoiceItemsTemp == 0)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $this->invoiceItemsTemp->where('created_by', $user['id'])->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

}
