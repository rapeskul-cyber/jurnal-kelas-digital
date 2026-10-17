<template>
  <div class="min-h-screen bg-[#FAFAFA] flex flex-col antialiased">
    <!-- Desktop Sidebar -->
    <div class="hidden md:block">
      <AppSidebar />
    </div>

    <!-- Mobile Navigation -->
    <MobileNav />

    <!-- Main Content Area -->
    <div class="flex-1 md:pl-64 pt-16 md:pt-0 pb-20 md:pb-8 flex flex-col">
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
