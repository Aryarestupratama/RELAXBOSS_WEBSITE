<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\AiFeature;
use App\Enums\CrisisLayer;
use App\Enums\UserRole;
use App\Models\AiUsageDaily;
use App\Models\AssessmentAttempt;
use App\Models\Conversation;
use App\Models\CrisisEvent;
use App\Models\MoodEntry;
use App\Models\User;
use App\Support\Aggregate;
use App\Support\Wib;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Statistik agregat untuk Admin Dashboard (FR-024, FR-025, SCR-019).
 *
 * Hanya hitungan: tanpa nama, email, ID, isi, atau catatan siapa pun. Setiap angka yang berasal dari
 * kelompok pengguna 1 sampai 4 disamarkan lewat `Aggregate` (RULE-042). Akun admin tidak dihitung.
 * Pemakaian AI (`ai_usage_daily`) tidak berisi identitas sehingga tampil apa adanya.
 *
 * KPI dihitung di PHP dari kohort kecil agar sama di SQLite dan MySQL (tanpa fungsi tanggal khusus basis data).
 */
final class AdminStatsService
{
    private const AI_FEATURE_LABELS = [
        'chat' => 'RelaxMate',
        'assessment' => 'Rekomendasi Asesmen',
    ];

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        return [
            'users' => $this->users(),
            'usage' => $this->usage(),
            'crisis' => $this->crisis(),
            'kpi' => $this->kpi(),
            'ai' => $this->aiUsage(),
        ];
    }

    /** @return Builder<User> */
    private function members(): Builder
    {
        return User::query()->where('role', UserRole::User->value);
    }

    /**
     * @return array{registered: string, verified: string, empty: bool}
     */
    private function users(): array
    {
        $registered = $this->members()->count();
        $verified = $this->members()->whereNotNull('email_verified_at')->count();

        return [
            'registered' => Aggregate::count($registered),
            'verified' => Aggregate::count($verified),
            'empty' => $registered === 0,
        ];
    }

    /**
     * @return array{assessments_total: string, assessments_by_type: list<array{name: string, total: string}>, mood_entries: string, conversations: string}
     */
    private function usage(): array
    {
        $members = fn (): Builder => $this->members()->select('id');

        $byType = AssessmentAttempt::query()
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->whereIn('assessment_attempts.user_id', $members())
            ->groupBy('assessments.id', 'assessments.name')
            ->selectRaw('assessments.name as name, count(*) as total')
            ->orderByDesc('total')
            ->orderBy('name')
            ->toBase()
            ->get();

        return [
            'assessments_total' => Aggregate::count((int) $byType->sum('total')),
            'assessments_by_type' => $byType
                ->map(fn (object $row): array => [
                    'name' => (string) $row->name,
                    'total' => Aggregate::count((int) $row->total),
                ])
                ->values()
                ->all(),
            'mood_entries' => Aggregate::count(MoodEntry::query()->whereIn('user_id', $members())->count()),
            // Hanya Percakapan yang sudah berisi pesan (Percakapan kosong dipakai ulang, TASK-020).
            'conversations' => Aggregate::count(
                Conversation::query()->whereIn('user_id', $members())->whereHas('messages')->count(),
            ),
        ];
    }

    /**
     * @return array{total: string, last_30_days: string, rule: string, pfa_rule: string}
     */
    private function crisis(): array
    {
        $since = now()->utc()->subDays(30);

        return [
            'total' => Aggregate::count(CrisisEvent::query()->count()),
            'last_30_days' => Aggregate::count(CrisisEvent::query()->where('created_at', '>=', $since)->count()),
            'rule' => Aggregate::count(CrisisEvent::query()->where('layer', CrisisLayer::Rule->value)->count()),
            'pfa_rule' => Aggregate::count(CrisisEvent::query()->where('layer', CrisisLayer::PfaRule->value)->count()),
        ];
    }

    /**
     * KPI M-1 sampai M-3 (PRD bagian 3). Definisi kohort dicatat di Architecture.
     *
     * @return list<array{code: string, title: string, description: string, target: string, value: ?string, cohort: string}>
     */
    private function kpi(): array
    {
        return [
            $this->activation(),
            $this->retention(),
            $this->chatAdoption(),
        ];
    }

    /**
     * M-1: dari akun terverifikasi yang sudah berumur 7 hari, berapa yang menyelesaikan Asesmen dalam 7 hari sejak daftar.
     *
     * @return array{code: string, title: string, description: string, target: string, value: ?string, cohort: string}
     */
    private function activation(): array
    {
        $cohort = fn (): Builder => $this->members()
            ->whereNotNull('email_verified_at')
            ->where('created_at', '<=', now()->utc()->subDays(7));

        $registeredAt = $cohort()->toBase()->get(['id', 'created_at'])->pluck('created_at', 'id');

        $firstAttempt = AssessmentAttempt::query()
            ->whereIn('user_id', $cohort()->select('id'))
            ->groupBy('user_id')
            ->selectRaw('user_id, min(completed_at) as first_at')
            ->toBase()
            ->get()
            ->pluck('first_at', 'user_id');

        $done = 0;
        foreach ($firstAttempt as $userId => $firstAt) {
            $joined = $registeredAt->get($userId);

            if ($joined !== null && $this->utc($firstAt) <= $this->utc($joined)->addDays(7)) {
                $done++;
            }
        }

        return $this->kpiRow(
            'M-1',
            'Aktivasi',
            'Akun terverifikasi (berumur minimal 7 hari) yang menyelesaikan Asesmen dalam 7 hari sejak mendaftar.',
            '≥ 40%',
            $done,
            $registeredAt->count(),
        );
    }

    /**
     * M-2: dari pengguna yang menyelesaikan Asesmen pertamanya minimal 14 hari lalu, berapa yang mencatat mood
     * di minimal 3 hari WIB berbeda dalam 14 hari sejak Asesmen pertama itu.
     *
     * @return array{code: string, title: string, description: string, target: string, value: ?string, cohort: string}
     */
    private function retention(): array
    {
        $cutoff = now()->utc()->subDays(14);

        /** @var Collection<int|string, string> $firstAttempt */
        $firstAttempt = AssessmentAttempt::query()
            ->whereIn('user_id', $this->members()->select('id'))
            ->groupBy('user_id')
            ->selectRaw('user_id, min(completed_at) as first_at')
            ->toBase()
            ->get()
            ->pluck('first_at', 'user_id')
            ->filter(fn ($firstAt): bool => $this->utc($firstAt) <= $cutoff);

        /** @var array<int|string, array<string, true>> $moodDays */
        $moodDays = [];

        foreach ($firstAttempt->keys()->chunk(500) as $ids) {
            $rows = MoodEntry::query()
                ->whereIn('user_id', $ids->all())
                ->select('user_id', 'entry_date')
                ->distinct()
                ->toBase()
                ->get();

            foreach ($rows as $row) {
                $moodDays[$row->user_id][substr((string) $row->entry_date, 0, 10)] = true;
            }
        }

        $done = 0;
        foreach ($firstAttempt as $userId => $firstAt) {
            $start = Wib::startOfDay($this->utc($firstAt));
            $from = $start->format('Y-m-d');
            $until = $start->addDays(14)->format('Y-m-d');

            $days = collect(array_keys($moodDays[$userId] ?? []))
                ->filter(fn (string $day): bool => $day >= $from && $day < $until)
                ->count();

            if ($days >= 3) {
                $done++;
            }
        }

        return $this->kpiRow(
            'M-2',
            'Retensi',
            'Pengguna yang selesai Asesmen (pertama kali minimal 14 hari lalu) dan mencatat mood di minimal 3 hari berbeda dalam 14 hari sejak itu.',
            '≥ 25%',
            $done,
            $firstAttempt->count(),
        );
    }

    /**
     * M-3: dari akun terverifikasi dan aktif yang berumur 14 hari, berapa yang memulai Percakapan (berisi pesan) dalam 14 hari sejak mendaftar.
     *
     * @return array{code: string, title: string, description: string, target: string, value: ?string, cohort: string}
     */
    private function chatAdoption(): array
    {
        $cohort = fn (): Builder => $this->members()
            ->whereNotNull('email_verified_at')
            ->where('is_active', true)
            ->where('created_at', '<=', now()->utc()->subDays(14));

        $registeredAt = $cohort()->toBase()->get(['id', 'created_at'])->pluck('created_at', 'id');

        $firstChat = Conversation::query()
            ->whereIn('user_id', $cohort()->select('id'))
            ->whereHas('messages')
            ->groupBy('user_id')
            ->selectRaw('user_id, min(created_at) as first_at')
            ->toBase()
            ->get()
            ->pluck('first_at', 'user_id');

        $done = 0;
        foreach ($firstChat as $userId => $firstAt) {
            $joined = $registeredAt->get($userId);

            if ($joined !== null && $this->utc($firstAt) <= $this->utc($joined)->addDays(14)) {
                $done++;
            }
        }

        return $this->kpiRow(
            'M-3',
            'Adopsi RelaxMate',
            'Akun terverifikasi dan aktif (berumur minimal 14 hari) yang memulai Percakapan dalam 14 hari sejak mendaftar.',
            '≥ 30%',
            $done,
            $registeredAt->count(),
        );
    }

    /**
     * @return array{code: string, title: string, description: string, target: string, value: ?string, cohort: string}
     */
    private function kpiRow(string $code, string $title, string $description, string $target, int $done, int $cohort): array
    {
        return [
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'target' => $target,
            'value' => Aggregate::percent($done, $cohort),
            'cohort' => Aggregate::count($cohort),
        ];
    }

    /**
     * Pemakaian AI per fitur: hari ini (UTC) dan 30 hari terakhir. Tanpa identitas, jadi tidak disamarkan.
     *
     * @return array{today: list<array<string, int|string>>, last_30_days: list<array<string, int|string>>}
     */
    private function aiUsage(): array
    {
        $today = now()->utc()->toDateString();
        $from = now()->utc()->subDays(29)->toDateString();

        return [
            'today' => $this->aiRows(fn (Builder $query): Builder => $query->whereDate('usage_date', $today)),
            'last_30_days' => $this->aiRows(fn (Builder $query): Builder => $query->whereDate('usage_date', '>=', $from)),
        ];
    }

    /**
     * @param  callable(Builder<AiUsageDaily>): Builder<AiUsageDaily>  $scope
     * @return list<array<string, int|string>>
     */
    private function aiRows(callable $scope): array
    {
        $rows = $scope(AiUsageDaily::query())
            ->groupBy('feature')
            ->selectRaw('feature, sum(requests) as requests, sum(rate_limited) as rate_limited, sum(errors) as errors, sum(prompt_tokens) as prompt_tokens, sum(completion_tokens) as completion_tokens')
            ->toBase()
            ->get()
            ->keyBy('feature');

        return collect(AiFeature::cases())
            ->map(function (AiFeature $feature) use ($rows): array {
                $row = $rows->get($feature->value);

                return [
                    'feature' => self::AI_FEATURE_LABELS[$feature->value] ?? $feature->value,
                    'requests' => (int) ($row->requests ?? 0),
                    'rate_limited' => (int) ($row->rate_limited ?? 0),
                    'errors' => (int) ($row->errors ?? 0),
                    'tokens' => (int) ($row->prompt_tokens ?? 0) + (int) ($row->completion_tokens ?? 0),
                ];
            })
            ->all();
    }

    private function utc(mixed $value): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $value, 'UTC');
    }
}
