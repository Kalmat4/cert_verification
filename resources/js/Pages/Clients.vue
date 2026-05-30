<template>
  <div>

    <!-- ══ МОДАЛКА ВЫПИСКИ ══ -->
    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"  leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeModal" />
        <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900">Выписка</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ modal.client?.fio }}</p>
            </div>
            <button @click="closeModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto px-5 py-4">
            <div v-if="modal.loading" class="text-center py-8 text-gray-400 text-sm">Загрузка…</div>
            <template v-else-if="modal.data">
              <div v-for="(m, i) in modal.data.meters" :key="i" class="mb-4 pb-4"
                :class="i < modal.data.meters.length - 1 ? 'border-b border-gray-100' : ''">
                <span class="text-xs font-bold uppercase tracking-wide text-gray-400">Счётчик {{ i + 1 }}</span>
                <div class="space-y-1 text-sm mt-1">
                  <div class="flex gap-2">
                    <span class="text-gray-500">Тип:</span>
                    <span class="font-medium text-gray-900">{{ m.type_model || '—' }}</span>
                    <span class="text-gray-400 ml-2">Год:</span>
                    <span class="font-medium text-gray-900">{{ m.make_year || '—' }}</span>
                  </div>
                  <div class="flex gap-2">
                    <span class="text-gray-500">Зав. номер:</span>
                    <span class="font-medium text-gray-900">{{ m.zavod_number }}</span>
                  </div>
                  <div class="flex gap-2">
                    <span class="text-gray-500">Номер пломбы:</span>
                    <span class="font-medium text-gray-900">{{ m.plomb_number || '—' }}</span>
                  </div>
                </div>
              </div>
              <div class="mt-2 pt-3 border-t border-gray-200 space-y-1 text-sm">
                <div class="flex gap-2">
                  <span class="text-gray-500 w-24 flex-shrink-0">Адрес:</span>
                  <span class="text-gray-900">{{ modal.data.address }}</span>
                </div>
                <div class="flex gap-2">
                  <span class="text-gray-500 w-24 flex-shrink-0">ФИО:</span>
                  <span class="font-medium text-gray-900">{{ modal.data.fio }}</span>
                </div>
                <div v-if="modal.data.phone" class="flex gap-2">
                  <span class="text-gray-500 w-24 flex-shrink-0">Телефон:</span>
                  <span class="text-gray-900">{{ modal.data.phone }}</span>
                </div>
              </div>
            </template>
          </div>
          <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-3">
            <span v-if="copied" class="text-xs text-green-600 font-medium">✓ Скопировано!</span>
            <span v-else class="text-xs text-gray-400">Текст для буфера обмена</span>
            <button @click="copyExcerpt" :disabled="!modal.data || modal.loading"
              class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-gray-900 hover:bg-gray-700 disabled:opacity-40 text-white rounded-lg transition-colors">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              Копировать
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ МОДАЛКА СКАЧИВАНИЯ ══ -->
    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"  leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="dl.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeDl" />
        <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

          <!-- Шапка -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900">Скачать документ</h2>
              <p class="text-xs text-gray-500 mt-0.5">{{ dl.client?.fio }}</p>
            </div>
            <button @click="closeDl" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!-- Контент -->
          <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
            <div v-if="dl.loading" class="text-center py-8 text-gray-400 text-sm">Загрузка…</div>

            <template v-else-if="dl.data">

              <!-- Выбор сертификата -->
              <div>
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-3">Выберите сертификат</p>
                <div class="space-y-3">
                  <div v-for="meter in dl.data.meters" :key="meter.id">
                    <!-- Заголовок счётчика -->
                    <div class="flex items-center gap-2 mb-1.5">
                      <span class="text-xs font-semibold text-gray-700">{{ meter.type_model || 'Счётчик' }}</span>
                      <span class="text-xs text-gray-400">· #{{ meter.zavod_number }}</span>
                    </div>
                    <!-- Сертификаты счётчика -->
                    <div v-if="meter.certs.length" class="space-y-1 ml-2">
                      <label v-for="cert in meter.certs" :key="cert.id"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                        :class="dl.certId === cert.id
                          ? 'border-blue-400 bg-blue-50'
                          : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                        <input type="radio" :value="cert.id" v-model="dl.certId" class="text-blue-600" />
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ cert.cert_number }}</p>
                          <p class="text-xs text-gray-500">{{ cert.check_date }}</p>
                        </div>
                      </label>
                    </div>
                    <p v-else class="text-xs text-gray-400 ml-2 italic">Нет сертификатов</p>
                  </div>
                </div>
              </div>

              <!-- Тип документа -->
              <div>
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Документ</p>
                <div class="flex gap-2">
                  <button v-for="d in docTypes" :key="d.key" type="button"
                    @click="dl.docType = d.key"
                    class="flex-1 py-2 text-xs font-semibold rounded-lg border transition-colors"
                    :class="dl.docType === d.key
                      ? 'bg-gray-900 text-white border-gray-900'
                      : 'border-gray-300 text-gray-700 hover:border-gray-400'">
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
                    :class="dl.format === 'word'
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'border-gray-300 text-gray-700 hover:border-blue-300'">
                    Word
                  </button>
                  <button v-if="pdfAvailable" type="button" @click="dl.format = 'pdf'"
                    class="flex-1 py-2 text-xs font-semibold rounded-lg border transition-colors"
                    :class="dl.format === 'pdf'
                      ? 'bg-red-600 text-white border-red-600'
                      : 'border-gray-300 text-gray-700 hover:border-red-300'">
                    PDF
                  </button>
                  <span v-else class="flex-1 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-400 text-center cursor-not-allowed"
                    title="PDF недоступен на Windows">PDF</span>
                </div>
              </div>

            </template>
          </div>

          <!-- Подвал -->
          <div class="px-5 py-3 border-t border-gray-100">
            <button @click="triggerDownload"
              :disabled="!dl.certId || dl.loading"
              class="w-full flex items-center justify-center gap-2 py-2.5 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white rounded-lg transition-colors">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              {{ dl.certId ? 'Скачать' : 'Выберите сертификат' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ ЗАГОЛОВОК ══ -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold text-gray-900">Пользователи</h1>
      <span class="text-sm text-gray-500">Найдено: {{ clients.total }}</span>
    </div>

    <!-- ══ ФИЛЬТРЫ ══ -->
    <div class="flex items-center gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-44">
        <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input v-model="filterFio" @input="applyFilters" type="text" placeholder="Поиск по ФИО"
          class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg outline-none focus:border-blue-500 transition-colors" />
      </div>
      <div class="relative flex-1 min-w-44">
        <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <input v-model="filterAddress" @input="applyFilters" type="text" placeholder="Поиск по адресу"
          class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg outline-none focus:border-blue-500 transition-colors" />
      </div>
      <button v-if="filterFio || filterAddress" @click="clearFilters"
        class="px-3 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
        Сбросить
      </button>
    </div>

    <!-- ══ ПУСТОЕ СОСТОЯНИЕ ══ -->
    <div v-if="clients.data.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <p class="text-gray-400 text-sm">
        {{ filterFio || filterAddress ? 'Ничего не найдено по заданным фильтрам.' : 'Пользователей пока нет.' }}
      </p>
      <button v-if="filterFio || filterAddress" @click="clearFilters"
        class="mt-4 inline-block px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
        Сбросить фильтры
      </button>
    </div>

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-visible">

      <!-- ══ МОБИЛЬНЫЕ КАРТОЧКИ (< sm) ══ -->
      <div class="sm:hidden divide-y divide-gray-100">
        <div v-for="client in clients.data" :key="client.id" class="px-4 py-3 hover:bg-gray-50 transition-colors">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900">{{ client.fio }}</p>
              <p class="text-xs text-gray-500 mt-0.5 truncate">{{ client.address }}</p>
              <div class="flex items-center gap-2 mt-1.5">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-semibold"
                  :class="client.meters_count > 1 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                  {{ client.meters_count }}
                </span>
                <span class="text-xs text-gray-400">{{ client.meters_count === 1 ? 'счётчик' : 'счётчика' }}</span>
              </div>
            </div>
            <div class="flex flex-col gap-1.5 flex-shrink-0">
              <button @click="openModal(client)"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50 transition-colors">
                Выписка
              </button>
              <button @click="openDl(client)"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition-colors">
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
            <th class="text-left px-4 py-3 font-semibold text-gray-600 rounded-tl-xl">№</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600">ФИО</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Адрес</th>
            <th class="text-center px-4 py-3 font-semibold text-gray-600">Счётчиков</th>
            <th class="text-right px-4 py-3 font-semibold text-gray-600 rounded-tr-xl">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="client in clients.data" :key="client.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ client.id }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ client.fio }}</td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell max-w-xs truncate">{{ client.address }}</td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-semibold"
                :class="client.meters_count > 1 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                {{ client.meters_count }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(client)"
                  class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50 transition-colors">
                  Выписка
                </button>
                <button @click="openDl(client)"
                  class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition-colors">
                  Скачать
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Пагинация -->
      <div v-if="clients.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
        <span class="text-xs text-gray-500">Страница {{ clients.current_page }} из {{ clients.last_page }}</span>
        <div class="flex gap-1">
          <template v-for="link in clients.links" :key="link.label">
            <component :is="link.url ? Link : 'span'" :href="link.url"
              class="px-2.5 py-1 text-xs rounded-md border transition-colors"
              :class="link.active
                ? 'bg-blue-600 text-white border-blue-600'
                : link.url
                  ? 'border-gray-300 text-gray-700 hover:bg-gray-100 cursor-pointer'
                  : 'border-gray-200 text-gray-400 cursor-not-allowed'"
              v-html="link.label" />
          </template>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  clients: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const pdfAvailable = computed(() => usePage().props.pdfAvailable)

const docTypes = [
  { key: 'cert',     label: 'Сертификат' },
  { key: 'protocol', label: 'Протокол'   },
  { key: 'garant',   label: 'Гарантия'   },
]

// ── Фильтры ───────────────────────────────────────
const filterFio     = ref(props.filters?.fio     ?? '')
const filterAddress = ref(props.filters?.address ?? '')

let _filterTimer = null
function applyFilters() {
  clearTimeout(_filterTimer)
  _filterTimer = setTimeout(() => {
    router.get('/clients', {
      fio:     filterFio.value     || undefined,
      address: filterAddress.value || undefined,
    }, { preserveState: true, replace: true })
  }, 400)
}
function clearFilters() {
  filterFio.value = filterAddress.value = ''
  router.get('/clients', {}, { preserveState: true, replace: true })
}

// ── Модалка ВЫПИСКИ ───────────────────────────────
const modal = reactive({ open: false, loading: false, client: null, data: null })
const copied = ref(false)

async function openModal(client) {
  modal.open = true; modal.loading = true; modal.client = client; modal.data = null; copied.value = false
  try {
    modal.data = await (await fetch(`/clients/${client.id}/excerpt`)).json()
  } catch (e) { console.error(e) } finally { modal.loading = false }
}
function closeModal() { modal.open = false; modal.client = null; modal.data = null; copied.value = false }

function buildExcerptText() {
  if (!modal.data) return ''
  const d = modal.data, lines = []
  d.meters.forEach((m, i) => {
    if (i > 0) lines.push('')
    lines.push(`Тип счётчика: ${m.type_model || '—'} Год: ${m.make_year || '—'}`)
    lines.push(`Заводской номер: ${m.zavod_number}`)
    lines.push(`Номер пломбы: ${m.plomb_number || '—'}`)
  })
  lines.push('', `Адрес: ${d.address}`, `ФИО: ${d.fio}`)
  if (d.phone) lines.push(`Номер телефона: ${d.phone}`)
  return lines.join('\n')
}

async function copyExcerpt() {
  try {
    await navigator.clipboard.writeText(buildExcerptText())
  } catch {
    const el = Object.assign(document.createElement('textarea'), { value: buildExcerptText() })
    document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el)
  }
  copied.value = true; setTimeout(() => { copied.value = false }, 2500)
}

// ── Модалка СКАЧИВАНИЯ ────────────────────────────
const dl = reactive({ open: false, loading: false, client: null, data: null, certId: null, docType: 'cert', format: 'word' })

async function openDl(client) {
  dl.open = true; dl.loading = true; dl.client = client; dl.data = null; dl.certId = null; dl.docType = 'cert'; dl.format = 'word'
  try {
    dl.data = await (await fetch(`/clients/${client.id}/meters-certs`)).json()
    // Автовыбор первого сертификата если он один
    const allCerts = dl.data.meters.flatMap(m => m.certs)
    if (allCerts.length === 1) dl.certId = allCerts[0].id
  } catch (e) { console.error(e) } finally { dl.loading = false }
}
function closeDl() { dl.open = false; dl.client = null; dl.data = null }

function downloadUrl(certId, docType, format) {
  return docType === 'cert'
    ? `/certificate/${certId}/${format}`
    : `/certificate/${certId}/${docType}/${format}`
}

function triggerDownload() {
  if (!dl.certId) return
  window.open(downloadUrl(dl.certId, dl.docType, dl.format), '_blank')
}
</script>
