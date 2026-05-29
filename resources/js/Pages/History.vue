<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">История</h1>
      <span class="text-sm text-gray-500">Всего: {{ certs.total }}</span>
    </div>

    <div v-if="certs.data.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <p class="text-gray-400 text-sm">Сертификатов ещё нет.</p>
      <Link href="/certificate/create" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
        Создать первый
      </Link>
    </div>

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 font-semibold text-gray-600">№</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">№ сертификата</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">Зав. номер</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">ФИО / Адрес</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Класс</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Дата поверки</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Действителен до</th>
            <th class="text-right px-4 py-3 font-semibold text-gray-600">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cert in certs.data" :key="cert.id" class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ cert.id }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ cert.cert_number }}</td>
            <td class="px-4 py-3 text-gray-700">{{ cert.zavod_number }}</td>
            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell max-w-xs truncate">{{ cert.fio_address }}</td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ cert.class }}</td>
            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ cert.check_date }}</td>
            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ cert.final_date }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2 flex-wrap">
                <Link :href="`/certificate/${cert.id}/edit`" class="px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
                  Редактировать
                </Link>
                <a :href="`/certificate/${cert.id}/word`" class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                  Word
                </a>
                <a v-if="pdfAvailable" :href="`/certificate/${cert.id}/pdf`" class="px-2.5 py-1 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors">
                  PDF
                </a>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="certs.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50">
        <span class="text-xs text-gray-500">
          Страница {{ certs.current_page }} из {{ certs.last_page }}
        </span>
        <div class="flex gap-1">
          <template v-for="link in certs.links" :key="link.label">
            <component
              :is="link.url ? Link : 'span'"
              :href="link.url"
              class="px-2.5 py-1 text-xs rounded-md border transition-colors"
              :class="link.active
                ? 'bg-blue-600 text-white border-blue-600'
                : link.url
                  ? 'border-gray-300 text-gray-700 hover:bg-gray-100 cursor-pointer'
                  : 'border-gray-200 text-gray-400 cursor-not-allowed'"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'



const props = defineProps({
  certs: { type: Object, required: true },
})

const pdfAvailable = computed(() => usePage().props.pdfAvailable)

</script>
