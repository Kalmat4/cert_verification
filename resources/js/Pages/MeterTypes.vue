<template>
  <div>

    <!-- ══ МОДАЛКА ДОБАВЛЕНИЯ / РЕДАКТИРОВАНИЯ ══ -->
    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"  leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeModal" />
        <div class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-2xl">

          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">
              {{ modal.editing ? 'Редактировать тип' : 'Добавить тип счётчика' }}
            </h2>
            <button @click="closeModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitModal" class="px-6 py-5 space-y-4">
            <!-- Flash в модалке -->
            <div v-if="Object.keys(mForm.errors).length" class="px-4 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs">
              <div v-for="(e, f) in mForm.errors" :key="f">{{ e }}</div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Название типа <span class="text-red-500">*</span></label>
              <input v-model="mForm.type_name" type="text" placeholder="АКВА L110 D15 ВЕ"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                :class="mForm.errors.type_name ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Изготовитель <span class="text-red-500">*</span></label>
              <input v-model="mForm.manufacturer" type="text" placeholder="ООО «Компания»"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                :class="mForm.errors.manufacturer ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Методика поверки <span class="text-red-500">*</span></label>
              <input v-model="mForm.verification_method" type="text" placeholder="СТ РК 2.86-2005"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                :class="mForm.errors.verification_method ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Интервал поверки (лет) <span class="text-red-500">*</span></label>
              <input v-model.number="mForm.verify_interval_years" type="number" min="1" max="20"
                class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeModal"
                class="px-4 py-2 text-sm font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Отмена
              </button>
              <button type="submit" :disabled="mForm.processing"
                class="px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg transition-colors">
                {{ mForm.processing ? 'Сохранение…' : (modal.editing ? 'Сохранить' : 'Добавить') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ══ ЗАГОЛОВОК ══ -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold text-gray-900">Типы счётчиков</h1>
      <button @click="openAdd"
        class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Добавить тип
      </button>
    </div>

    <!-- Flash -->
    <div v-if="$page.props.flash?.success" class="mb-4 px-4 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
      {{ $page.props.flash.error }}
    </div>

    <!-- ══ ПОИСК ══ -->
    <div class="flex items-center gap-3 mb-4">
      <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input v-model="filterQ" @input="applyFilter" type="text" placeholder="Поиск по названию или изготовителю…"
          class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg outline-none focus:border-blue-500 transition-colors" />
      </div>
      <button v-if="filterQ" @click="clearFilter"
        class="px-3 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        Сбросить
      </button>
      <span class="text-sm text-gray-500">{{ types.total }} записей</span>
    </div>

    <!-- ══ ПУСТОЕ СОСТОЯНИЕ ══ -->
    <div v-if="types.data.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <p class="text-gray-400 text-sm">{{ filterQ ? 'Ничего не найдено.' : 'Типов счётчиков нет.' }}</p>
    </div>

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-visible">

      <!-- Мобильные карточки -->
      <div class="sm:hidden divide-y divide-gray-100">
        <div v-for="t in types.data" :key="t.id" class="px-4 py-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900">{{ t.type_name }}</p>
              <p class="text-xs text-gray-500 mt-0.5 truncate">{{ t.manufacturer }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ t.verification_method }}</p>
              <div class="flex items-center gap-3 mt-1 text-xs">
                <span class="text-gray-500">{{ t.verify_interval_years }} лет</span>
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full font-semibold"
                  :class="t.meters_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                  {{ t.meters_count }} счётчиков
                </span>
              </div>
            </div>
            <div class="flex flex-col gap-1.5 flex-shrink-0">
              <button @click="openEdit(t)" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 text-gray-700 hover:bg-gray-100">Ред.</button>
              <button @click="deleteType(t)" class="px-2.5 py-1 text-xs font-medium rounded border border-red-300 text-red-600 hover:bg-red-50">Удалить</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Десктоп таблица -->
      <table class="hidden sm:table w-full text-sm rounded-xl overflow-hidden">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 font-semibold text-gray-600 rounded-tl-xl">Тип / Модель</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">Изготовитель</th>
            <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Методика</th>
            <th class="text-center px-4 py-3 font-semibold text-gray-600 w-20">Лет</th>
            <th class="text-center px-4 py-3 font-semibold text-gray-600 w-32">Счётчиков</th>
            <th class="text-right px-4 py-3 font-semibold text-gray-600 rounded-tr-xl">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in types.data" :key="t.id"
            class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 font-medium text-gray-900">{{ t.type_name }}</td>
            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell max-w-xs truncate">{{ t.manufacturer }}</td>
            <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">{{ t.verification_method }}</td>
            <td class="px-4 py-3 text-center text-gray-600">{{ t.verify_interval_years }}</td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="t.meters_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                {{ t.meters_count }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1.5">
                <button @click="openEdit(t)"
                  class="px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
                  Редактировать
                </button>
                <button @click="deleteType(t)"
                  class="px-2.5 py-1 text-xs font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
                  Удалить
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Пагинация -->
      <div v-if="types.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
        <span class="text-xs text-gray-500">Страница {{ types.current_page }} из {{ types.last_page }}</span>
        <div class="flex gap-1">
          <template v-for="link in types.links" :key="link.label">
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
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  types:   { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

// ── Фильтр ────────────────────────────────────────
const filterQ = ref(props.filters?.q ?? '')

let _filterTimer = null
function applyFilter() {
  clearTimeout(_filterTimer)
  _filterTimer = setTimeout(() => {
    router.get('/meter-types', { q: filterQ.value || undefined }, { preserveState: true, replace: true })
  }, 400)
}
function clearFilter() {
  filterQ.value = ''
  router.get('/meter-types', {}, { preserveState: true, replace: true })
}

// ── Модалка ───────────────────────────────────────
const modal = reactive({ open: false, editing: false, id: null })

const mForm = useForm({
  type_name:             '',
  manufacturer:          '',
  verification_method:   '',
  verify_interval_years: 5,
})

function openAdd() {
  mForm.reset()
  mForm.clearErrors()
  mForm.verify_interval_years = 5
  modal.editing = false
  modal.id      = null
  modal.open    = true
}

function openEdit(t) {
  mForm.type_name             = t.type_name
  mForm.manufacturer          = t.manufacturer
  mForm.verification_method   = t.verification_method
  mForm.verify_interval_years = t.verify_interval_years
  mForm.clearErrors()
  modal.editing = true
  modal.id      = t.id
  modal.open    = true
}

function closeModal() {
  modal.open = false
}

function submitModal() {
  if (modal.editing) {
    mForm.put(`/meter-types/${modal.id}`, { onSuccess: closeModal })
  } else {
    mForm.post('/meter-types', { onSuccess: closeModal })
  }
}

function deleteType(t) {
  if (!confirm(`Удалить тип "${t.type_name}"?`)) return
  router.delete(`/meter-types/${t.id}`)
}
</script>
