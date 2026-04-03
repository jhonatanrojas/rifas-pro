<?php

namespace App\Services;

use App\Models\NotificationTemplate;

class NotificationTemplateService
{
    public function render(string $channel, string $key, array $context = []): array
    {
        $template = NotificationTemplate::query()
            ->where('channel', $channel)
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        $title = $template?->title ?? match ($key) {
            'payment_approved' => 'Pago aprobado',
            'payment_rejected' => 'Pago rechazado',
            'winner' => 'Ganaste la rifa',
            default => ucfirst(str_replace('_', ' ', $key)),
        };

        $body = $template?->body ?? $this->defaultBody($key);

        return [
            'title' => $this->replaceTokens($title, $context),
            'body' => $this->replaceTokens($body, $context),
        ];
    }

    protected function defaultBody(string $key): string
    {
        return match ($key) {
            'payment_approved' => 'Tu pago para {rifa} fue aprobado.',
            'payment_rejected' => 'Tu pago para {rifa} fue rechazado.',
            'winner' => 'Tu ticket {numeros} ganó en {rifa}.',
            default => '{rifa}',
        };
    }

    public function replaceTokens(string $text, array $context = []): string
    {
        $tokens = [
            '{nombre}' => $context['nombre'] ?? $context['name'] ?? '',
            '{rifa}' => $context['rifa'] ?? $context['raffle'] ?? '',
            '{numeros}' => $context['numeros'] ?? $context['numbers'] ?? '',
            '{total}' => $context['total'] ?? '',
            '{premio}' => $context['premio'] ?? $context['prize'] ?? '',
            '{motivo}' => $context['motivo'] ?? $context['reason'] ?? '',
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $text);
    }
}
