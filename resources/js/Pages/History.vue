<template>
  <div>
    <div v-if="openMenuId !== null" class="fixed inset-0 z-10" @click="openMenuId = null" />

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

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-visible">
      <table class="w-full text-sm rounded-xl overflow-hidden">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="px-3 py-3 w-8 rounded-tl-xl">
              <input
                type="checkbox"
                :checked="allSelected"
                :ref="el => { if (el) el.indeterminate = someSelected && !allSelected }"
                @change="toggleAll"
                class="rounded border-gray-300 text-blue-600 cursor-pointer"
              />
            </th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">№</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">№ сертификата</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">Зав. номер</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">ФИО</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Класс</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Дата поверки</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Действителен до</th>
            <th class="text-right px-4 py-3 font-semibold text-gray-600 rounded-tr-xl">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="cert in certs.data"
            :key="cert.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors"
            :class="selectedIds.includes(cert.id) ? 'bg-blue-50/60' : ''"
          >
            <td class="px-3 py-3 text-center">
              <input
                type="checkbox"
                :checked="selectedIds.includes(cert.id)"
                @change="toggleOne(cert.id)"
                class="rounded border-gray-300 text-blue-600 cursor-pointer"
              />
            </td>
            <td class="px-4 py-3 text-gray-400 text-xs">{{ cert.id }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ cert.cert_number }}</td>
            <td class="px-4 py-3 text-gray-700">{{ cert.zavod_number }}</td>
            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell max-w-xs truncate">{{ cert.fio }}</td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ cert.class }}</td>
            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ cert.check_date }}</td>
            <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ cert.final_date }}</td>
            <td class="px-4 py-3">
              <div class="flex flex-col items-end gap-1.5">

                <!-- Навигация -->
                <div class="flex items-center gap-1.5">
                  <Link :href="`/certificate/${cert.id}/edit`"
                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
                    Редактировать
                  </Link>
                  <button @click="copyAndCreate(cert)"
                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-violet-300 text-violet-700 hover:bg-violet-50 transition-colors">
                    Копировать
                  </button>
                  <button @click="deleteCert(cert)"
                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
                    Удалить
                  </button>
                </div>

                <!-- Word -->
                <div class="flex items-center gap-1">
                  <span class="text-xs font-medium text-gray-400 w-7">Word</span>
                  <a :href="`/certificate/${cert.id}/word`" title="Сертификат (Word)"
                    class="px-2 py-0.5 text-xs font-medium rounded bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Серт.
                  </a>
                  <a :href="`/certificate/${cert.id}/protocol/word`" title="Протокол (Word)"
                    class="px-2 py-0.5 text-xs font-medium rounded bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Прот.
                  </a>
                  <a :href="`/certificate/${cert.id}/garant/word`" title="Гарантия (Word)"
                    class="px-2 py-0.5 text-xs font-medium rounded bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    Гар.
                  </a>
                </div>

                <!-- PDF -->
                <div class="flex items-center gap-1">
                  <span class="text-xs font-medium text-gray-400 w-7">PDF</span>
                  <template v-if="pdfAvailable">
                    <a :href="`/certificate/${cert.id}/pdf`" title="Сертификат (PDF)"
                      class="px-2 py-0.5 text-xs font-medium rounded bg-red-600 text-white hover:bg-red-700 transition-colors">
                      Серт.
                    </a>
                    <a :href="`/certificate/${cert.id}/protocol/pdf`" title="Протокол (PDF)"
                      class="px-2 py-0.5 text-xs font-medium rounded bg-red-600 text-white hover:bg-red-700 transition-colors">
                      Прот.
                    </a>
                    <a :href="`/certificate/${cert.id}/garant/pdf`" title="Гарантия (PDF)"
                      class="px-2 py-0.5 text-xs font-medium rounded bg-red-600 text-white hover:bg-red-700 transition-colors">
                      Гар.
                    </a>
                  </template>
                  <span v-else class="text-xs text-gray-400 italic">недоступно на Windows</span>
                </div>

              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="certs.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
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

    <!-- Плавающая панель массового скачивания -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div
        v-if="selectedIds.length > 0"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-gray-900 text-white rounded-2xl shadow-2xl px-4 py-3 whitespace-nowrap"
      >
        <!-- Счётчик -->
        <span class="text-sm font-semibold mr-1">Выбрано: {{ selectedIds.length }}</span>

        <div class="w-px h-5 bg-gray-600" />

        <!-- Word -->
        <span class="text-xs text-gray-400 font-medium">Word:</span>
        <button @click="downloadZip('cert', 'word')"
          class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-500 rounded-lg transition-colors">
          <IconZip /> Серт.
        </button>
        <button @click="downloadZip('protocol', 'word')"
          class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-500 rounded-lg transition-colors">
          <IconZip /> Прот.
        </button>
        <button @click="downloadZip('garant', 'word')"
          class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-500 rounded-lg transition-colors">
          <IconZip /> Гар.
        </button>

        <!-- PDF (только если доступно) -->
        <template v-if="pdfAvailable">
          <div class="w-px h-5 bg-gray-600" />
          <span class="text-xs text-gray-400 font-medium">PDF:</span>
          <button @click="downloadZip('cert', 'pdf')"
            class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-red-600 hover:bg-red-500 rounded-lg transition-colors">
            <IconZip /> Серт.
          </button>
          <button @click="downloadZip('protocol', 'pdf')"
            class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-red-600 hover:bg-red-500 rounded-lg transition-colors">
            <IconZip /> Прот.
          </button>
          <button @click="downloadZip('garant', 'pdf')"
            class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-red-600 hover:bg-red-500 rounded-lg transition-colors">
            <IconZip /> Гар.
          </button>
        </template>

        <div class="w-px h-5 bg-gray-600" />

        <!-- Сбросить -->
        <button @click="selectedIds = []" class="p-1 text-gray-400 hover:text-white transition-colors" title="Сбросить выбор">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, defineComponent, h } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const IconZip = defineComponent({
  render: () => h('svg', { width: 12, height: 12, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2.2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
    h('path', { d: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4' }),
    h('polyline', { points: '7 10 12 15 17 10' }),
    h('line', { x1: '12', y1: '15', x2: '12', y2: '3' }),
  ]),
})

const props = defineProps({
  certs: { type: Object, required: true },
})

const openMenuId   = ref(null)
const selectedIds  = ref([])
const pdfAvailable = computed(() => usePage().props.pdfAvailable)

const allPageIds   = computed(() => props.certs.data.map(c => c.id))
const allSelected  = computed(() => allPageIds.value.length > 0 && allPageIds.value.every(id => selectedIds.value.includes(id)))
const someSelected = computed(() => selectedIds.value.length > 0)

function toggleAll() {
  if (allSelected.value) {
    selectedIds.value = selectedIds.value.filter(id => !allPageIds.value.includes(id))
  } else {
    const extra = allPageIds.value.filter(id => !selectedIds.value.includes(id))
    selectedIds.value = [...selectedIds.value, ...extra]
  }
}

function toggleOne(id) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter(i => i !== id)
  } else {
    selectedIds.value = [...selectedIds.value, id]
  }
}

function copyAndCreate(cert) {
  router.get('/certificate/create', { copy_id: cert.id })
}

function deleteCert(cert) {
  if (!confirm(`Удалить сертификат ${cert.cert_number}?\nЭто действие необратимо.`)) return
  router.delete(`/certificate/${cert.id}`)
}

function downloadZip(type, format = 'word') {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/certificates/download-zip'
  form.style.display = 'none'

  const addHidden = (name, value) => {
    const input = document.createElement('input')
    input.type  = 'hidden'
    input.name  = name
    input.value = value
    form.appendChild(input)
  }

  addHidden('_token', csrfToken)
  addHidden('type', type)
  addHidden('format', format)
  selectedIds.value.forEach(id => addHidden('ids[]', String(id)))

  document.body.appendChild(form)
  form.submit()
  document.body.removeChild(form)
}
</script>
