<?php

namespace App\Models\Concerns;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Attach to any admin-managed model (Product, Category, Brand, Enquiry,
 * Quote, Solution, Blog, Page, Banner, ...) to automatically write
 * created/updated/deleted rows to admin_activity_logs, matching section 50
 * of the plan ("Admin John changed: HPE DL380 Gen11 Price from X -> Y").
 *
 * Usage:
 *   class Product extends Model
 *   {
 *       use LogsActivity;
 *   }
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->writeActivityLog('created', $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (! empty($changes)) {
                $model->writeActivityLog('updated', $changes);
            }
        });

        static::deleted(function ($model) {
            $model->writeActivityLog('deleted', []);
        });
    }

    protected function writeActivityLog(string $action, array $changes): void
    {
        if (! Auth::check()) {
            return;
        }

        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => class_basename($this),
            'record_id' => $this->getKey(),
            'changes' => $changes ?: null,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
