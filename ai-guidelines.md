# AI Agent Guidelines — ai-content-generator

> This file provides additional context for AI coding agents (Claude Code, Cursor, Copilot).
> Place this file in the project root alongside CLAUDE.md.

---

## What We Are Building

A **content generation SaaS** where users:
1. Choose a content template (Blog, Social Media, Email, SEO, etc.)
2. Fill in variables (topic, tone, length)
3. Get AI-generated content via Claude (streamed in real-time)
4. Save, edit, export (PDF/DOCX), and share their content

The "secret sauce" is **Sprint 5**: RAG (Retrieval-Augmented Generation) using Pinecone —
the app learns from each user's past content to generate more personalized results over time.

---

## How to Implement New Features

### Adding a New AI Agent

1. Create `app/Ai/Agents/YourAgent.php`
2. Add `#[Provider(Lab::Anthropic)]` and `#[Model('claude-sonnet-4-6')]` attributes
3. Implement `Agent` contract with `prompt()` and `systemPrompt()` methods
4. Register in `app/Providers/AiServiceProvider.php` if needed
5. Write unit test in `tests/Unit/Ai/YourAgentTest.php`

### Adding a New Content Template

1. Add row to `database/seeders/PromptTemplateSeeder.php`
2. Template variables use `{{variable_name}}` syntax
3. Add corresponding Agent class if logic is complex
4. Add to `TemplateSelector.vue` categories

### Adding a New Page

1. Create controller in `app/Http/Controllers/`
2. Create Inertia Vue page in `resources/js/Pages/`
3. Add route in `routes/web.php`
4. Add to navigation in `resources/js/Layouts/AppLayout.vue`
5. Write Feature test

---

## Sprint 1 — Specific Tasks (Current)

These are the exact files to create in Sprint 1:

```
# Backend
app/Ai/Agents/ContentGeneratorAgent.php
app/Http/Controllers/GeneratorController.php
app/Http/Requests/GenerateContentRequest.php
app/Models/ContentItem.php
app/Models/ApiLog.php
app/Models/PromptTemplate.php
database/migrations/XXXX_create_content_items_table.php
database/migrations/XXXX_create_api_logs_table.php
database/migrations/XXXX_create_prompt_templates_table.php

# Frontend
resources/js/Pages/Generator/Index.vue
resources/js/Components/Generator/GeneratorForm.vue
resources/js/Components/Generator/StreamingOutput.vue
resources/js/Stores/useGeneratorStore.js
resources/js/Composables/useStreaming.js
```

**Sprint 1 Definition of Done:**
- [ ] `composer create-project laravel/laravel ai-content-generator` done
- [ ] Vue 3 + Inertia.js installed via Breeze
- [ ] Laravel AI SDK installed and configured
- [ ] `ContentGeneratorAgent` calls Claude and returns text
- [ ] Basic `GeneratorForm` Vue component works
- [ ] Streaming output visible in browser
- [ ] All migrations created and run successfully
- [ ] At least 3 unit tests passing

---

## Preferred Implementation Patterns

### ✅ Streaming Response (Controller)

```php
// GeneratorController.php
public function stream(GenerateContentRequest $request): StreamedResponse
{
    return Ai::agent(new ContentGeneratorAgent(
        topic: $request->topic,
        tone: $request->tone,
        length: $request->length,
    ))->stream();
}
```

### ✅ Streaming Consumer (Vue Composable)

```javascript
// useStreaming.js
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useStreaming() {
    const streamingText = ref('')
    const isStreaming = ref(false)

    async function startStream(payload) {
        isStreaming.value = true
        streamingText.value = ''

        const response = await fetch('/api/generator/stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf_token
            },
            body: JSON.stringify(payload),
        })

        const reader = response.body.getReader()
        const decoder = new TextDecoder()

        while (true) {
            const { done, value } = await reader.read()
            if (done) break
            streamingText.value += decoder.decode(value)
        }

        isStreaming.value = false
    }

    return { streamingText, isStreaming, startStream }
}
```

### ✅ Logging AI Usage (Service Pattern)

```php
// Always log after every AI call
ApiLog::create([
    'user_id'     => auth()->id(),
    'agent'       => ContentGeneratorAgent::class,
    'model'       => 'claude-sonnet-4-6',
    'tokens_used' => $result->usage->totalTokens,
    'cost_usd'    => $result->usage->totalTokens * 0.000003,
    'duration_ms' => $durationMs,
]);
```

---

## What NOT to Do

- ❌ Do NOT put business logic in controllers — use Services or Agents
- ❌ Do NOT use `DB::raw()` for normal queries — use Eloquent
- ❌ Do NOT hardcode API keys — always use `.env`
- ❌ Do NOT use Options API in Vue — always Composition API + `<script setup>` (без TypeScript)
- ❌ Do NOT skip writing tests — every new class needs a test
- ❌ Do NOT commit `.env` file
- ❌ Do NOT use `var_dump()` or `dd()` in production code
- ❌ Do NOT make synchronous AI calls for requests that could take > 5s — use Queue

---

## Environment Setup (macOS)

```bash
# Prerequisites
brew install php@8.3 mysql redis node
brew services start mysql
brew services start redis

# Project setup
composer create-project laravel/laravel ai-content-generator
cd ai-content-generator
composer require laravel/breeze --dev
php artisan breeze:install vue
composer require laravel/ai
npm install && npm run dev

# .env
cp .env.example .env
php artisan key:generate
# Set DB_DATABASE, ANTHROPIC_API_KEY

php artisan migrate --seed
php artisan serve
```

---

## API Endpoints (Reference)

```
POST   /generator/stream          → Stream content generation (SSE)
POST   /generator/store           → Save generated content
GET    /generator/history         → List user's content history
GET    /generator/{id}            → Show single content item
PUT    /generator/{id}            → Update content item
DELETE /generator/{id}            → Delete content item
GET    /generator/{uuid}/share    → Public share view (no auth)

GET    /templates                 → List all templates
GET    /templates/{id}            → Get template details

GET    /dashboard                 → User stats dashboard
GET    /api/usage                 → API usage stats (tokens, cost)
```

---

## Estimated Costs (Anthropic Claude)

| Model              | Input (per 1M tokens) | Output (per 1M tokens) |
|--------------------|-----------------------|------------------------|
| claude-sonnet-4-6  | $3.00                 | $15.00                 |
| claude-haiku-4-5   | $0.25                 | $1.25                  |

**Recommendation:** Use `claude-haiku-4-5` for templates/short content,
`claude-sonnet-4-6` for long-form articles and complex generation.

---

## Git Workflow

```bash
# Branch naming
feature/sprint1-claude-agent
feature/sprint2-auth
bugfix/streaming-timeout
chore/update-dependencies

# Commit message format (Conventional Commits)
feat: add ContentGeneratorAgent with streaming support
fix: handle Claude API timeout in streaming response
test: add unit tests for ContentGeneratorAgent
chore: install Laravel AI SDK
```
