<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbassyAttestationFileCosting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'embassy_attestation_file_costing';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['country_id','title','fee','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function countries()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'country_id' => 'required',
        'title' => 'required',
        'amount' => 'required'
    ];
    /**
     * The attributes that store validation errors.
     */
    protected $errors;
    /**
     * Validate method for model.
     */
    public function validate($data){
        // make a new validator object
        $validator = Validator::make($data,$this->rules);
        // check for failure
        if($validator->fails()){
            // set errors and return false
            $this->errors = $validator->errors();
            return false;
        }
        // validation pass
        return true;
    }
    
    // Returns Validation errors
    public function errors()
    {
        return $this->errors;
    }
}
