<?php

namespace EloquentSessionHandler;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

/**
 * @link https://github.com/repat/laravel-database-session-model
 *
 * @property string $id
 * @property string $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $payload
 * @property \Carbon\Carbon $last_activity
 * @property array $unserialized_payload
 */
class Session extends Model
{
    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should auto increment.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model has no timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * Attribute casting
     *
     * @var array
     */
    protected $casts = [
        'last_activity' => 'datetime',
    ];

    /**
     * Use parent constructor and
     * set the sessiontable according
     * to the laravel configuration
     */
    public function __construct()
    {
        parent::__construct();

        /** @var string */
        $table = Config::get('sessions.table', 'sessions');

        $this->table = $table;
    }


    public function getUnserializedPayloadAttribute(): array
    {
        /** @var array */
        $payload = unserialize(base64_decode($this->payload));

        return $payload;
    }

    /**
     * Manually set Payload (base64 encoded / serialized)
     *
     * @return void
     */
    public function setPayload(string $payload)
    {
        $this->payload = serialize(base64_encode($payload));
        $this->save();
    }

    /**
     * User Relationship
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('\App\Models\User');
    }
}
