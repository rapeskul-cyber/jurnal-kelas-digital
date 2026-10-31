import { defineStore } from 'pinia'

export type ConfirmType = 'danger' | 'warning' | 'info' | 'primary'

export interface ConfirmOptions {
  title?: string
  message: string
  confirmText?: string
  cancelText?: string
  type?: ConfirmType
  icon?: string
}

export const useConfirmStore = defineStore('confirm', () => {
  const isOpen = ref(false)
  const options = ref<ConfirmOptions>({
    title: 'Konfirmasi Tindakan',
    message: '',
    confirmText: 'Lanjutkan',
    cancelText: 'Batal',
    type: 'primary',
  })

  let resolvePromise: ((value: boolean) => void) | null = null

  function ask(opts: ConfirmOptions | string): Promise<boolean> {
    if (typeof opts === 'string') {
      options.value = {
        title: 'Konfirmasi',
        message: opts,
        confirmText: 'Lanjutkan',
        cancelText: 'Batal',
        type: 'primary',
      }
    } else {
      options.value = {
        title: opts.title || (opts.type === 'danger' ? 'Hapus Data?' : 'Konfirmasi Tindakan'),
        message: opts.message,
        confirmText: opts.confirmText || (opts.type === 'danger' ? 'Ya, Hapus' : 'Lanjutkan'),
        cancelText: opts.cancelText || 'Batal',
        type: opts.type || 'primary',
        icon: opts.icon,
      }
    }

    isOpen.value = true

    return new Promise<boolean>((resolve) => {
      resolvePromise = resolve
    })
  }

  function confirm() {
    isOpen.value = false
    if (resolvePromise) {
      resolvePromise(true)
      resolvePromise = null
    }
  }

  function cancel() {
    isOpen.value = false
    if (resolvePromise) {
      resolvePromise(false)
      resolvePromise = null
    }
  }

  return {
    isOpen,
    options,
    ask,
    confirm,
    cancel,
  }
})
