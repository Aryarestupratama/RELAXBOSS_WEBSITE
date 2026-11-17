<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\Intent;

/**
 * Bentuk keluaran balasan chat: daftar kode dan json_schema untuk Groq Structured Outputs.
 * Daftar ini juga disisipkan ke prompt (`{{STRATEGY_CODES}}`, `{{FLOW_ACTIONS}}`), jadi satu sumber kebenaran.
 */
final class ChatReplySchema
{
    public const STRATEGIES = [
        'validation', 'affirmation', 'cognitive_reframing', 'socratic_questioning', 'motivational_interviewing',
        'active_listening', 'crisis_intervention', 'grounding_technique', 'psychoeducation', 'hold_space',
        'gentle_reframe', 'grow_coaching', 'explore', 'small_steps', 'presence', 'encourage',
    ];

    public const FLOW_ACTIONS = [
        'continue', 'ask_situation', 'suggest_action', 'provide_crisis_resources', 'recommend_professional',
        'end_topic', 'redirect_to_mental_health', 'redirect_to_feature',
    ];

    /**
     * Nilai `strict` diisi klien sesuai model. Null bila dimatikan lewat `AI_CHAT_STRUCTURED_OUTPUT=false`.
     *
     * @return array<string, mixed>|null
     */
    public function responseFormat(): ?array
    {
        if (! (bool) config('relaxboss.ai.features.chat.structured_output', true)) {
            return null;
        }

        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'relaxmate_reply',
                'strict' => false,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'reply' => ['type' => 'string'],
                        'intent' => ['type' => 'string', 'enum' => Intent::codes()],
                        'confidence' => ['type' => 'number'],
                        'strategy' => ['type' => 'string', 'enum' => self::STRATEGIES],
                        'flow_action' => ['type' => 'string', 'enum' => self::FLOW_ACTIONS],
                    ],
                    'required' => ['reply', 'intent', 'confidence', 'strategy', 'flow_action'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
