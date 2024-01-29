<?php

namespace App\Models;

use App\Scopes\TimesheetScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    protected $fillable = ['date', 'user_id', 'vendor_id', 'project_id', 'hours', 'amount', 'paid_by', 'check_id', 'hourly', 'invoice', 'note', 'created_by_user_id', 'created_at', 'updated_at', 'deleted_at'];

    protected static function booted()
    {
        static::addGlobalScope(new TimesheetScope);
    }

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function check()
    {
        return $this->belongsTo(Check::class);
    }
}
