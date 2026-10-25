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
import { useAuthStore } from '~/stores/auth'

const props = withDefaults(
  defineProps<{
    status: string
    hasDot?: boolean
    role?: string
    context?: string
    customLabel?: string
  }>(),
  {
    hasDot: false,
    role: '',
    context: '',
    customLabel: ''
  }
)

const authStore = useAuthStore()

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
    case 'ALFA':
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
    case 'MENUNGGU KONFIRMASI GURU PENGAJAR':
    case 'MENUNGGU KONFIRMASI GURU PEMBIMBING':
    case 'MENUNGGU_PEMBIMBING':
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
    case 'SUDAH DIISI':
      return 'bg-emerald-500'
    case 'SAKIT':
    case 'PENDING':
    case 'MENUNGGU':
    case 'MENUNGGU KONFIRMASI':
    case 'MENUNGGU KONFIRMASI GURU PENGAJAR':
    case 'MENUNGGU KONFIRMASI GURU PEMBIMBING':
    case 'MENUNGGU_PEMBIMBING':
      return 'bg-amber-500'
    case 'IZIN':
      return 'bg-blue-500'
    case 'ALPHA':
    case 'ALFA':
    case 'REJECTED':
      return 'bg-red-500'
    default:
      return 'bg-gray-400'
  }
})

const labelText = computed(() => {
  if (props.customLabel) return props.customLabel
  const s = props.status || ''
  const upper = s.toUpperCase()

  if (upper === 'APPROVED' || upper === 'DISETUJUI') return 'Disetujui'
  if (upper === 'REJECTED' || upper === 'DITOLAK') return 'Ditolak'
  if (upper === 'TERVERIFIKASI') return 'Terverifikasi'
  if (upper === 'BELUM_KONFIRMASI') return 'Belum Dikonfirmasi'
  if (upper === 'SUDAH DIISI') return 'Sudah Diisi'
  if (upper === 'BELUM DIISI') return 'Belum Diisi'
  if (upper === 'ALPHA' || upper === 'ALFA') return 'ALFA'

  if (
    upper === 'MENUNGGU KONFIRMASI GURU PEMBIMBING' ||
    upper === 'MENUNGGU_PEMBIMBING'
  ) {
    return 'Menunggu Konfirmasi Guru Pembimbing'
  }

  if (upper === 'MENUNGGU KONFIRMASI GURU PENGAJAR') {
    return 'Menunggu Konfirmasi Guru Pengajar'
  }

  if (upper === 'PENDING' || upper === 'MENUNGGU KONFIRMASI' || upper === 'MENUNGGU') {
    const isPembimbing =
      props.context === 'pembimbing' ||
      props.context === 'leave' ||
      props.role === 'GURU_PEMBIMBING' ||
      authStore.user?.role === 'GURU_PEMBIMBING'

    if (isPembimbing) {
      return 'Menunggu Konfirmasi Guru Pembimbing'
    }

    return 'Menunggu Konfirmasi Guru Pengajar'
  }

  return s
})
</script>
