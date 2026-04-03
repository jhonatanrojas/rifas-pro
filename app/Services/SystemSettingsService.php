<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemSettingsService
{
    public function all(): array
    {
        return SystemSetting::query()
            ->get()
            ->mapWithKeys(function (SystemSetting $setting) {
                return [$setting->key => $setting->value['value'] ?? null];
            })
            ->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = SystemSetting::query()->where('key', $key)->first();

        return $setting?->value['value'] ?? $default;
    }

    public function set(string $key, mixed $value, ?string $group = null): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => ['value' => $value],
            ]
        );
    }

    public function setMany(array $data): void
    {
        foreach ($data as $key => $payload) {
            $this->set($key, $payload['value'] ?? null, $payload['group'] ?? null);
        }
    }
}
