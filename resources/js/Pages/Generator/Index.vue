<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import { marked } from 'marked'
import { computed, ref } from 'vue'

const topic = ref('')
const tone = ref('professional')
const length = ref('medium')
const content = ref('')
const loading = ref(false)
const error = ref('')

const renderedContent = computed(() => marked.parse(content.value))

async function generate() {
    loading.value = true
    error.value = ''
    content.value = ''

    try {
        const { data } = await axios.post('/generator', {
            topic: topic.value,
            tone: tone.value,
            length: length.value,
        })
        content.value = data.content
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Something went wrong. Please try again.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <Head title="Content Generator" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Content Generator
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

                <!-- Form -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="generate" class="space-y-5">

                            <!-- Topic -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Topic
                                </label>
                                <input
                                    v-model="topic"
                                    type="text"
                                    required
                                    placeholder="e.g. Laravel 13 new features"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <!-- Tone + Length -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Tone
                                    </label>
                                    <select
                                        v-model="tone"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="professional">Professional</option>
                                        <option value="casual">Casual</option>
                                        <option value="creative">Creative</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Length
                                    </label>
                                    <select
                                        v-model="length"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="short">Short</option>
                                        <option value="medium">Medium</option>
                                        <option value="long">Long</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button
                                type="submit"
                                :disabled="loading"
                                class="flex w-full items-center justify-center gap-2 rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
                            >
                                <!-- Spinner -->
                                <svg
                                    v-if="loading"
                                    class="h-4 w-4 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                {{ loading ? 'Generating...' : 'Generate' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="error" class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ error }}
                </div>

                <!-- Result -->
                <div v-if="content" class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Result
                        </h3>
                        <div
                            class="prose prose-sm max-w-none text-gray-800"
                            v-html="renderedContent"
                        />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
