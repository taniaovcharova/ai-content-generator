# AI Content Generator — Claude Code Instructions

## Project Overview

Laravel 13 + Vue 3 + Inertia.js application for AI-powered content generation using the
**Laravel AI SDK** with **Anthropic Claude** as the primary provider.

**Developer:** Tatiana Ovcharova (Middle Full-Stack, 8 yrs Laravel/Vue experience)
**Goal:** Portfolio project to demonstrate Laravel + AI integration skills
**GitHub:** https://github.com/taniaovcharova/ai-content-generator

---

## Tech Stack

| Layer      | Technology                              |
|------------|-----------------------------------------|
| Backend    | Laravel 13, PHP 8.3                     |
| Frontend   | Vue 3, Inertia.js, JavaScript           |
| Styling    | Tailwind CSS                            |
| State      | Pinia                                   |
| AI         | Laravel AI SDK (`laravel/ai`) + Claude  |
| DB         | MySQL 8 (prod) / SQLite (local dev)     |
| Cache/Queue| Redis + Laravel Horizon                 |
| Vector DB  | Pinecone (Sprint 5)                     |
| Testing    | Pest, Laravel Dusk                      |
| CI/CD      | GitHub Actions + Docker                 |

---

## Essential Commands

```bash
# Development
php artisan serve              # Start Laravel dev server
npm run dev                    # Start Vite HMR
php artisan horizon            # Start queue worker dashboard

# Database
php artisan migrate            # Run migrations
php artisan migrate:fresh --seed  # Reset + seed

# Testing
php artisan test               # Run all tests
php artisan test --coverage    # With coverage report
php artisan dusk               # E2E tests

# Code quality
./vendor/bin/pint              # Laravel Pint (code style fixer)
php artisan ide-helper:generate  # IDE helpers

# Queue
php artisan queue:work redis   # Start queue worker
php artisan horizon            # Horizon dashboard at /horizon

# AI SDK
php artisan ai:list-agents     # List all registered agents
```

---

## Project Structure

```
app/
├── Ai/
│   ├── Agents/
│   │   ├── ContentGeneratorAgent.php   ← Main content generation agent
│   │   ├── BlogPostAgent.php
│   │   ├── SocialMediaAgent.php
│   │   └── SEOAgent.php
│   ├── Prompts/                        ← Prompt builder classes
│   └── Tools/                         ← Laravel AI SDK tools
├── Http/
│   ├── Controllers/
│   │   ├── ContentController.php
│   │   ├── GeneratorController.php
│   │   └── TemplateController.php
│   └── Requests/
├── Jobs/
│   ├── GenerateContentJob.php         ← Async generation
│   └── BatchGenerateContentJob.php
├── Models/
│   ├── User.php
│   ├── ContentItem.php
│   ├── PromptTemplate.php
│   └── ApiLog.php
├── Services/
│   ├── PineconeService.php            ← Sprint 5: Vector DB
│   └── ContentExportService.php       ← PDF/DOCX export
resources/
├── js/
│   ├── Pages/
│   │   ├── Generator/
│   │   │   ├── Index.vue              ← Main generator page
│   │   │   └── History.vue
│   │   ├── Templates/
│   │   │   └── Index.vue
│   │   └── Dashboard.vue
│   ├── Components/
│   │   ├── Generator/
│   │   │   ├── GeneratorForm.vue
│   │   │   ├── StreamingOutput.vue    ← Real-time streaming display
│   │   │   ├── TemplateSelector.vue
│   │   │   └── ContentCard.vue
│   │   └── UI/                       ← Reusable UI components
│   ├── Stores/
│   │   └── useGeneratorStore.js      ← Pinia store
│   └── Composables/
│       ├── useStreaming.js            ← SSE streaming composable
│       └── useContentExport.js
```

---

## Database Schema

```sql
-- Core tables
users               id, name, email, password, api_usage_limit, ...
content_items       id, user_id, title, content, template_id, tokens_used, is_public, share_uuid, ...
prompt_templates    id, name, category, system_prompt, user_prompt_template, variables (JSON), ...
api_logs            id, user_id, agent, model, tokens_used, cost_usd, duration_ms, ...
conversations       id, user_id, messages (JSON), created_at  -- for multi-turn
```

---

## Laravel AI SDK Usage

This project uses the **official Laravel AI SDK** (`laravel/ai`).

### Agent Pattern (preferred approach)

```php
<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
class ContentGeneratorAgent implements Agent
{
    use Promptable;

    public function __construct(
        private string $topic,
        private string $tone = 'professional',
        private string $length = 'medium'
    ) {}

    public function prompt(): string
    {
        return "Generate a {$this->length} {$this->tone} article about: {$this->topic}";
    }

    public function systemPrompt(): string
    {
        return "You are an expert content writer. Write engaging, well-structured content.";
    }
}
```

### Calling an Agent

```php
use App\Ai\Agents\ContentGeneratorAgent;
use Laravel\Ai\Facades\Ai;

// Simple generation
$result = Ai::agent(new ContentGeneratorAgent(
    topic: 'Laravel 13 new features',
    tone: 'casual',
    length: 'short'
))->generate();

// Streaming (for real-time UI)
return Ai::agent(new ContentGeneratorAgent(topic: $request->topic))
    ->stream(); // Returns SSE response
```

### Environment Variables (.env)

```dotenv
ANTHROPIC_API_KEY=sk-ant-...
AI_DEFAULT_PROVIDER=anthropic
AI_DEFAULT_MODEL=claude-sonnet-4-6

# Optional fallback
OPENAI_API_KEY=sk-...
```

---

## Coding Conventions

### PHP / Laravel

- **PHP 8.3 features:** Use enums, readonly properties, constructor promotion, named args
- **Strict types:** Always add `declare(strict_types=1)` at the top of every PHP file
- **Eloquent:** Always use Eloquent, never raw SQL except for complex reports
- **Services:** Business logic goes in `app/Services/`, never in controllers or models
- **Form Requests:** Always use Form Request classes for validation
- **Resources:** Use API Resources for JSON responses
- **Events/Listeners:** Use for side effects (logging, notifications) — not in controllers
- **Style:** Follow PSR-12, enforced by Laravel Pint

```php
// ✅ Good
declare(strict_types=1);

class GeneratorController extends Controller
{
    public function store(GenerateContentRequest $request): JsonResponse
    {
        $content = $this->generator->generate($request->validated());
        return response()->json(ContentResource::make($content));
    }
}

// ❌ Bad — no strict types, validation in controller, logic in controller
```

### Vue 3 / JavaScript

- **Composition API only** — no Options API
- **`<script setup>`** — always use setup syntax
- **Pinia** for all shared state — no Vuex, no prop drilling for shared state
- **Composables** for reusable logic (prefix with `use`)
- **Props:** Define with `defineProps()` using runtime declaration

```vue
<!-- ✅ Good -->
<script setup>
const props = defineProps({
  templateId: Number,
  tone: {
    type: String,
    default: 'casual',
    validator: (v) => ['formal', 'casual', 'creative'].includes(v)
  }
})
const emit = defineEmits(['generated'])
</script>

<!-- ❌ Bad — Options API, no Composition API -->
```

### Naming Conventions

| Type              | Convention          | Example                     |
|-------------------|---------------------|-----------------------------|
| PHP Classes       | PascalCase          | `ContentGeneratorAgent`     |
| PHP Methods       | camelCase           | `generateContent()`         |
| DB Tables         | snake_case plural   | `content_items`             |
| DB Columns        | snake_case          | `tokens_used`               |
| Vue Components    | PascalCase          | `GeneratorForm.vue`         |
| Vue Composables   | camelCase + use     | `useStreaming.js`            |
| Pinia Stores      | camelCase + use     | `useGeneratorStore.js`      |
| Routes            | kebab-case          | `/content-generator`        |
| Env Variables     | UPPER_SNAKE_CASE    | `ANTHROPIC_API_KEY`         |

---

## Key Business Rules

1. **Rate limiting:** Free plan = 20 generations/day per user (resets at 00:00 UTC)
2. **Streaming:** All content generation uses SSE streaming for better UX
3. **Logging:** Every AI API call must be logged to `api_logs` with tokens + cost
4. **Caching:** Cache identical prompts for 1 hour via Redis to save API costs
5. **Async:** Generations > 1000 tokens go to queue automatically
6. **Privacy:** Content is private by default; sharing requires explicit UUID link

---

## Sprint Plan (3 Months)

| Sprint | Weeks  | Focus                              | Key Deliverables                         |
|--------|--------|------------------------------------|------------------------------------------|
| **1**  | 1–2    | Setup + Claude API                 | Laravel 13 + Vue 3, ClaudeAgent, streaming |
| **2**  | 3–4    | Auth + Prompt Engineering          | Auth, 6 templates, multi-turn, Redis cache |
| **3**  | 5–6    | Full UI + Real-time                | Generator UI, Pinia, SSE streaming, history |
| **4**  | 7–8    | Export + History                   | PDF/DOCX, sharing, dashboard             |
| **5**  | 9–10   | RAG + Vector DB                    | Pinecone, embeddings, semantic search    |
| **6**  | 11–12  | Tests + Deployment                 | Docker, CI/CD, 80% coverage, production  |

**Current Sprint:** Sprint 1 — Started 23 March 2026

---

## Testing Strategy

```bash
# Unit tests — pure PHP logic
tests/Unit/Ai/ContentGeneratorAgentTest.php
tests/Unit/Services/PineconeServiceTest.php

# Feature tests — HTTP endpoints
tests/Feature/GeneratorControllerTest.php
tests/Feature/TemplateControllerTest.php

# E2E tests — full browser flows (Dusk)
tests/Browser/ContentGenerationTest.php
```

**Rules:**
- Every new Service class must have a Unit test
- Every new Controller must have a Feature test
- Critical user flows must have a Dusk E2E test
- Run `php artisan test` before every commit

---

## Common Gotchas

- **Streaming responses:** Laravel's `stream()` disables output buffering — test locally with `php artisan serve`, not with `valet` or `herd` which may buffer
- **Inertia + SSE:** Use `axios` with `responseType: 'stream'` for streaming, not `EventSource` (Inertia may intercept)
- **Pinecone dimensions:** Must match embedding model output — `claude` embeddings = 1536 dimensions
- **Queue + AI:** Always set `timeout` on AI jobs — Claude can take up to 60s for long content
- **Cost tracking:** `claude-sonnet-4-6` costs ~$3/M input tokens — log everything in `api_logs`

---

## Useful Resources

- [Laravel 13 Docs](https://laravel.com/docs/13.x)
- [Laravel AI SDK Docs](https://laravel.com/docs/13.x/ai-sdk)
- [Anthropic Claude API](https://docs.anthropic.com)
- [Inertia.js Docs](https://inertiajs.com)
- [Pinia Docs](https://pinia.vuejs.org)
- [Pinecone Docs](https://docs.pinecone.io)
- [Tailwind CSS](https://tailwindcss.com/docs)
