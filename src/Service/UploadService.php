<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadService
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    public function uploadFile(?UploadedFile $file, string $uploadDirectory): ?string
    {
        if (!$file) {
            return null;
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($uploadDirectory, $newFilename);

        return $newFilename;
    }

    public function removeFile(?string $filename, string $uploadDirectory): void
    {
        if (!$filename) {
            return;
        }

        $filePath = $uploadDirectory . '/' . $filename;

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
}
