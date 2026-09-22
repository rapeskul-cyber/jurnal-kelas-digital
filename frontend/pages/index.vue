<script setup lang="ts">
const searchQuery = ref('')
const searchInput = ref()

const journals = ref([
  { id: 1, title: 'Matematika - Persamaan Linear', type: 'Catatan', subject: 'Matematika', author: 'Pak Budi', date: '22 Sep 2025', tags: ['#Aljabar', '#Quiz'], snippet: 'Membahas penyelesaian persamaan linear satu variabel.' },
  { id: 2, title: 'Tugas Biologi - Sel Tumbuhan', type: 'PR', subject: 'Biologi', author: 'Bu Ani', date: '21 Sep 2025', tags: ['#Tugas', '#Sel'], snippet: 'Gambar dan jelaskan bagian-bagian sel tumbuhan.' },
  { id: 3, title: 'Pengumuman Libur Sekolah', type: 'Pengumuman', subject: 'Umum', author: 'Admin', date: '20 Sep 2025', tags: ['#Libur'], snippet: 'Diberitahukan kepada seluruh siswa bahwa sekolah libur...' }
])

const filteredJournals = computed(() => {
  if (!searchQuery.value) return journals.value
  const q = searchQuery.value.toLowerCase()
  return journals.value.filter(j => 
    j.title.toLowerCase().includes(q) || 
    j.subject.toLowerCase().includes(q)
  )
})

const typeColors: Record<string, string> = {
  'PR': 'bg-orange-50 text-orange-700 ring-orange-600/20',
  'Pengumuman': 'bg-blue-50 text-blue-700 ring-blue-600/20',
  'Catatan': 'bg-green-50 text-green-700 ring-green-600/20'
}
</script>

<template>
  <div class="flex-1 flex flex-col">
    <!-- Top Bar -->
    <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 md:px-6">
      <div class="flex items-center gap-3">
        <div class="relative hidden sm:block">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            ref="searchInput"
            type="text"
            placeholder="Cari jurnal, tugas, atau pengumuman..."
            class="h-10 w-64 rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none transition-colors"
          >
        </div>
      </div>
      <button class="flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700 transition-colors">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="hidden sm:inline">Buat Jurnal</span>
      </button>
    </header>

    <!-- Feed Content -->
    <div class="flex-1 overflow-y-auto p-4 md:p-6">
      <div class="mx-auto max-w-3xl space-y-4">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-slate-900">Feed Jurnal</h1>
          <p class="text-sm text-slate-500 mt-1">Kelas XII IPA 1 • Tahun Ajaran 2025/2026</p>
        </div>

        <transition-group name="journal-list" tag="div" class="space-y-4">
          <article
            v-for="journal in filteredJournals"
            :key="journal.id"
            class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md cursor-pointer hover:border-brand-300"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                  <span
                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                    :class="typeColors[journal.type]"
                  >
                    {{ journal.type }}
                  </span>
                  <span class="text-xs font-medium text-slate-500">{{ journal.subject }}</span>
                </div>
                <h3 class="text-base font-semibold text-slate-900 group-hover:text-brand-600 transition-colors truncate">
                  {{ journal.title }}
                </h3>
                <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ journal.snippet }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                  <span
                    v-for="tag in journal.tags"
                    :key="tag"
                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
              <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                  {{ journal.author.charAt(0) }}
                </div>
                <span class="text-xs font-medium text-slate-600">{{ journal.author }}</span>
              </div>
              <span class="text-xs text-slate-400">{{ journal.date }}</span>
            </div>
          </article>
        </transition-group>

        <div v-if="filteredJournals.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
          <svg class="h-12 w-12 text-slash-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11- +14 0 7 7 0 0114 0z" />
          </svg>
          <p class="text-sm font-medium text-slate-600">Tidak ada jurnal ditemukan</p>
          <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian</p>
</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Vue Transitions (Framer Motion equivalent) */
.journal-list-enter-active,
.journal-list-leave-active {
  transition: all 0.3s ease;
}

.journal-list-enter-from,
.journal-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.journal-list-move {
  transition: transform 0.3s ease;
}
</style>