<?php
namespace App\Models;

use App\Core\Database;

/** pages — static CMS pages (About, Privacy, Terms, Returns ...) */
class Page extends \App\Core\Model
{
    protected static string $table = 'pages';

    public static function publishedBySlug(string $slug): ?array
    {
        return Database::fetch(
            "SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1",
            [$slug]
        );
    }
}
