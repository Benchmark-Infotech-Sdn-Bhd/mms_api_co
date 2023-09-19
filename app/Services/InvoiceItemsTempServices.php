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
        $params['created_by'] = $user['id'];
        /*if(!($this->validationServices->validate($request->toArray(),$this->invoiceItemsTemp->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }*/
        $invoiceItemsTemp = $this->invoiceItemsTemp->create([
            'service_id' => $request['service_id'],
            'expense_id' => $request['expense_id'],
            'invoice_number' => $request['invoice_number'],
            'item' => $request['item'] ?? '',
            'description' => $request['description'] ?? '',
            'quantity' => $request['quantity'] ?? '',
            'price' => $request['price'] ?? '',
            'account' => $request['account'] ?? '',
            'tax_rate' => $request['tax_rate'] ?? '',
            'total_price' => $request['total_price'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0
        ]);

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
        $params['modified_by'] = $user['id'];

        /*if(!($this->validationServices->validate($request->toArray(),$this->invoiceItemsTemp->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }*/

        $invoiceItemsTemp = $this->invoiceItemsTemp->findOrFail($request['id']);
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
        //$invoiceItemsTemp->created_by = $request['created_by'] ?? $invoiceItemsTemp->created_by;
        //$invoiceItemsTemp->modified_by = $params['modified_by'];
        $invoiceItemsTemp->save();

        return true;
    }
    
    
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $data = $this->invoiceItemsTemp->find($request['id']);

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
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->invoiceItemsTemp
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('item', 'like', "%{$request['search_param']}%")
                ->orWhere('description', 'like', '%'.$request['search_param'].'%');
            }            
        })->select('id','service_id','expense_id','invoice_number','item','description','quantity','price','account','tax_rate','total_price')
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
        $invoiceItemsTemp = $this->invoiceItemsTemp::find($request['id']);

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

}
