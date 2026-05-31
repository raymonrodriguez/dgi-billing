<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(static function ($model): void {
            $initialData = collect($model->getAttributes())
                ->except(['id', 'updated_at', 'created_at', 'deleted_at', 'company_id', 'user_id'])
                ->toArray();

            $model->logActivity('create', $initialData);
        });

        static::updated(static function ($model): void {
            $changes = [];
            foreach ($model->getChanges() as $field => $newValue) {
                if (in_array($field, ['updated_at', 'created_at'])) {
                    continue;
                }

                $oldValue = $model->getOriginal($field);
                if ($oldValue !== $newValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
            if (! empty($changes)) {
                $model->logActivity('update', $changes);
            }
        });

        static::deleted(static function ($model): void {
            $model->logActivity('delete');
        });
    }

    public function logActivity(string $action, array $changes = []): void
    {
        Log::create([
            'model' => class_basename(static::class),
            'record_id' => (string) $this->getKey(),
            'user_id' => (string) (Auth::id() ?? 'system'),
            'module' => static::class,
            'action' => $action,
            'changes' => ! empty($changes) ? $changes : null,
        ]);
    }

    public function activityLogs()
    {
        return $this->hasMany(Log::class, 'record_id', $this->getKeyName())
            ->where('model', class_basename(static::class))
            ->orderByDesc('created_at');
    }
}
