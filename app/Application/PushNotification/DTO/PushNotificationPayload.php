<?php

declare(strict_types=1);

namespace App\Application\PushNotification\DTO;

final readonly class PushNotificationPayload
{
    public function __construct(
        public string $title,
        public string $body,
        public string $icon,
        public string $badge,
        public string $url,
        public string $tag,
    ) {
    }

    /**
     * @return array{title: string, body: string, icon: string, badge: string, url: string, tag: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'url' => $this->url,
            'tag' => $this->tag,
        ];
    }
}
