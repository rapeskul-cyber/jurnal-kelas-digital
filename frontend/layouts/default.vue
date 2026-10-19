<template>
  <div class="min-h-screen bg-[#FAFAFA] flex flex-col antialiased">
    <!-- Mobile Top Bar & Bottom Navigation -->
    <MobileNav />

    <!-- Main Content Area: Padding top 16 (4rem) untuk top bar mobile, padding bottom 24 (6rem) agar konten tidak tertutup bottom navbar -->
    <div class="flex-1 flex flex-col pt-16 pb-24">
      <slot />
    </div>
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
