<template>
  <div class="min-h-screen bg-[#FAFAFA] flex flex-col antialiased">
    <!-- Main Content Area (mobile-first: header in page, bottom nav fixed) -->
    <div class="flex-1 flex flex-col pt-16 pb-20 min-w-0 max-w-full overflow-x-hidden">
      <slot />
    </div>

    <!-- Bottom Navigation -->
    <MobileNav />
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  // If not logged in and not on login / forgot-password, redirect
  if (!authStore.isAuthenticated) {
    const route = useRoute()
    if (route.path !== '/login' && !route.path.startsWith('/forgot-password')) {
      navigateTo('/login')
    }
  }
})
</script>
