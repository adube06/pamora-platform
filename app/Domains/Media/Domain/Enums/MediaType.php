<?php

namespace App\Domains\Media\Domain\Enums;

enum MediaType: string
{
    case Image = 'image';
    case Video = 'video';
    case Pdf = 'pdf';
    case Document = 'document';

    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => self::Image,
            str_starts_with($mimeType, 'video/') => self::Video,
            $mimeType === 'application/pdf' => self::Pdf,
            default => self::Document,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Video',
            self::Pdf => 'PDF',
            self::Document => 'Document',
        };
    }
}
