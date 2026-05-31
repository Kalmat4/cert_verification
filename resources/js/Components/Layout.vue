<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-white border-b border-gray-200 shadow-sm relative">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">

        <!-- Логотип -->
        <Link href="/history" class="font-bold text-gray-800 text-base tracking-tight hover:text-blue-600 transition-colors whitespace-nowrap">
          Поверка счётчиков
        </Link>

        <!-- Десктоп навигация -->
        <nav class="hidden md:flex items-center gap-1">
          <Link href="/history"
            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
            :class="isActive('/history') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
            История поверок
          </Link>
          <Link href="/clients"
            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
            :class="isActive('/clients') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
            Пользователи
          </Link>
          <Link href="/meter-types"
            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
            :class="isActive('/meter-types') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
            Типы счётчиков
          </Link>
          <Link href="/certificate/create"
            class="ml-2 px-3 py-1.5 rounded-md text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
            + Добавить
          </Link>
        </nav>

        <!-- Мобильная: кнопка + Добавить + бургер -->
        <div class="flex items-center gap-2 md:hidden">
          <Link href="/certificate/create"
            class="px-3 py-1.5 rounded-md text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
            + Добавить
          </Link>
          <button @click="menuOpen = !menuOpen"
            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors"
            :aria-expanded="menuOpen">
            <!-- Burger / Close icon -->
            <svg v-if="!menuOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <line x1="3" y1="6"  x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Мобильное меню (выпадающее) -->
      <Transition
        enter-active-class="transition-all duration-150 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-100 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="menuOpen" class="md:hidden absolute top-full left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-40 px-4 py-3 space-y-1">
          <Link href="/history" @click="menuOpen = false"
            class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
            :class="isActive('/history') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'">
            История поверок
          </Link>
          <Link href="/clients" @click="menuOpen = false"
            class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
            :class="isActive('/clients') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'">
            Пользователи
          </Link>
          <Link href="/meter-types" @click="menuOpen = false"
            class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
            :class="isActive('/meter-types') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'">
            Типы счётчиков
          </Link>
        </div>
      </Transition>
    </header>

    <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 py-8 w-full">
      <slot />
    </main>

    <footer class="border-t border-gray-100 py-4 mt-4">
      <p class="text-center text-xs text-gray-400">@k_almat_t — 2026</p>
    </footer>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page       = usePage()
const menuOpen   = ref(false)
const currentUrl = computed(() => page.url)

function isActive(path) {
  return currentUrl.value.startsWith(path)
}
</script>
