<template>
  <div>
    <!-- Закрытие меню по клику вне -->
    <div v-if="openMenuId !== null" class="fixed inset-0 z-10" @click="openMenuId = null" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">Главная</h1>
      <span class="text-sm text-gray-500">Последние 5 сертификатов</span>
    </div>

    <div v-if="certs.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <p class="text-gray-400 text-sm">Сертификатов ещё нет.</p>
      <Link href="/certificate/create" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
        Создать первый
      </Link>
    </div>

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-visible">
      <table class="w-full text-sm rounded-xl overflow-hidden">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 font-semibold text-gray-600 rounded-tl-xl">№ сертификата</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">Зав. номер</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">ФИО / Адрес</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Дата поверки</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Действителен до</th>
            <th class="text-right px-4 py-3 font-semibold text-gray-600 rounded-tr-xl">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cert in certs" :key="cert.id" class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 font-medium text-gray-900">{{ cert.cert_number }}</td>
            <td class="px-4 py-3 text-gray-700">{{ cert.zavod_number }}</td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell max-w-xs truncate">{{ cert.fio }}</td>
            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ cert.check_date }}</td>
            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ cert.final_date }}</td>
            <td class="px-4 py-3">
              <div class="flex flex-col items-end gap-1.5">

                <!-- Строка 1: навигация -->
                <div class="flex items-center gap-1.5">
                  <Link :href="`/certificate/${cert.id}/edit`"
                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
                    Редактировать
                  </Link>
                  <button @click="copyAndCreate(cert)"
                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-violet-300 text-violet-700 hover:bg-violet-50 transition-colors">
                    Копировать
                  </button>
                </div>

                <!-- Строка 2: скачать -->
                <div class="flex items-center gap-1">
                  <span class="text-xs text-gray-400 mr-0.5">↓</span>
                  <a :href="`/certificate/${cert.id}/word`"
                    title="Сертификат поверки (Word)"
                    class="px-2 py-1 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Серт.
                  </a>
                  <a :href="`/certificate/${cert.id}/protocol/word`"
                    title="Протокол (Word)"
                    class="px-2 py-1 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Прот.
                  </a>
                  <a :href="`/certificate/${cert.id}/garant/word`"
                    title="Гарантийное соглашение (Word)"
                    class="px-2 py-1 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Гар.
                  </a>
                </div>

              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
  certs: { type: Array, default: () => [] },
})

const openMenuId = ref(null)
const pdfAvailable = computed(() => usePage().props.pdfAvailable)

function copyAndCreate(cert) {
  router.get('/certificate/create', { copy_id: cert.id })
}
</script>
