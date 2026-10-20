<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold select-none',
      badgeClass
    ]"
  >
    <span v-if="hasDot" :class="['w-1.5 h-1.5 rounded-full mr-1.5', dotClass]"></span>
    {{ labelText }}
  </span>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    status: string
    hasDot?: boolean
  }>(),
  {
    hasDot: false
  }
)

const badgeClass = computed(() => {
  const s = props.status?.toUpperCase() || ''
  switch (s) {
    case 'HADIR':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    case 'SAKIT':
      return 'bg-orange-50 text-orange-700 border border-orange-200'
    case 'IZIN':
      return 'bg-blue-50 text-blue-700 border border-blue-200'
    case 'ALPHA':
      return 'bg-red-50 text-red-700 border border-red-200'
    case 'DISPENSASI':
      return 'bg-purple-50 text-purple-700 border border-purple-200'
    case 'TERVERIFIKASI':
    case 'APPROVED':
    case 'DISETUJUI':
    case 'SUDAH DIISI':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    case 'PENDING':
    case 'MENUNGGU':
    case 'MENUNGGU KONFIRMASI':
      return 'bg-amber-50 text-amber-700 border border-amber-200'
    case 'BELUM_KONFIRMASI':
    case 'BELUM DIISI':
    case 'REJECTED':
    case 'DITOLAK':
      return 'bg-red-50 text-red-700 border border-red-200'
    default:
      return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
})

const dotClass = computed(() => {
  const s = props.status?.toUpperCase() || ''
  switch (s) {
    case 'HADIR':
    case 'TERVERIFIKASI':
    case 'APPROVED':
      return 'bg-emerald-500'
    case 'SAKIT':
    case 'PENDING':
      return 'bg-amber-500'
    case 'IZIN':
      return 'bg-blue-500'
    case 'ALPHA':
    case 'REJECTED':
      return 'bg-red-500'
    default:
      return 'bg-gray-400'
  }
})

const labelText = computed(() => {
  const s = props.status || ''
  if (s === 'APPROVED') return 'Disetujui'
  if (s === 'REJECTED') return 'Ditolak'
  if (s === 'PENDING') return 'Menunggu Konfirmasi Guru Pengajar'
  if (s === 'TERVERIFIKASI') return 'Terverifikasi'
  if (s === 'BELUM_KONFIRMASI') return 'Menunggu Konfirmasi Guru'
  if (s === 'MENUNGGU' || s === 'MENUNGGU KONFIRMASI') return 'Menunggu Konfirmasi Guru Pengajar'
  return s
})
</script>
