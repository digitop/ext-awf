<?php

namespace AWF\Extension\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AWF_SEQUENCE_LOG extends Model
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
    protected $table = 'AWF_SEQUENCE_LOG';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SELOID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'SEQUID',
        'WCSHNA',
        'LSTIME',
        'LETIME',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }
}
