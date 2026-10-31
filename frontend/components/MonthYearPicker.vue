<template>
  <div class="relative inline-block text-left" ref="pickerRef">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 hover:border-gray-300 rounded-xl text-xs font-semibold text-gray-700 shadow-2xs hover:bg-gray-50 transition cursor-pointer"
      :class="{ 'ring-2 ring-primary/20 border-primary': isOpen }"
    >
      <CalendarIcon class="w-4 h-4 text-gray-400" />
      <span>{{ selectedLabel }}</span>
      <ChevronDownIcon
        class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Dropdown Popover -->
    <div
      v-if="isOpen"
      class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-150 p-4 z-50 animate-in fade-in zoom-in-95 duration-150"
    >
      <!-- Year Header -->
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
        <button
          type="button"
          @click="currentYear--"
          class="p-1 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition cursor-pointer"
          title="Tahun Sebelumnya"
        >
          <ChevronLeftIcon class="w-4 h-4" />
        </button>

        <span class="text-sm font-bold text-gray-900">{{ currentYear }}</span>

        <button
          type="button"
          @click="currentYear++"
          class="p-1 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition cursor-pointer"
          title="Tahun Berikutnya"
        >
          <ChevronRightIcon class="w-4 h-4" />
        </button>
      </div>

      <!-- Month Grid -->
      <div class="grid grid-cols-3 gap-2">
        <button
          v-for="(m, idx) in months"
          :key="idx"
          type="button"
          @click="selectMonth(idx)"
          class="py-2 text-xs font-medium rounded-xl transition cursor-pointer text-center"
          :class="
            isSelected(idx)
              ? 'bg-primary text-white font-bold shadow-xs'
              : 'text-gray-700 hover:bg-gray-100'
          "
        >
          {{ m.short }}
        </button>
      </div>

      <!-- Quick Reset to Current Month -->
      <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-[11px]">
        <button
          type="button"
          @click="resetToCurrent"
          class="text-primary font-semibold hover:underline cursor-pointer"
        >
          Bulan Ini
        </button>
        <button
          type="button"
          @click="isOpen = false"
          class="text-gray-500 hover:text-gray-700 cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  ref,
  computed,
  onMounted,
  onUnmounted,
} from 'vue'
import {
  Calendar as CalendarIcon,
  ChevronDown as ChevronDownIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
} from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    modelValue?: string // Format 'YYYY-MM', e.g. '2026-10'
  }>(),
  {
    modelValue: () => {
      const d = new Date()
      return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    }
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'change', value: { year: number; month: number; label: string; value: string }): void
}>()

const isOpen = ref(false)
const pickerRef = ref<HTMLElement | null>(null)

const months = [
  { short: 'Jan', full: 'Januari' },
  { short: 'Feb', full: 'Februari' },
  { short: 'Mar', full: 'Maret' },
  { short: 'Apr', full: 'April' },
  { short: 'Mei', full: 'Mei' },
  { short: 'Jun', full: 'Juni' },
  { short: 'Jul', full: 'Juli' },
  { short: 'Agu', full: 'Agustus' },
  { short: 'Sep', full: 'September' },
  { short: 'Okt', full: 'Oktober' },
  { short: 'Nov', full: 'November' },
  { short: 'Des', full: 'Desember' }
]

// Parse initial year & month from modelValue
const parseValue = (val?: string) => {
  if (val && val.includes('-')) {
    const [y, m] = val.split('-').map(Number)
    return { year: y || new Date().getFullYear(), month: (m ? m - 1 : new Date().getMonth()) }
  }
  const now = new Date()
  return { year: now.getFullYear(), month: now.getMonth() }
}

const initial = parseValue(props.modelValue)
const currentYear = ref(initial.year)
const selectedMonthIdx = ref(initial.month)
const selectedYear = ref(initial.year)

// Watch external modelValue changes
watch(
  () => props.modelValue,
  (newVal) => {
    const parsed = parseValue(newVal)
    currentYear.value = parsed.year
    selectedYear.value = parsed.year
    selectedMonthIdx.value = parsed.month
  }
)

const selectedLabel = computed(() => {
  const monthName = months[selectedMonthIdx.value]?.full || 'Oktober'
  return `${monthName} ${selectedYear.value}`
})

const isSelected = (monthIdx: number) => {
  return selectedMonthIdx.value === monthIdx && selectedYear.value === currentYear.value
}

const selectMonth = (monthIdx: number) => {
  selectedMonthIdx.value = monthIdx
  selectedYear.value = currentYear.value
  isOpen.value = false

  const monthNum = String(monthIdx + 1).padStart(2, '0')
  const valString = `${selectedYear.value}-${monthNum}`

  emit('update:modelValue', valString)
  emit('change', {
    year: selectedYear.value,
    month: monthIdx + 1,
    label: `${months[monthIdx].full} ${selectedYear.value}`,
    value: valString
  })
}

const resetToCurrent = () => {
  const now = new Date()
  currentYear.value = now.getFullYear()
  selectMonth(now.getMonth())
}

const handleClickOutside = (event: MouseEvent) => {
  if (pickerRef.value && !pickerRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
