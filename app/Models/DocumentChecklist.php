<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentChecklist extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_checklist';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sector_id','document_title','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function sectors()
    {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'sector_id' => 'required',
        'document_title' => 'required'
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
