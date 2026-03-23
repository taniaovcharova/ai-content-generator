<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[Timeout(60)]
class ContentGeneratorAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<INSTRUCTIONS
        You are an expert content writer specialising in creating engaging, well-structured,
        SEO-friendly content. Your writing adapts to the requested tone and length while
        maintaining clarity and value for the reader.

        Guidelines:
        - Structure content with clear headings and logical flow
        - Match the tone precisely (professional, casual, or creative)
        - Optimise for readability: short paragraphs, active voice
        - Include a compelling introduction and a clear conclusion
        - Never fabricate facts or statistics
        INSTRUCTIONS;
    }
}
