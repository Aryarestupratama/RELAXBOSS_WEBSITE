<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SeverityLevel;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Instrumen contoh DUMMY (RULE-008): slug berawalan `demo-`, bukan instrumen asli.
 * Isinya dibuat sendiri (bukan salinan instrumen mana pun) untuk menguji: tiga subskala,
 * satu butir terbalik, pengali skor 2, dan aturan skor dengan PFA. Hanya lokal.
 *
 * Asumsi pembalikan (A-3, divalidasi di TASK-030): nilai terbalik = (nilai maks + nilai min) - nilai.
 *
 * Rentang skor setelah pembalikan dan pengali 2.00 (opsi 0 sampai 3):
 *   Stres      5 butir: 0..30
 *   Kecemasan  4 butir: 0..24
 *   Kelelahan  3 butir: 0..18
 */
class DemoAssessmentSeeder extends Seeder
{
    private const SLUG = 'demo-stres-harian';

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoAssessmentSeeder dilewati: hanya untuk lingkungan local.');

            return;
        }

        DB::transaction(function (): void {
            $assessment = Assessment::query()->updateOrCreate(
                ['slug' => self::SLUG],
                [
                    'name' => '[DEMO] Cek Stres Harian',
                    'display_name' => 'Cek Stres Harian (Contoh)',
                    'description' => 'Instrumen contoh untuk pengembangan. Bukan instrumen asli dan hasilnya tidak bermakna.',
                    'instructions' => 'Pilih seberapa sering pernyataan berikut sesuai dengan keadaanmu dalam seminggu terakhir.',
                    'estimated_minutes' => 3,
                    'options' => [
                        ['value' => 0, 'label' => 'Tidak pernah'],
                        ['value' => 1, 'label' => 'Kadang-kadang'],
                        ['value' => 2, 'label' => 'Sering'],
                        ['value' => 3, 'label' => 'Hampir selalu'],
                    ],
                    'score_multiplier' => 2.00,
                    'source_reference' => 'DUMMY: dibuat untuk pengembangan, bukan instrumen asli (OQ-1).',
                    'creator_name' => null,
                    'creator_institution' => null,
                    'validator_name' => null,
                    'validator_credential' => null,
                    'validated_at' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );

            foreach ($this->questions() as $position => [$text, $subScale, $reversed]) {
                $assessment->questions()->updateOrCreate(
                    ['position' => $position],
                    ['text' => $text, 'sub_scale' => $subScale, 'is_reversed' => $reversed],
                );
            }

            foreach ($this->rules() as [$subScale, $min, $max, $label, $level, $pfaQuestion, $recommendation]) {
                $assessment->scoringRules()->updateOrCreate(
                    ['sub_scale' => $subScale, 'min_score' => $min],
                    [
                        'max_score' => $max,
                        'interpretation' => $label,
                        'severity_level' => $level,
                        'trigger_pfa' => $pfaQuestion !== null,
                        'pfa_question' => $pfaQuestion,
                        'static_recommendation' => $recommendation,
                    ],
                );
            }
        });
    }

    /**
     * @return array<int, array{string, string, bool}> posisi => [teks, subskala, terbalik]
     */
    private function questions(): array
    {
        return [
            1 => ['Aku merasa sulit untuk bersantai.', 'Stres', false],
            2 => ['Jantungku berdebar tanpa sebab yang jelas.', 'Kecemasan', false],
            3 => ['Aku merasa lelah walau sudah cukup tidur.', 'Kelelahan', false],
            4 => ['Aku mudah kesal oleh hal-hal kecil.', 'Stres', false],
            5 => ['Aku khawatir sesuatu yang buruk akan terjadi.', 'Kecemasan', false],
            6 => ['Aku sulit memulai kegiatan yang biasa kulakukan.', 'Kelelahan', false],
            7 => ['Aku merasa terbebani oleh tugas dan jadwal kuliah.', 'Stres', false],
            8 => ['Aku sulit berhenti memikirkan hal yang membuatku cemas.', 'Kecemasan', false],
            9 => ['Aku kehilangan semangat untuk kuliah.', 'Kelelahan', false],
            10 => ['Aku merasa mampu mengatasi tekanan yang datang.', 'Stres', true],
            11 => ['Tanganku terasa gemetar atau berkeringat saat gugup.', 'Kecemasan', false],
            12 => ['Aku merasa tegang sepanjang hari.', 'Stres', false],
        ];
    }

    /**
     * Rentang per subskala menutup seluruh kemungkinan skor tanpa tumpang tindih.
     *
     * @return list<array{string, int, int, string, SeverityLevel, ?string, string}>
     */
    private function rules(): array
    {
        $stresPfa = 'Hal apa yang paling membebanimu belakangan ini?';
        $cemasPfa = 'Kapan rasa cemas itu paling sering muncul?';

        return [
            ['Stres', 0, 9, 'Normal', SeverityLevel::Normal, null,
                'Tingkat stresmu tampak terkendali. Pertahankan rutinitas tidur, makan, dan istirahat yang teratur.'],
            ['Stres', 10, 15, 'Ringan', SeverityLevel::Mild, null,
                'Ada tanda stres ringan. Coba sisihkan waktu istirahat singkat di sela tugas dan ceritakan bebanmu kepada orang yang kamu percaya.'],
            ['Stres', 16, 22, 'Sedang', SeverityLevel::Moderate, null,
                'Stresmu cukup terasa. Pecah tugas menjadi bagian kecil, jaga jam tidur, dan pertimbangkan berbicara dengan konselor kampus.'],
            ['Stres', 23, 30, 'Tinggi', SeverityLevel::Severe, $stresPfa,
                'Stresmu tinggi dan perlu perhatian. Kamu tidak harus menghadapinya sendirian; bicarakan dengan psikolog atau tenaga profesional.'],

            ['Kecemasan', 0, 7, 'Normal', SeverityLevel::Normal, null,
                'Kecemasanmu tampak terkendali. Teruskan kebiasaan yang membuatmu tenang.'],
            ['Kecemasan', 8, 12, 'Ringan', SeverityLevel::Mild, null,
                'Ada tanda cemas ringan. Latihan napas perlahan dan jeda dari layar bisa membantu.'],
            ['Kecemasan', 13, 18, 'Sedang', SeverityLevel::Moderate, null,
                'Kecemasanmu cukup terasa. Tuliskan hal yang kamu khawatirkan dan pertimbangkan berbicara dengan tenaga profesional.'],
            ['Kecemasan', 19, 24, 'Tinggi', SeverityLevel::Severe, $cemasPfa,
                'Kecemasanmu tinggi dan perlu perhatian. Bicarakan dengan psikolog atau tenaga profesional.'],

            ['Kelelahan', 0, 5, 'Normal', SeverityLevel::Normal, null,
                'Tingkat energimu tampak baik. Jaga pola tidur dan aktivitas yang kamu sukai.'],
            ['Kelelahan', 6, 9, 'Ringan', SeverityLevel::Mild, null,
                'Ada tanda kelelahan ringan. Beri dirimu waktu istirahat yang cukup dan atur ulang jadwal bila perlu.'],
            ['Kelelahan', 10, 13, 'Sedang', SeverityLevel::Moderate, null,
                'Kelelahanmu cukup terasa. Periksa pola tidur dan beban kegiatanmu, dan ceritakan kepada orang terdekat.'],
            ['Kelelahan', 14, 18, 'Tinggi', SeverityLevel::Severe, null,
                'Kelelahanmu tinggi. Bila berlangsung lama, bicarakan dengan tenaga profesional.'],
        ];
    }
}
