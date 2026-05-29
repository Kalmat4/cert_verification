<template>
  <div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">
        {{ cert ? 'Редактировать сертификат' : 'Новый сертификат' }}
      </h1>
      <span v-if="cert" class="text-xs text-gray-400"># {{ cert.id }}</span>
    </div>

    <!-- Success -->
    <div v-if="flash.success" class="mb-5 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
      {{ flash.success }}
    </div>

    <!-- Errors -->
    <div v-if="Object.keys(form.errors).length" class="mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
      <ul class="list-disc list-inside space-y-0.5">
        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
      </ul>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
      <form @submit.prevent="submit">

        <!-- Данные счётчика -->
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Данные счётчика</p>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Номер сертификата</label>
            <input v-model="form.cert_number" type="text" placeholder="VM-07-26-6206067"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.cert_number ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Заводской номер</label>
            <input v-model="form.zavod_number" type="text" placeholder="7525449"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.zavod_number ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1">
            Тип / модель
          </label>
          <input v-model="form.type_model" type="text" placeholder="ВСХ-15"
            class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors border-gray-300 focus:border-blue-500" />
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Год изготовления</label>
            <input v-model="form.make_year" type="text" placeholder="2019г."
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.make_year ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Класс</label>
            <input v-model="form.class" type="text" placeholder="В"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.class ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Номер пломбы</label>
            <input v-model="form.plomb_number" type="text" placeholder="12345"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.plomb_number ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1">
            Показания счётчика (м³) <span class="font-normal text-gray-400">только число</span>
          </label>
          <input v-model="form.water_data" type="number" step="any" min="0" placeholder="0"
            class="w-40 px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
            :class="form.errors.water_data ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
        </div>

        <hr class="border-gray-100 my-5" />

        <!-- Пользователь -->
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Пользователь</p>

        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1">ФИО</label>
          <input v-model="form.fio" type="text"
            placeholder="Иванов А. Г."
            class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
            :class="form.errors.fio ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          <p class="text-xs text-gray-400 mt-1">Формат: «Фамилия И. О.»</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Адрес</label>
            <input v-model="form.address" type="text"
              placeholder="г. Костанай, мкр. Береке д. 67а кв. 22"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.address ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">
              Номер телефона
            </label>
            <input v-model="form.phone" type="text"
              placeholder="+7 (700) 000-00-00"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.phone ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
        </div>

        <hr class="border-gray-100 my-5" />

        <!-- Даты -->
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Даты поверки</p>

        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Дата поверки</label>
            <input v-model="form.check_date" @input="formatDate" type="text"
              placeholder="22.04.2026" maxlength="10"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.check_date ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
            <p class="text-xs text-gray-400 mt-1">Формат: ДД.ММ.ГГГГ</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">
              Действителен до <span class="font-normal text-gray-400">авто +5 лет</span>
            </label>
            <input :value="finalDate" type="text" placeholder="автоматически" readonly
              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed" />
          </div>
        </div>

        <!-- Кнопка сохранения -->
        <button type="submit" :disabled="form.processing"
          class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
          <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          {{ form.processing ? 'Сохранение…' : 'Сохранить данные о поверке' }}
        </button>
      </form>

      <!-- Скачать — только после сохранения -->
      <template v-if="cert">
        <hr class="border-gray-100 my-6" />
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Скачать</p>

        <div class="space-y-3">

          <!-- Сертификат поверки -->
          <div class="border border-gray-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-gray-500 mb-2">Сертификат поверки</p>
            <div class="flex gap-2">
              <a :href="`/certificate/${cert.id}/word`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload />
                WORD
              </a>
              <a v-if="pdfAvailable" :href="`/certificate/${cert.id}/pdf`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload />
                PDF
              </a>
              <span v-else
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-gray-100 text-gray-400 font-semibold rounded-md text-xs cursor-not-allowed"
                title="Недоступно на Windows">
                <IconDownload />
                PDF
              </span>
            </div>
          </div>

          <!-- Протокол -->
          <div class="border border-gray-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-gray-500 mb-2">Протокол</p>
            <div class="flex gap-2">
              <button disabled
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-gray-100 text-gray-400 font-semibold rounded-md text-xs cursor-not-allowed">
                <IconDownload />
                WORD
              </button>
              <button disabled
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-gray-100 text-gray-400 font-semibold rounded-md text-xs cursor-not-allowed">
                <IconDownload />
                PDF
              </button>
            </div>
          </div>

          <!-- Гарантийный талон -->
          <div class="border border-gray-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-gray-500 mb-2">Гарантийный талон</p>
            <div class="flex gap-2">
              <a :href="`/certificate/${cert.id}/garant/word`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload />
                WORD
              </a>
              <a v-if="pdfAvailable" :href="`/certificate/${cert.id}/garant/pdf`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload />
                PDF
              </a>
              <span v-else
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-gray-100 text-gray-400 font-semibold rounded-md text-xs cursor-not-allowed"
                title="Недоступно на Windows">
                <IconDownload />
                PDF
              </span>
            </div>
          </div>

        </div>
        <p v-if="!pdfAvailable" class="text-xs text-gray-400 mt-2">
          Конвертация в PDF недоступна на Windows. Скачайте WORD и конвертируйте самостоятельно.
        </p>
      </template>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, defineComponent, h } from 'vue'

const IconDownload = defineComponent({
  render: () => h('svg', { width: 13, height: 13, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2.2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
    h('path', { d: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4' }),
    h('polyline', { points: '7 10 12 15 17 10' }),
    h('line', { x1: '12', y1: '15', x2: '12', y2: '3' }),
  ]),
})

const props = defineProps({
  cert: { type: Object, default: null },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const pdfAvailable = computed(() => page.props.pdfAvailable)

const form = useForm({
  cert_number:  props.cert?.cert_number  ?? 'VM-07-26-',
  zavod_number: props.cert?.zavod_number ?? '',
  type_model:   props.cert?.type_model   ?? '',
  make_year:    props.cert?.make_year    ?? '2019г.',
  fio:          props.cert?.fio          ?? '',
  address:      props.cert?.address      ?? '',
  phone:        props.cert?.phone        ?? '',
  water_data:   props.cert?.water_data   ?? '',
  class:        props.cert?.class        ?? 'В',
  check_date:   props.cert?.check_date   ?? '',
  plomb_number: props.cert?.plomb_number ?? '',
})

const finalDate = computed(() => {
  const match = form.check_date?.match(/^(\d{2})\.(\d{2})\.(\d{4})$/)
  return match ? `${match[1]}.${match[2]}.${parseInt(match[3]) + 5}` : ''
})

function formatDate(e) {
  let digits = e.target.value.replace(/\D/g, '')
  let masked = ''
  if (digits.length > 0) masked += digits.substring(0, 2)
  if (digits.length > 2) masked += '.' + digits.substring(2, 4)
  if (digits.length > 4) masked += '.' + digits.substring(4, 8)
  form.check_date = masked
}

function submit() {
  if (props.cert) {
    form.put(`/certificate/${props.cert.id}`)
  } else {
    form.post('/certificate')
  }
}
</script>
