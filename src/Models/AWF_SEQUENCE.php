<?php

namespace AWF\Extension\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AWF_SEQUENCE extends Model
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
    protected $table = 'AWF_SEQUENCE';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SEQUID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'SEPONR',
        'SEPSEQ',
        'SEARNU',
        'SEARDE',
        'SESIDE',
        'SEEXPI',
        'SEPILL',
        'SEINPR',
        'PRCODE',
        'ORCODE',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'SEINPR' => 'bool',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }
}
