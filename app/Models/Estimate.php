<?php

namespace App\Models;

use App\Models\Scopes\EstimateScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_id', 'options', 'belongs_to_vendor_id', 'created_at', 'updated_at'];

    protected $casts = [
        'options' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new EstimateScope);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function line_items()
    {
        return $this->belongsToMany(LineItem::class)->withPivot('id', 'name', 'category', 'sub_category', 'unit_type', 'cost', 'desc', 'notes', 'quantity', 'total', 'section_id')->withTimestamps();
    }

    public function estimate_line_items()
    {
        return $this->hasMany(EstimateLineItem::class);
    }

    public function estimate_sections()
    {
        return $this->hasMany(EstimateSection::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'belongs_to_vendor_id');
    }

    // public function getSectionsAttribute($value)
    // {
    //     // dd($value);
    //     //where not removed
    //     $sections = collect(json_decode($value, true));
    //     return $sections->where('deleted', '!=', true);
    //     // dd($sections->where('deleted', '!=', true));
    //     // foreach($sections as $section){
    //     //     if(isset($section['deleted'])){
    //     //         continue;
    //     //     }else{
    //     //         // $sections
    //     //     }
    //     // }
    //     // return json_decode($value, true);
    // }

    public function getReimbursmentsAttribute()
    {
        if(isset($this->options['include_reimbursement']) && $this->options['include_reimbursement'] == TRUE){
            return $this->project->finances['reimbursments'];
        }else{
            return NULL;
        }
    }

    public function getPaymentsAttribute()
    {
        if(isset($this->options['payments'])){
            return $this->options['payments'];
        }else{
            return NULL;
        }
    }

    public function getNumberAttribute()
    {
        $number =
            $this->project->belongs_to_vendor_id . '-' .
            $this->project->client_id . '-' .
            $this->project->id . '-' .
            $this->id;

        return $number;
    }
}
