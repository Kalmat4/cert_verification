<template>
  <div class="w-5/5 mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">
        {{ cert ? 'Редактировать сертификат' : copyFrom ? 'Новый сертификат (копия)' : 'Новый сертификат' }}
      </h1>
      <div class="flex items-center gap-3">
        <button v-if="isLocal" type="button" @click="fillRandom"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-dashed border-amber-400 bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="16 3 21 3 21 8"/><polyline points="4 20 9 20 4 15"/>
            <path d="M21 3l-7 7M4 20l7-7"/><path d="M14 7l3-3M10 17l-3 3"/>
          </svg>
          Вставить рандомные данные
        </button>
        <span v-if="cert" class="text-xs text-gray-400"># {{ cert.id }}</span>
        <button v-if="cert" type="button" @click="deleteCert"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
          </svg>
          Удалить
        </button>
      </div>
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

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Тип / модель</label>
            <input v-model="form.type_model" type="text" placeholder="ВСХ-15"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors border-gray-300 focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Изготовитель</label>
            <input v-model="form.manufacturer" type="text"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors border-gray-300 focus:border-blue-500" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Методика поверки</label>
            <input v-model="form.verification_method" type="text"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors border-gray-300 focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Поверитель</label>
            <input v-model="form.verifier" type="text"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors border-gray-300 focus:border-blue-500" />
          </div>
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
            <label class="block text-xs font-semibold text-gray-600 mb-1">Номер телефона</label>
            <input v-model="form.phone" type="text"
              placeholder="+7 (700) 000-00-00"
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.phone ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
        </div>

        <hr class="border-gray-100 my-5" />

        <!-- Даты -->
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Даты поверки</p>

        <div class="grid grid-cols-2 gap-4 mb-5">
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

        <!-- Данные о поверке (аккордеон) -->
        <div class="my-5 rounded-xl border-2 transition-colors"
          :class="showReadings ? 'border-blue-200 bg-blue-50/30' : 'border-dashed border-blue-300 bg-blue-50/50 hover:border-blue-400'">

          <button type="button" @click="showReadings = !showReadings"
            class="flex items-center justify-between w-full text-left px-4 py-3 group">
            <div class="flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                :class="showReadings ? 'bg-blue-100 text-blue-500' : 'bg-blue-200 text-blue-600'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-blue-700">Данные о поверке</p>
                <p class="text-xs text-blue-500/80 mt-0.5">
                  <template v-if="!form.water_data">Введите показания счётчика — данные рассчитаются</template>
                  <template v-else-if="form.readings.length === 0">Рассчитывается…</template>
                  <template v-else>1 замер · нажмите чтобы {{ showReadings ? 'скрыть' : 'посмотреть' }}</template>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span v-if="form.readings.length === 0 && !showReadings"
                class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                Не заполнено
              </span>
              <span v-else-if="form.readings.length > 0 && !showReadings"
                class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">
                {{ form.readings.length }} строк
              </span>
              <svg class="w-4 h-4 text-blue-400 transition-transform duration-200 flex-shrink-0"
                :class="showReadings ? 'rotate-180' : ''"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </div>
          </button>

        <div v-show="showReadings" class="px-4 pb-4">
          <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="text-xs border-collapse w-full">
              <thead>
                <tr class="bg-gray-50 text-gray-600">
                  <th rowspan="2" class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-8">№</th>
                  <th rowspan="2" class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-12">DN мм</th>
                  <th colspan="3" class="border border-gray-200 px-2 py-1.5 font-semibold text-center">Qmin (Qнаим) 0,03 м³/ч</th>
                  <th colspan="3" class="border border-gray-200 px-2 py-1.5 font-semibold text-center">1,1Qn (Qр) 0,12 м³/ч</th>
                  <th colspan="3" class="border border-gray-200 px-2 py-1.5 font-semibold text-center">Qmax (Qн) 1,5 м³/ч</th>
                  <th rowspan="2" class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-16">До поверки м³</th>
                  <th rowspan="2" class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-16">После поверки м³</th>
                </tr>
                <tr class="bg-gray-50 text-gray-500">
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Счётчик дм³</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Эталон дм³</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Погреш. %</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Счётчик дм³</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Эталон дм³</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Погреш. %</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Счётчик дм³</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Эталон дм³</th>
                  <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Погреш. %</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!form.water_data">
                  <td colspan="13" class="border border-gray-200 px-4 py-4 text-center text-xs text-gray-400">
                    Введите показания счётчика (м³) — замеры рассчитаются автоматически
                  </td>
                </tr>
                <template v-else>
                  <tr v-for="(row, i) in form.readings" :key="i">
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-gray-500 text-xs">{{ i + 1 }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-600">{{ row.dn || '—' }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs font-semibold text-gray-800">{{ row.qmin_s }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-700 bg-blue-50/50">{{ row.qmin_e }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-500 bg-gray-50">{{ row.qmin_p }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs font-semibold text-gray-800">{{ row.qn_s }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-700 bg-blue-50/50">{{ row.qn_e }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-500 bg-gray-50">{{ row.qn_p }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs font-semibold text-gray-800">{{ row.qmax_s }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-700 bg-blue-50/50">{{ row.qmax_e }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-500 bg-gray-50">{{ row.qmax_p }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-600">{{ row.before_val || '—' }}</td>
                    <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-600">{{ row.after_val || '—' }}</td>
                  </tr>
                  <tr v-if="form.readings.length === 0">
                    <td colspan="13" class="border border-gray-200 px-4 py-3 text-center text-xs text-gray-400">
                      Рассчитывается…
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
        </div><!-- /accordion wrapper -->

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
              <a :href="`/certificate/${cert.id}/protocol/word`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload />
                WORD
              </a>
              <a v-if="pdfAvailable" :href="`/certificate/${cert.id}/protocol/pdf`"
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

          <!-- Гарантийное соглашение -->
          <div class="border border-gray-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-gray-500 mb-2">Гарантийное соглашение</p>
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
import { useForm, usePage, router } from '@inertiajs/vue3'
import { computed, defineComponent, h, ref, watch } from 'vue'

const IconDownload = defineComponent({
  render: () => h('svg', { width: 13, height: 13, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2.2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
    h('path', { d: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4' }),
    h('polyline', { points: '7 10 12 15 17 10' }),
    h('line', { x1: '12', y1: '15', x2: '12', y2: '3' }),
  ]),
})

const props = defineProps({
  cert:     { type: Object, default: null },
  copyFrom: { type: Object, default: null },
})

const page = usePage()
const flash        = computed(() => page.props.flash ?? {})
const pdfAvailable = computed(() => page.props.pdfAvailable)
const isLocal      = computed(() => page.props.isLocal)

const showReadings = ref(true)

function randomEtalonAndErr(counterVal, maxErr) {
  const sign = Math.random() > 0.4 ? 1 : -1
  const p    = sign * (Math.random() * maxErr * 0.75 + maxErr * 0.05)
  const e    = counterVal / (1 + p / 100)
  return { e: e.toFixed(4), p: (((counterVal - e) / e) * 100).toFixed(2) }
}

function ceilToThousandths(valStr) {
  const n      = parseFloat(valStr)
  const scaled = n * 1000
  const nearest = Math.round(scaled)
  const ceiled  = Math.abs(scaled - nearest) < 1e-6 ? nearest : Math.ceil(scaled)
  return (ceiled / 1000).toFixed(4)
}

function generateReadings() {
  const waterVal = parseFloat(String(form.water_data))
  if (isNaN(waterVal) || waterVal <= 0) return

  const { e: qmin_e, p: qmin_p } = randomEtalonAndErr(waterVal, 5)
  const qn_s                      = ceilToThousandths(qmin_e)
  const { e: qn_e,   p: qn_p }   = randomEtalonAndErr(parseFloat(qn_s), 2)
  const qmax_s                    = ceilToThousandths(qn_e)
  const { e: qmax_e, p: qmax_p } = randomEtalonAndErr(parseFloat(qmax_s), 2)

  form.readings = [{
    dn:         '15',
    qmin_s:     waterVal.toString(),
    qmin_e,     qmin_p,
    qn_s,       qn_e,    qn_p,
    qmax_s,     qmax_e,  qmax_p,
    before_val: waterVal.toString(),
    after_val:  qmax_e,
  }]
}

const src = props.cert ?? props.copyFrom ?? {}

const form = useForm({
  cert_number:         src.cert_number         ?? 'VM-07-26-',
  zavod_number:        src.zavod_number        ?? '',
  type_model:          src.type_model          ?? '',
  manufacturer:        src.manufacturer        ?? 'ООО "Телематические Решения", г. Москва, Российская Федерация',
  verification_method: src.verification_method ?? 'СТ РК 2.86-2005',
  verifier:            src.verifier            ?? 'Карабаев А.',
  make_year:           src.make_year           ?? '2019г.',
  fio:                 src.fio                 ?? '',
  address:             src.address             ?? '',
  phone:               src.phone               ?? '',
  water_data:          src.water_data          ?? '',
  class:               src.class               ?? 'В',
  check_date:          src.check_date          ?? '',
  plomb_number:        src.plomb_number        ?? '',
  readings: (src.readings ?? []).map(r => ({
    dn:         r.dn         ?? '',
    qmin_s:     r.qmin_s     ?? '',
    qmin_e:     r.qmin_e     ?? '',
    qmin_p:     r.qmin_p     ?? '',
    qn_s:       r.qn_s       ?? '',
    qn_e:       r.qn_e       ?? '',
    qn_p:       r.qn_p       ?? '',
    qmax_s:     r.qmax_s     ?? '',
    qmax_e:     r.qmax_e     ?? '',
    qmax_p:     r.qmax_p     ?? '',
    before_val: r.before_val ?? '',
    after_val:  r.after_val  ?? '',
  })),
})

// Пересчитываем замеры при изменении показаний счётчика
let _genTimer = null
watch(() => form.water_data, () => {
  clearTimeout(_genTimer)
  _genTimer = setTimeout(generateReadings, 600)
})

// Генерируем сразу при создании нового сертификата (не при редактировании)
if (!props.cert && parseFloat(String(form.water_data)) > 0) {
  generateReadings()
}

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

function fillRandom() {
  const rand = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min
  const pick = arr => arr[rand(0, arr.length - 1)]

  const surnames = ['Иванов', 'Петров', 'Сидоров', 'Ахметов', 'Нурмагамбетов', 'Байжанов', 'Касымов', 'Жумабеков']
  const initials = ['А. Б.', 'С. Т.', 'Д. Е.', 'К. М.', 'Н. О.', 'Р. В.', 'И. Г.', 'Ю. Л.']
  const streets  = ['мкр. Береке д. 12 кв. 5', 'мкр. Аль-Фараби д. 34 кв. 18', 'ул. Алтынсарина д. 7 кв. 3', 'мкр. Юбилейный д. 56 кв. 101', 'ул. Байтурсынова д. 9 кв. 47']
  const models   = ['ВСХ-15', 'ВСХ-20', 'ВСКМ-15', 'СХВ-15Г', 'МТК-Т-15', 'ВСГ-15']
  const classes  = ['В', 'Г', 'А']

  const day   = String(rand(1, 28)).padStart(2, '0')
  const month = String(rand(1, 12)).padStart(2, '0')
  const year  = rand(2023, 2026)
  const zavodNum    = String(rand(1000000, 9999999))
  const certSuffix  = String(rand(1000000, 9999999))
  const phone2      = String(rand(700, 777))
  const phone3      = String(rand(1000000, 9999999))

  form.cert_number         = `VM-07-${String(year).slice(2)}-${certSuffix}`
  form.zavod_number        = zavodNum
  form.type_model          = pick(models)
  form.manufacturer        = 'ООО "Телематические Решения", г. Москва, Российская Федерация'
  form.verification_method = 'СТ РК 2.86-2005'
  form.verifier            = 'Карабаев А.'
  form.make_year           = `${rand(2018, 2023)}г.`
  form.class               = pick(classes)
  form.plomb_number        = String(rand(10000, 99999))
  form.water_data          = rand(0, 9999)
  form.fio                 = `${pick(surnames)} ${pick(initials)}`
  form.address             = `г. Костанай, ${pick(streets)}`
  form.phone               = `+7 (${phone2}) ${phone3.slice(0,3)}-${phone3.slice(3,5)}-${phone3.slice(5,7)}`
  form.check_date          = `${day}.${month}.${year}`
}

function deleteCert() {
  if (!confirm(`Удалить сертификат ${form.cert_number}?\nЭто действие необратимо.`)) return
  router.delete(`/certificate/${props.cert.id}`)
}

function submit() {
  if (props.cert) {
    form.put(`/certificate/${props.cert.id}`)
  } else {
    form.post('/certificate')
  }
}
</script>
