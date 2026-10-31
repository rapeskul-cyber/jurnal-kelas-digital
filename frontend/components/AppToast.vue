<template>
  <Teleport to="body">
    <div
      aria-live="polite"
      aria-atomic="false"
      class="fixed bottom-5 right-4 sm:right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none"
      style="max-width: 360px; width: calc(100vw - 32px);"
    >
      <TransitionGroup name="toast" tag="div" class="flex flex-col gap-2.5">
        <div
          v-for="toast in toastStore.toasts"
          :key="toast.id"
          class="flex items-start gap-3 px-4 py-3.5 rounded-2xl shadow-lg border pointer-events-auto cursor-pointer select-none backdrop-blur-sm transition-all"
          :class="toastClasses(toast.type)"
          @click="toastStore.remove(toast.id)"
          role="alert"
        >
          <!-- Icon -->
          <span class="text-lg flex-shrink-0 leading-none mt-0.5">{{ toastIcon(toast.type) }}</span>

          <!-- Message -->
          <p class="text-xs font-semibold leading-snug flex-1">{{ toast.message }}</p>

          <!-- Close -->
          <button
            type="button"
            @click.stop="toastStore.remove(toast.id)"
            class="flex-shrink-0 opacity-50 hover:opacity-100 transition mt-0.5"
            aria-label="Tutup notifikasi"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useToastStore } from '~/stores/toast'
import type { ToastType } from '~/stores/toast'

const toastStore = useToastStore()

function toastClasses(type: ToastType) {
  switch (type) {
    case 'success':
      return 'bg-emerald-50/95 border-emerald-200 text-emerald-800'
    case 'error':
      return 'bg-rose-50/95 border-rose-200 text-rose-800'
    case 'warning':
      return 'bg-amber-50/95 border-amber-200 text-amber-800'
    case 'info':
    default:
      return 'bg-blue-50/95 border-blue-200 text-blue-800'
  }
}

function toastIcon(type: ToastType) {
  switch (type) {
    case 'success': return '✅'
    case 'error':   return '❌'
    case 'warning': return '⚠️'
    case 'info':
    default:        return 'ℹ️'
  }
}
</script>

<style scoped>
.toast-enter-active {
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-leave-active {
  transition: all 0.2s ease-in;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(40px) scale(0.95);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(40px) scale(0.95);
}
.toast-move {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
