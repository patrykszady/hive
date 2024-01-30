<?php

namespace App\Models;

use App\Scopes\HourScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['date', 'hours', 'project_id', 'user_id', 'vendor_id', 'timesheet_id', 'created_by_user_id', 'note', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new HourScope);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class);
    }
}
