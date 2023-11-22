<?php

namespace EloquentSessionHandler;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

/**
 * @link https://github.com/repat/laravel-database-session-model
 *
 * @property string $id
 * @property int $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $payload
 * @property \Carbon\Carbon $last_activity
 * @property array $unserialized_payload
 */
class Session extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $keyType = 'string';

    /**
     * {@inheritDoc}
     */
    public $incrementing = false;

    /**
     * {@inheritDoc}
     */
    public $timestamps = false;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'user_id' => 'int',
        'last_activity' => 'datetime',
    ];

    /**
     * {@inheritDoc}
     */
    protected $appends = [
        // 'id',
        // 'user_id',
        // 'ip_address',
        // 'user_agent',
        // 'payload',
        // 'last_activity',
        'unserialized_payload',
    ];

    /**
     * Use parent constructor and
     * set the sessiontable according
     * to the laravel configuration
     *
     * {@inheritDoc}
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
        if (!is_array($payload = unserialize(base64_decode($this->payload)))) {
            return [];
        }

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
        if (!is_string($user = Config::get('session.models.user', '\App\Models\User'))) {
            throw new \RuntimeException('[session.models.user] should be a string');
        }

        return $this->belongsTo($user);
    }
}
