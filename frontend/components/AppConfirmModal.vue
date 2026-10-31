<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div
        v-if="confirmStore.isOpen"
        class="fixed inset-0 z-[10000] flex items-end sm:items-center justify-center p-0 sm:p-6 overflow-y-auto"
        @keydown.esc="confirmStore.cancel()"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
          @click="confirmStore.cancel()"
        ></div>

        <!-- Dialog Box (Bottom Sheet on Mobile, Centered Dialog on Desktop) -->
        <div
          role="dialog"
          aria-modal="true"
          class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-gray-150 p-6 sm:p-7 z-10 overflow-hidden transform transition-all pb-8 sm:pb-7 pb-safe"
        >
          <!-- Top drag handle on mobile -->
          <div class="sm:hidden w-10 h-1 bg-gray-300 rounded-full mx-auto -mt-2 mb-4"></div>

          <!-- Top subtle ambient highlight -->
          <div
            class="absolute top-0 left-0 right-0 h-1.5"
            :class="{
              'bg-gradient-to-r from-rose-500 via-rose-600 to-rose-500': confirmStore.options.type === 'danger',
              'bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400': confirmStore.options.type === 'warning',
              'bg-gradient-to-r from-primary via-blue-600 to-primary': !confirmStore.options.type || confirmStore.options.type === 'primary' || confirmStore.options.type === 'info',
            }"
          ></div>

          <!-- Close Icon (X) -->
          <button
            type="button"
            @click="confirmStore.cancel()"
            class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer"
            aria-label="Tutup"
          >
            <X class="w-4 h-4" />
          </button>

          <!-- Icon Header -->
          <div class="mb-4">
            <div
              v-if="confirmStore.options.type === 'danger'"
              class="w-13 h-13 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-600 flex items-center justify-center shadow-2xs"
            >
              <Trash2 class="w-6 h-6 stroke-[2.2]" />
            </div>

            <div
              v-else-if="confirmStore.options.type === 'warning'"
              class="w-13 h-13 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-600 flex items-center justify-center shadow-2xs"
            >
              <AlertTriangle class="w-6 h-6 stroke-[2.2]" />
            </div>

            <div
              v-else
              class="w-13 h-13 rounded-2xl bg-blue-50 border border-blue-200/80 text-primary flex items-center justify-center shadow-2xs"
            >
              <HelpCircle class="w-6 h-6 stroke-[2.2]" />
            </div>
          </div>

          <!-- Content -->
          <div class="space-y-2">
            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-snug">
              {{ confirmStore.options.title }}
            </h3>
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed whitespace-pre-line">
              {{ confirmStore.options.message }}
            </p>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex flex-col-reverse sm:flex-row items-center gap-2.5 pt-2">
            <button
              type="button"
              @click="confirmStore.cancel()"
              class="w-full sm:flex-1 py-2.5 px-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs transition cursor-pointer text-center shadow-2xs hover:border-gray-300"
            >
              {{ confirmStore.options.cancelText || 'Batal' }}
            </button>

            <button
              type="button"
              @click="confirmStore.confirm()"
              class="w-full sm:flex-1 py-2.5 px-4 rounded-xl text-white font-bold text-xs shadow-md transition cursor-pointer text-center flex items-center justify-center gap-1.5"
              :class="{
                'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20 active:bg-rose-800': confirmStore.options.type === 'danger',
                'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20 active:bg-amber-800': confirmStore.options.type === 'warning',
                'bg-primary hover:bg-primary-hover shadow-primary/20 active:bg-blue-800': !confirmStore.options.type || confirmStore.options.type === 'primary' || confirmStore.options.type === 'info',
              }"
            >
              <span>{{ confirmStore.options.confirmText || 'Konfirmasi' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { Trash2, AlertTriangle, HelpCircle, X } from 'lucide-vue-next'
import { useConfirmStore } from '~/stores/confirm'

const confirmStore = useConfirmStore()
</script>

<style scoped>
.confirm-fade-enter-active,
.confirm-fade-leave-active {
  transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
  opacity: 0;
}

.confirm-fade-enter-from [role="dialog"],
.confirm-fade-leave-to [role="dialog"] {
  transform: scale(0.94) translateY(8px);
  opacity: 0;
}

.confirm-fade-enter-to [role="dialog"],
.confirm-fade-leave-from [role="dialog"] {
  transform: scale(1) translateY(0);
  opacity: 1;
}
</style>
