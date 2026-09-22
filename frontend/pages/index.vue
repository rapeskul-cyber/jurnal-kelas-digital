<script setup lang="ts">
const searchQuery = ref('')

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
  <div class="min-h-screen bg-slate-50">
    <!-- Header -->
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white">
      <div class="mx-auto flex h-16 max-w-4xl items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div>
            <h1 class="text-lg font-bold leading-tight text-slate-900">Jurnal Kelas Digital</h1>
            <p class="text-xs text-slate-500">Kelas XII IPA 1 • Tahun Ajaran 2025/2026</p>
          </div>
        </div>

        <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 active:scale-95">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span class="hidden sm:inline">Buat Jurnal</span>
        </button>
      </div>

      <!-- Search -->
      <div class="mx-auto max-w-4xl px-4 pb-4">
        <div class="relative">
          <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari jurnal, tugas, atau pengumuman..."
            class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          >
        </div>
      </div>
    </header>

    <!-- Feed -->
    <main class="mx-auto max-w-4xl px-4 py-6">
      <TransitionGroup
        name="list"
        tag="div"
        class="space-y-4"
      >
        <article
          v-for="journal in filteredJournals"
          :key="journal.id"
          class="group cursor-pointer rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md"
        >
          <div class="mb-2 flex items-center gap-2">
            <span
              class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
              :class="typeColors[journal.type]"
            >
              {{ journal.type }}
            </span>
            <span class="text-xs font-medium text-slate-500">{{ journal.subject }}</span>
          </div>

          <h2 class="text-base font-semibold text-slate-900 transition group-hover:text-indigo-600">
            {{ journal.title }}
          </h2>
          <p class="mt-1 text-sm text-slate-600">{{ journal.snippet }}</p>

          <div class="mt-3 flex flex-wrap gap-2">
            <span
              v-for="tag in journal.tags"
              :key="tag"
              class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
            >
              {{ tag }}
            </span>
          </div>

          <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
            <div class="flex items-center gap-2">
              <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">
                {{ journal.author.charAt(0) }}
              </div>
              <span class="text-xs font-medium text-slate-600">{{ journal.author }}</span>
            </div>
            <span class="text-xs text-slate-400">{{ journal.date }}</span>
          </div>
        </article>
      </TransitionGroup>

      <!-- Empty state -->
      <div
        v-if="filteredJournals.length === 0"
        class="flex flex-col items-center justify-center py-16 text-center"
      >
        <svg class="mb-4 h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-sm font-medium text-slate-600">Tidak ada jurnal ditemukan</p>
        <p class="mt-1 text-xs text-slate-400">Coba ubah kata kunci pencarian</p>
      </div>
    </main>
  </div>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
.list-move {
  transition: transform 0.3s ease;
}
</style>