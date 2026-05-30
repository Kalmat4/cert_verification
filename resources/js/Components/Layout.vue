<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">
        <Link href="/history" class="font-bold text-gray-800 text-base tracking-tight hover:text-blue-600 transition-colors">Поверка счётчиков</Link>
        <nav class="flex items-center gap-1">
          <Link
            href="/history"
            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
            :class="isActive('/history')
              ? 'bg-blue-50 text-blue-700'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
          >
            История поверок
          </Link>
          <Link
            href="/clients"
            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
            :class="isActive('/clients')
              ? 'bg-blue-50 text-blue-700'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
          >
            Пользователи
          </Link>
          <Link
            href="/certificate/create"
            class="ml-2 px-3 py-1.5 rounded-md text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors"
          >
            Добавить
          </Link>
        </nav>
      </div>
    </header>
    <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 py-8 w-full">
      <component :is="$slots.default ? 'div' : 'div'">
        <slot />
      </component>
    </main>
    <footer class="border-t border-gray-100 py-4 mt-4">
      <p class="text-center text-xs text-gray-400">@k_almat_t — 2026</p>
    </footer>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const currentUrl = computed(() => page.url)

function isActive(path) {
  return currentUrl.value.startsWith(path)
}
</script>
