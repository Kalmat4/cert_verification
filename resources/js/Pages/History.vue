<template>
  <div>
    <div v-if="openMenuId !== null" class="fixed inset-0 z-10" @click="openMenuId = null" />

    <!-- ══ МОДАЛКА СКАЧИВАНИЯ ══ -->
    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"  leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="dl.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeDl" />
        <div class="relative z-10 w-full max-w-sm bg-white rounded-2xl shadow-2xl flex flex-col">
          <!-- Шапка -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900">Скачать документ</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ dl.cert?.cert_number }}</p>
            </div>
            <button @click="closeDl" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <!-- Контент -->
          <div class="px-5 py-4 space-y-4">
            <!-- Тип документа -->
            <div>
              <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Документ</p>
              <div class="flex gap-2">
                <button v-for="d in dlDocTypes" :key="d.key" type="button" @click="dl.docType = d.key"
                  class="flex-1 py-2 text-xs font-semibold rounded-lg border transition-colors"
                  :class="dl.docType === d.key ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 text-gray-700 hover:border-gray-400'">
                  {{ d.label }}
                </button>
              </div>
            </div>
            <!-- Формат -->
            <div>
              <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Формат</p>
              <div class="flex gap-2">
                <button type="button" @click="dl.format = 'word'"
                  class="flex-1 py-2 text-xs font-semibold rounded-lg border transition-colors"
                  :class="dl.format === 'word' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-700 hover:border-blue-300'">
                  Word
                </button>
                <button v-if="pdfAvailable" type="button" @click="dl.format = 'pdf'"
                  class="flex-1 py-2 text-xs font-semibold rounded-lg border transition-colors"
                  :class="dl.format === 'pdf' ? 'bg-red-600 text-white border-red-600' : 'border-gray-300 text-gray-700 hover:border-red-300'">
                  PDF
                </button>
                <span v-else class="flex-1 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-400 text-center cursor-not-allowed" title="PDF недоступен на Windows">PDF</span>
              </div>
            </div>
          </div>
          <!-- Кнопка -->
          <div class="px-5 pb-4">
            <button @click="triggerDl"
              class="w-full flex items-center justify-center gap-2 py-2.5 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              Скачать
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold text-gray-900">История поверок</h1>
      <span class="text-sm text-gray-500">Найдено: {{ certs.total }}</span>
    </div>

    <!-- Фильтры -->
    <div class="flex items-center gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="filterFio"
          @input="applyFilters"
          type="text"
          placeholder="Поиск по ФИО"
          class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg outline-none focus:border-blue-500 transition-colors"
        />
      </div>
      <div class="relative flex-1 min-w-40">
        <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <input
          v-model="filterDate"
          @input="applyFilters"
          type="text"
          placeholder="Дата поверки (напр. 2026)"
          class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg outline-none focus:border-blue-500 transition-colors"
        />
      </div>
      <button
        v-if="filterFio || filterDate"
        @click="clearFilters"
        class="px-3 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap"
      >
        Сбросить
      </button>
    </div>

    <div v-if="certs.data.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <p class="text-gray-400 text-sm">
        {{ filterFio || filterDate ? 'Ничего не найдено по заданным фильтрам.' : 'Сертификатов ещё нет.' }}
      </p>
      <Link v-if="!filterFio && !filterDate" href="/certificate/create" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
        Создать первый
      </Link>
      <button v-else @click="clearFilters" class="mt-4 inline-block px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
        Сбросить фильтры
      </button>
    </div>

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-visible">

      <!-- ══ МОБИЛЬНЫЕ КАРТОЧКИ (< sm) ══ -->
      <div class="sm:hidden divide-y divide-gray-100">
        <!-- Шапка: выбрать все -->
        <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-t-xl">
          <input
            type="checkbox"
            :checked="allSelected"
            :ref="el => { if (el) el.indeterminate = someSelected && !allSelected }"
            @change="toggleAll"
            class="rounded border-gray-300 text-blue-600 cursor-pointer"
          />
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Все на странице</span>
        </div>

        <div v-for="cert in certs.data" :key="cert.id"
          class="px-4 py-3 transition-colors"
          :class="selectedIds.includes(cert.id) ? 'bg-blue-50' : ''">

          <div class="flex items-start gap-3">
            <input type="checkbox"
              :checked="selectedIds.includes(cert.id)"
              @change="toggleOne(cert.id)"
              class="mt-1 rounded border-gray-300 text-blue-600 cursor-pointer flex-shrink-0" />

            <div class="flex-1 min-w-0">
              <!-- Строка 1: номер + ID -->
              <div class="flex items-baseline justify-between gap-2">
                <span class="text-sm font-semibold text-gray-900 truncate">{{ cert.cert_number }}</span>
                <span class="text-xs text-gray-400 flex-shrink-0">#{{ cert.id }}</span>
              </div>

              <!-- Строка 2: ФИО клиента -->
              <p class="text-sm text-gray-700 mt-0.5">{{ cert.meter?.client?.fio ?? '—' }}</p>

              <!-- Строка 3: зав. номер · дата · класс -->
              <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1 text-xs text-gray-500">
                <span>{{ cert.meter?.zavod_number ?? '—' }}</span>
                <span>{{ cert.check_date }}</span>
                <span v-if="cert.meter?.class">Кл. {{ cert.meter.class }}</span>
                <span v-if="(cert.meter?.client?.meters_count ?? 0) > 1"
                  class="text-blue-600">{{ cert.meter.client.meters_count }} счётчика</span>
              </div>

              <!-- Действия -->
              <div class="flex flex-wrap gap-1.5 mt-2.5">
                <Link :href="`/certificate/${cert.id}/edit`"
                  class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
                  Редактировать
                </Link>
                <button @click="copyAndCreate(cert)"
                  class="px-2.5 py-1 text-xs font-medium rounded border border-violet-300 text-violet-700 hover:bg-violet-50">
                  Копировать
                </button>
                <button @click="deleteCert(cert)"
                  class="px-2.5 py-1 text-xs font-medium rounded border border-red-300 text-red-600 hover:bg-red-50">
                  Удалить
                </button>
              </div>

              <button @click="openDl(cert)"
                class="mt-1.5 px-3 py-1 text-xs font-semibold rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition-colors">
                Скачать
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ ДЕСКТОП ТАБЛИЦА (>= sm) ══ -->
      <table class="hidden sm:table w-full text-sm rounded-xl overflow-hidden">
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
            <th class="text-left px-4 py-3 font-semibold text-gray-600">Зав. номер</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">ФИО</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">Счётчиков</th>
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
            <td class="px-4 py-3 text-gray-700">{{ cert.meter?.zavod_number ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell max-w-xs truncate">{{ cert.meter?.client?.fio ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 hidden xl:table-cell text-center">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-semibold"
                :class="(cert.meter?.client?.meters_count ?? 0) > 1 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                {{ cert.meter?.client?.meters_count ?? '—' }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ cert.meter?.class ?? '—' }}</td>
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

                <button @click="openDl(cert)"
                  class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition-colors">
                  Скачать
                </button>

              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination (общая) -->
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
        class="fixed bottom-4 inset-x-3 sm:inset-x-auto sm:bottom-6 sm:left-1/2 sm:-translate-x-1/2 sm:w-max z-50 flex items-center gap-2 bg-gray-900 text-white rounded-2xl shadow-2xl px-4 py-3 overflow-x-auto"
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
import { ref, computed, reactive, defineComponent, h } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const IconZip = defineComponent({
  render: () => h('svg', { width: 12, height: 12, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2.2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
    h('path', { d: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4' }),
    h('polyline', { points: '7 10 12 15 17 10' }),
    h('line', { x1: '12', y1: '15', x2: '12', y2: '3' }),
  ]),
})

const props = defineProps({
  certs:   { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const openMenuId   = ref(null)
const selectedIds  = ref([])
const pdfAvailable = computed(() => usePage().props.pdfAvailable)

// ── Модалка скачивания ────────────────────────────
const dlDocTypes = [
  { key: 'cert',     label: 'Сертификат' },
  { key: 'protocol', label: 'Протокол'   },
  { key: 'garant',   label: 'Гарантия'   },
]
const dl = reactive({ open: false, cert: null, docType: 'cert', format: 'word' })

function openDl(cert) {
  dl.open    = true
  dl.cert    = cert
  dl.docType = 'cert'
  dl.format  = 'word'
}
function closeDl() { dl.open = false; dl.cert = null }

function triggerDl() {
  if (!dl.cert) return
  const path = dl.docType === 'cert'
    ? `/certificate/${dl.cert.id}/${dl.format}`
    : `/certificate/${dl.cert.id}/${dl.docType}/${dl.format}`
  window.open(path, '_blank')
  closeDl()
}

// ── Фильтры ───────────────────────────────────────
const filterFio  = ref(props.filters?.fio        ?? '')
const filterDate = ref(props.filters?.check_date ?? '')

let _filterTimer = null
function applyFilters() {
  clearTimeout(_filterTimer)
  _filterTimer = setTimeout(() => {
    router.get('/history', {
      fio:        filterFio.value  || undefined,
      check_date: filterDate.value || undefined,
    }, { preserveState: true, replace: true })
  }, 400)
}

function clearFilters() {
  filterFio.value  = ''
  filterDate.value = ''
  router.get('/history', {}, { preserveState: true, replace: true })
}

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
