<template>
  <div class="min-h-screen bg-[#FAFAFA] flex flex-col antialiased">
    <!-- Desktop Sidebar -->
    <div class="hidden md:block">
      <AppSidebar />
    </div>

    <!-- Mobile Navigation -->
    <MobileNav />

    <!-- Main Content Area -->
    <div class="flex-1 md:pl-64 pb-28 md:pb-8 flex flex-col min-w-0 max-w-full overflow-x-hidden">
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
    if (route.path !== '/' && route.path !== '/login' && !route.path.startsWith('/forgot-password')) {
      navigateTo('/login')
    }
  }
})
</script>
