<?php

namespace AWF\Extension\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AWF_SEQUENCE_WORKCENTER extends Model
{
    use LogsActivity;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'custom_mysql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'AWF_SEQUENCE_WORKCENTER';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SEWCID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'SEQUID',
        'WCSHNA',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }
}
