<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

/** API-023: ubah artikel. Slug tidak ikut diubah (URL Publik dan SEO tetap). */
final class UpdateArticleRequest extends ArticleFormRequest {}
