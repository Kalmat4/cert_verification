<template>
  <div class="w-5/5 mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">
        {{ cert ? 'Редактировать поверку' : copyFrom ? 'Новая поверка (копия)' : 'Новая поверка' }}
      </h1>
      <div class="flex items-center gap-3">
        <span v-if="cert" class="text-xs text-gray-400"># {{ cert.id }}</span>
        <button v-if="cert" type="button" @click="deleteCert"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            <path d="M9 6V4h6v2" />
          </svg>
          Удалить
        </button>
      </div>
    </div>

    <!-- Flash -->
    <div v-if="flash.success"
      class="mb-5 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
      {{ flash.success }}
    </div>

    <!-- Errors -->
    <div v-if="Object.keys(form.errors).length"
      class="mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
      <ul class="list-disc list-inside space-y-0.5">
        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
      </ul>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
      <form @submit.prevent="submit">

        <!-- ═══════════════════════════════════════════
             БЛОК 1: ПОЛЬЗОВАТЕЛЬ
        ═══════════════════════════════════════════ -->
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Пользователь</p>

        <!-- Клиент выбран / залочен -->
        <div v-if="clientLocked"
          class="mb-4 flex items-start justify-between gap-4 px-4 py-3 rounded-lg bg-blue-50 border border-blue-200">
          <div>
            <p class="text-sm font-semibold text-blue-900">{{ form.fio }}</p>
            <p class="text-xs text-blue-700 mt-0.5">{{ form.address }}</p>
            <p v-if="form.phone" class="text-xs text-blue-600">{{ form.phone }}</p>
          </div>
          <button type="button" @click="unlockClient"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap mt-0.5">
            Сменить
          </button>
        </div>

        <!-- Клиент: поиск + ввод -->
        <div v-else class="mb-4 space-y-3">
          <!-- Поиск -->
          <div class="relative">
            <input v-model="searchQuery" @input="handleSearch" type="text"
              placeholder="Поиск клиента по ФИО, телефону, адресу…"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
            <span v-if="searchLoading" class="absolute right-3 top-2.5 text-gray-400 text-xs">…</span>

            <!-- Результаты поиска -->
            <div v-if="searchResults.length"
              class="absolute z-30 left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
              <button v-for="c in searchResults" :key="c.id" type="button" @click="pickClient(c)"
                class="w-full text-left px-3 py-2.5 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                <p class="text-sm font-medium text-gray-900">{{ c.fio }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ c.address }}{{ c.phone ? ' · ' + c.phone : '' }}</p>
                <p v-if="c.meters?.length" class="text-xs text-blue-500 mt-0.5">
                  Счётчики: {{c.meters.map(m => m.zavod_number).join(', ')}}
                </p>
              </button>
            </div>
          </div>

          <p class="text-xs text-gray-400 text-center">— или заполните данные нового клиента —</p>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">ФИО</label>
            <input v-model="form.fio" type="text" placeholder="Иванов А. Г."
              class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
              :class="form.errors.fio ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Адрес</label>
              <input v-model="form.address" type="text" placeholder="г. Костанай, мкр. Береке…"
                class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                :class="form.errors.address ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Телефон</label>
              <input v-model="form.phone" type="text" placeholder="+7 (700) 000-00-00"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
            </div>
          </div>

          <button type="button" @click="confirmClient"
            class="px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            Подтвердить клиента →
          </button>
        </div>

        <!-- Поверитель -->
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Поверитель</label>
          <input v-model="form.verifier" type="text" :placeholder="FIELD_DEFAULTS.verifier"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
        </div>

        <!-- ═══════════════════════════════════════════
             БЛОК 2: ДАННЫЕ СЧЁТЧИКА (после выбора клиента)
        ═══════════════════════════════════════════ -->
        <template v-if="clientLocked">
          <hr class="border-gray-100 my-5" />

          <!-- Кнопка показать секцию счётчика -->
          <div v-if="!showMeterSection" class="text-center">
            <button type="button" @click="showMeterSection = true"
              class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
              </svg>
              Добавить данные счётчика
            </button>
          </div>

          <div v-if="showMeterSection">

            <!-- Существующие счётчики клиента -->
            <div v-if="clientMeters.length" class="mb-5">
              <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Счётчики клиента</p>
              <div class="flex flex-wrap gap-2">
                <button v-for="m in clientMeters" :key="m.id" type="button" @click="pickMeter(m)"
                  class="px-3 py-1.5 text-xs rounded-lg border transition-colors" :class="form.meter_id === m.id
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'border-gray-300 text-gray-700 hover:border-blue-400 hover:text-blue-600'">
                  {{ m.zavod_number }}{{ m.type_model ? ' · ' + m.type_model : '' }}
                </button>
                <button type="button" @click="clearMeter"
                  class="px-3 py-1.5 text-xs rounded-lg border border-dashed border-gray-300 text-gray-500 hover:border-gray-400 transition-colors">
                  + Новый счётчик
                </button>
              </div>
              <hr class="border-gray-100 mt-4" />
            </div>

            <!-- Данные счётчика -->
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Данные счётчика</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Тип / модель</label>
                <input v-model="form.type_model" type="text" placeholder="ВСХ-15"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Изготовитель</label>
                <input v-model="form.manufacturer" type="text" :placeholder="FIELD_DEFAULTS.manufacturer"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Год изготовления</label>
                <input v-model="form.make_year" type="text" :placeholder="FIELD_DEFAULTS.make_year"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Методика поверки</label>
                <input v-model="form.verification_method" type="text" :placeholder="FIELD_DEFAULTS.verification_method"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Класс</label>
                <input v-model="form.class" type="text" :placeholder="FIELD_DEFAULTS.class"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-500 transition-colors" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Номер пломбы</label>
                <input v-model="form.plomb_number" type="text" placeholder="12345"
                  class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                  :class="form.errors.plomb_number ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                  Показания счётчика (м³) <span class="font-normal text-gray-400">только число</span>
                </label>
                <input v-model="form.water_data" type="number" step="any" min="0" placeholder="0"
                  class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                  :class="form.errors.water_data ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Дата поверки</label>
                <input v-model="form.check_date" @input="formatDate" type="text" placeholder="22.04.2026" maxlength="10"
                  class="w-full px-3 py-2 border rounded-lg text-sm outline-none transition-colors"
                  :class="form.errors.check_date ? 'border-red-400' : 'border-gray-300 focus:border-blue-500'" />
                <p class="text-xs text-gray-400 mt-1">Формат: ДД.ММ.ГГГГ</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                  Действителен до <span class="font-normal text-gray-400">авто +5 лет</span>
                </label>
                <input :value="finalDate" type="text" readonly placeholder="автоматически"
                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed" />
              </div>
            </div>

            <!-- Данные о поверке (аккордеон, read-only) -->
            <div class="my-5 rounded-xl border-2 transition-colors"
              :class="showReadings ? 'border-blue-200 bg-blue-50/30' : 'border-dashed border-blue-300 bg-blue-50/50 hover:border-blue-400'">

              <button type="button" @click="showReadings = !showReadings"
                class="flex items-center justify-between w-full text-left px-4 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                    :class="showReadings ? 'bg-blue-100 text-blue-500' : 'bg-blue-200 text-blue-600'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9 11l3 3L22 4" />
                      <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
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
                  <span v-if="!form.water_data && !showReadings"
                    class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                    Не заполнено
                  </span>
                  <span v-else-if="form.readings.length > 0 && !showReadings"
                    class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">
                    Рассчитано
                  </span>
                  <svg class="w-4 h-4 text-blue-400 transition-transform duration-200 flex-shrink-0"
                    :class="showReadings ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </div>
              </button>

              <div v-show="showReadings" class="px-4 pb-4">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                  <table class="text-xs border-collapse w-full">
                    <thead>
                      <tr class="bg-gray-50 text-gray-600">
                        <th rowspan="2"
                          class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-8">№</th>
                        <th rowspan="2"
                          class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-12">DN мм
                        </th>
                        <th colspan="3" class="border border-gray-200 px-2 py-1.5 font-semibold text-center">Qmin
                          (Qнаим) 0,03 м³/ч
                        </th>
                        <th colspan="3" class="border border-gray-200 px-2 py-1.5 font-semibold text-center">1,1Qn (Qр)
                          0,12 м³/ч</th>
                        <th colspan="3" class="border border-gray-200 px-2 py-1.5 font-semibold text-center">Qmax (Qн)
                          1,5 м³/ч</th>
                        <th rowspan="2"
                          class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-16">До
                          поверки м³</th>
                        <th rowspan="2"
                          class="border border-gray-200 px-2 py-1.5 font-semibold text-center align-middle w-16">После
                          поверки м³</th>
                      </tr>
                      <tr class="bg-gray-50 text-gray-500">
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Счётчик</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Эталон</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Погреш. %</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Счётчик</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Эталон</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Погреш. %</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Счётчик</th>
                        <th class="border border-gray-200 px-1.5 py-1 font-medium text-center w-14">Эталон</th>
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
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-gray-500 text-xs">{{ i + 1 }}
                          </td>
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-600">{{ row.dn ||
                            '—' }}</td>
                          <td
                            class="border border-gray-200 px-2 py-1.5 text-center text-xs font-semibold text-gray-800">
                            {{ row.qmin_s }}</td>
                          <td
                            class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-700 bg-blue-50/50">
                            {{ row.qmin_e }}</td>
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-500 bg-gray-50">{{
                            row.qmin_p }}</td>
                          <td
                            class="border border-gray-200 px-2 py-1.5 text-center text-xs font-semibold text-gray-800">
                            {{ row.qn_s }}</td>
                          <td
                            class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-700 bg-blue-50/50">
                            {{ row.qn_e }}</td>
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-500 bg-gray-50">{{
                            row.qn_p }}</td>
                          <td
                            class="border border-gray-200 px-2 py-1.5 text-center text-xs font-semibold text-gray-800">
                            {{ row.qmax_s }}</td>
                          <td
                            class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-700 bg-blue-50/50">
                            {{ row.qmax_e }}</td>
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-500 bg-gray-50">{{
                            row.qmax_p }}</td>
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-600">{{
                            row.before_val || '—' }}</td>
                          <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-600">{{
                            row.after_val || '—' }}</td>
                        </tr>
                        <tr v-if="form.readings.length === 0">
                          <td colspan="13" class="border border-gray-200 px-4 py-3 text-center text-xs text-gray-400">
                            Рассчитывается…</td>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Сохранить -->
            <button type="submit" :disabled="form.processing"
              class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
              <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                <polyline points="17 21 17 13 7 13 7 21" />
                <polyline points="7 3 7 8 15 8" />
              </svg>
              {{ form.processing ? 'Сохранение…' : 'Сохранить данные о поверке' }}
            </button>
          </div>
        </template>
      </form>

      <!-- ═══ СКАЧАТЬ (только при редактировании) ═══ -->
      <template v-if="cert">
        <hr class="border-gray-100 my-6" />
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Скачать</p>
        <div class="space-y-3">
          <div v-for="doc in downloadDocs" :key="doc.key" class="border border-gray-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-gray-500 mb-2">{{ doc.label }}</p>
            <div class="flex gap-2">
              <a :href="`/certificate/${cert.id}/${doc.wordPath}`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload /> WORD
              </a>
              <a v-if="pdfAvailable" :href="`/certificate/${cert.id}/${doc.pdfPath}`"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition-colors">
                <IconDownload /> PDF
              </a>
              <span v-else
                class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 bg-gray-100 text-gray-400 font-semibold rounded-md text-xs cursor-not-allowed"
                title="Недоступно на Windows">
                <IconDownload /> PDF
              </span>
            </div>
          </div>
        </div>
        <p v-if="!pdfAvailable" class="text-xs text-gray-400 mt-2">
          Конвертация в PDF недоступна на Windows.
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
  cert: { type: Object, default: null },
  copyFrom: { type: Object, default: null },
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const pdfAvailable = computed(() => page.props.pdfAvailable)

const src = props.cert ?? props.copyFrom

// ── Клиент ───────────────────────────────────────
const searchQuery = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
const clientLocked = ref(!!src)
const clientMeters = ref(src?.meter?.client?.meters ?? [])

// ── Счётчик / поверка ────────────────────────────
const showMeterSection = ref(!!src)
const showReadings = ref(true)

// ── Форма ────────────────────────────────────────
function todayDMY() {
  const d = new Date()
  return [
    String(d.getDate()).padStart(2, '0'),
    String(d.getMonth() + 1).padStart(2, '0'),
    d.getFullYear(),
  ].join('.')
}

const form = useForm({
  // Client — fallback через FK-колонки, если вложенный объект не доступен
  client_id: src?.meter?.client?.id ?? src?.meter?.client_id ?? null,
  fio: src?.meter?.client?.fio ?? '',
  address: src?.meter?.client?.address ?? '',
  phone: src?.meter?.client?.phone ?? '',

  // Verifier (cert-level)
  verifier: src?.verifier ?? '',

  // Meter — fallback через прямую FK-колонку cert.meter_id
  meter_id: src?.meter?.id ?? src?.meter_id ?? null,
  zavod_number: src?.meter?.zavod_number ?? '',
  type_model: src?.meter?.type_model ?? '',
  manufacturer: src?.meter?.manufacturer ?? '',
  make_year: src?.meter?.make_year ?? '',
  class: src?.meter?.class ?? '',

  // Cert
  cert_number: src?.cert_number ?? '',
  verification_method: src?.verification_method ?? '',
  plomb_number: src?.plomb_number ?? '',
  water_data: src?.water_data ?? '',
  check_date: src?.check_date ?? todayDMY(),

  readings: (src?.readings ?? []).map(r => ({ ...r })),
})

// ── Readings auto-calc ────────────────────────────

/**
 * Округляет м³ так, чтобы миллилитры (4-й знак после запятой) стали 0.
 * 100.4632 → 100.4630   (округление к ближайшему ровному литру)
 * 100.4637 → 100.4640
 */
function roundToLiter(valStr) {
  const n = parseFloat(valStr)
  return (Math.round(n * 1000) / 1000).toFixed(4)
}

/**
 * Случайная погрешность в диапазоне [minAbs, maxAbs] со случайным знаком 50/50.
 */
function randomError(minAbs, maxAbs) {
  const sign = Math.random() < 0.5 ? -1 : 1
  return sign * (minAbs + Math.random() * (maxAbs - minAbs))
}

/**
 * По объёму проливки и целевой погрешности возвращает объём эталона.
 */
function etalonVolume(deltaCounter, errorPercent) {
  if (deltaCounter <= 0) return 0
  return deltaCounter / (1 + errorPercent / 100)
}

/**
 * Фактическая погрешность по округлённым значениям, формат "+1.85" / "-0.47".
 */
function actualErrorPercent(sCounter, sEtalon, base) {
  const dCounter = parseFloat(sCounter) - base
  const dEtalon = parseFloat(sEtalon) - base
  if (dEtalon <= 0) return '0.00'
  const err = (dCounter - dEtalon) / dEtalon * 100
  return (err >= 0 ? '+' : '') + err.toFixed(2)
}

function generateReadings() {
  const w = parseFloat(String(form.water_data))
  if (isNaN(w) || w <= 0) return

  // Случайное количество литров для замера (целое число от min до max)
  function randomLiters(min, max) {
    return (min + Math.floor(Math.random() * (max - min + 1))) / 1000
  }

  // ── Qmin (первый замер, допуск ±5%) ───────────────
  // Прогоняем ровно 3 литра
  const QMIN_VOLUME = 0.0030
  const qmin_s = (w + QMIN_VOLUME).toFixed(4)

  const qmin_err_target = randomError(0.3, 4.8)
  const qmin_eVol = etalonVolume(QMIN_VOLUME, qmin_err_target)
  const qmin_e = (w + qmin_eVol).toFixed(4)
  const qmin_p = actualErrorPercent(qmin_s, qmin_e, w)

  // ── 1.1Qn (второй замер, допуск ±2%) ──────────────
  // Счётчик = эталон Qmin округлённый до литра + случайно 4-7 литров проливки
  const qn_base = parseFloat(qmin_e)
  const qn_base_rounded = parseFloat(roundToLiter(qmin_e))
  const qn_extra_volume = randomLiters(4, 7)
  const qn_s = (qn_base_rounded + qn_extra_volume).toFixed(4)
  const qn_volume = parseFloat(qn_s) - qn_base

  const qn_err_target = randomError(0.1, 1.8)
  const qn_eVol = etalonVolume(qn_volume, qn_err_target)
  const qn_e = (qn_base + qn_eVol).toFixed(4)
  const qn_p = actualErrorPercent(qn_s, qn_e, qn_base)

  // ── Qmax (третий замер, допуск ±2%) ───────────────
  const qmax_base = parseFloat(qn_e)
  const qmax_base_rounded = parseFloat(roundToLiter(qn_e))
  const qmax_extra_volume = randomLiters(4, 7)
  const qmax_s = (qmax_base_rounded + qmax_extra_volume).toFixed(4)
  const qmax_volume = parseFloat(qmax_s) - qmax_base

  const qmax_err_target = randomError(0.1, 1.8)
  const qmax_eVol = etalonVolume(qmax_volume, qmax_err_target)
  const qmax_e = (qmax_base + qmax_eVol).toFixed(4)
  const qmax_p = actualErrorPercent(qmax_s, qmax_e, qmax_base)

  form.readings = [{
    dn:         '15',
    qmin_s,     qmin_e,   qmin_p,
    qn_s,       qn_e,     qn_p,
    qmax_s,     qmax_e,   qmax_p,
    before_val: w.toFixed(4),
    after_val:  qmax_e,
  }]
}
// Флаг: пропустить следующее срабатывание watcher-а
// (когда данные вставлены из базы — не перерасчитываем)
let _skipNextGenerate = false
let _genTimer = null
watch(() => form.water_data, () => {
  clearTimeout(_genTimer)
  if (_skipNextGenerate) {
    _skipNextGenerate = false
    return
  }
  _genTimer = setTimeout(generateReadings, 600)
})

if (!props.cert && parseFloat(String(form.water_data)) > 0) {
  generateReadings()
}

// ── Client search ─────────────────────────────────
let _searchTimer = null
function handleSearch() {
  clearTimeout(_searchTimer)
  searchResults.value = []
  if (searchQuery.value.trim().length < 2) return
  _searchTimer = setTimeout(async () => {
    searchLoading.value = true
    const res = await fetch(`/api/clients?q=${encodeURIComponent(searchQuery.value.trim())}`)
    searchResults.value = await res.json()
    searchLoading.value = false
  }, 300)
}

function pickClient(client) {
  form.client_id = client.id
  form.fio = client.fio
  form.address = client.address
  form.phone = client.phone ?? ''
  clientMeters.value = client.meters ?? []
  searchResults.value = []
  searchQuery.value = ''
  clientLocked.value = true
}

function confirmClient() {
  if (!form.fio.trim()) return
  clientLocked.value = true
}

function unlockClient() {
  clientLocked.value = false
  showMeterSection.value = false
  searchResults.value = []
}

// ── Meter selection ───────────────────────────────
async function pickMeter(meter) {
  form.meter_id = meter.id
  form.zavod_number = meter.zavod_number
  form.type_model = meter.type_model ?? ''
  form.manufacturer = meter.manufacturer ?? ''
  form.make_year = meter.make_year ?? ''
  form.class = meter.class ?? ''

  try {
    const res = await fetch(`/api/meters/${meter.id}`)
    const data = await res.json()

    if (data.last_cert) {
      const c = data.last_cert
      form.cert_number = c.cert_number ?? ''
      form.verification_method = c.verification_method ?? ''
      form.plomb_number = c.plomb_number ?? ''
      // Ставим флаг ДО изменения water_data, чтобы watcher не запустил пересчёт
      _skipNextGenerate = true
      form.water_data = c.water_data ?? ''
      form.check_date = c.check_date ?? ''
      form.readings = (c.readings ?? []).map(r => ({ ...r }))
    }
  } catch (e) {
    console.error('Не удалось загрузить данные счётчика', e)
  }
}

function clearMeter() {
  form.meter_id = null
  form.zavod_number = ''
  form.type_model = ''
  form.manufacturer = ''
  form.make_year = ''
  form.class = ''
  form.cert_number = ''
  form.verification_method = ''
  form.plomb_number = ''
  form.water_data = ''
  form.check_date = todayDMY()
  form.readings = []
}

// ── Computed ──────────────────────────────────────
const finalDate = computed(() => {
  const match = form.check_date?.match(/^(\d{2})\.(\d{2})\.(\d{4})$/)
  return match ? `${match[1]}.${match[2]}.${parseInt(match[3]) + 5}` : ''
})

const downloadDocs = [
  { key: 'cert', label: 'Сертификат поверки', wordPath: 'word', pdfPath: 'pdf' },
  { key: 'protocol', label: 'Протокол', wordPath: 'protocol/word', pdfPath: 'protocol/pdf' },
  { key: 'garant', label: 'Гарантийное соглашение', wordPath: 'garant/word', pdfPath: 'garant/pdf' },
]

// ── Helpers ───────────────────────────────────────
const FIELD_DEFAULTS = {
  verifier: 'Карабаев А.',
  manufacturer: 'ООО "Телематические Решения", г. Москва, Российская Федерация',
  make_year: '2019г.',
  class: 'В',
  cert_number: 'VM-07-26-',
  verification_method: 'СТ РК 2.86-2005',
}

function suggestDefault(field) {
  if (!form[field] && FIELD_DEFAULTS[field]) {
    form[field] = FIELD_DEFAULTS[field]
  }
}

function formatDate(e) {
  let digits = e.target.value.replace(/\D/g, '')
  let masked = ''
  if (digits.length > 0) masked += digits.substring(0, 2)
  if (digits.length > 2) masked += '.' + digits.substring(2, 4)
  if (digits.length > 4) masked += '.' + digits.substring(4, 8)
  form.check_date = masked
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
