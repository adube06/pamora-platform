<?php

namespace App\Domains\Communication\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuid;

    /**
     * The full set of notification types NotificationSubscriber fires —
     * single source of truth for both preference validation and the
     * Profile page's preference checkboxes.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'task_assigned' => 'Task Assigned',
        'task_completed' => 'Task Completed',
        'contribution_received' => 'Contribution Received',
        'member_joined' => 'Member Joined',
        'reminder_triggered' => 'Reminders',
        'quotation_submitted' => 'Quotation Submitted',
        'quotation_accepted' => 'Quotation Accepted',
        'quotation_rejected' => 'Quotation Rejected',
        'booking_confirmed' => 'Booking Confirmed',
        'booking_completed' => 'Booking Completed',
        'review_published' => 'Review Published',
    ];

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }

    protected $fillable = [
        'user_id',
        'occasion_id',
        'subject_type',
        'subject_id',
        'type',
        'title',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
