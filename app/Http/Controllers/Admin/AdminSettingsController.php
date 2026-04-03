<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\SystemSetting;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminSettingsController extends Controller
{
    public function index(SystemSettingsService $settingsService)
    {
        return Inertia::render('Admin/Settings', [
            'settings' => $settingsService->all(),
            'templates' => NotificationTemplate::query()->orderBy('channel')->orderBy('key')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings.exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'settings.reservation_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'settings.whatsapp_provider' => ['required', 'in:callmebot,twilio'],
            'settings.push_enabled' => ['nullable', 'boolean'],
            'templates' => ['nullable', 'array'],
            'templates.*.channel' => ['required', 'in:whatsapp,email,push'],
            'templates.*.key' => ['required', 'string', 'max:120'],
            'templates.*.title' => ['nullable', 'string', 'max:255'],
            'templates.*.body' => ['required', 'string'],
            'templates.*.is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (($validated['settings'] ?? []) as $key => $value) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => match ($key) {
                            'exchange_rate', 'reservation_minutes' => 'general',
                            'whatsapp_provider', 'push_enabled' => 'notifications',
                            default => 'general',
                        },
                        'value' => ['value' => $value],
                    ]
                );
            }

            foreach (($validated['templates'] ?? []) as $template) {
                NotificationTemplate::updateOrCreate(
                    [
                        'channel' => $template['channel'],
                        'key' => $template['key'],
                    ],
                    [
                        'title' => $template['title'] ?? null,
                        'body' => $template['body'],
                        'is_active' => (bool) ($template['is_active'] ?? true),
                    ]
                );
            }
        });

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
