<?php
namespace App\Core;

/**
 * Upload — secure image upload helper.
 * Validates extension + MIME, enforces a size limit, randomizes the filename
 * and stores under /public/uploads/{subdir}. Returns a path relative to /public
 * (so media_url() can resolve it).
 */
class Upload
{
    private const MAX_BYTES = 3 * 1024 * 1024; // 3 MB
    private const EXT       = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const MIME      = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /**
     * Save a single file input (['name','tmp_name','error','size']).
     * Returns the stored path (relative to /public) or null if none was provided.
     * @throws \RuntimeException on validation failure
     */
    public static function image(?array $file, string $subdir): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (error code ' . $file['error'] . ').');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new \RuntimeException('Image is too large (max 3 MB).');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXT, true)) {
            throw new \RuntimeException('Unsupported image type. Use JPG, PNG, WEBP or GIF.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::MIME, true)) {
            throw new \RuntimeException('The file does not appear to be a valid image.');
        }

        $dir = UPLOAD_PATH . '/' . trim($subdir, '/');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Could not save the uploaded image.');
        }
        return 'uploads/' . trim($subdir, '/') . '/' . $name;
    }

    /**
     * Convert PHP's multiple-file input (name=[], tmp_name=[], ...) into a list
     * of normal single-file arrays, skipping empty slots.
     */
    public static function restructure(?array $file): array
    {
        $out = [];
        if (!$file || !isset($file['name'])) {
            return $out;
        }
        if (!is_array($file['name'])) {
            return [$file];
        }
        $count = count($file['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($file['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = [
                'name'     => $file['name'][$i],
                'type'     => $file['type'][$i] ?? '',
                'tmp_name' => $file['tmp_name'][$i],
                'error'    => $file['error'][$i],
                'size'     => $file['size'][$i],
            ];
        }
        return $out;
    }
}
