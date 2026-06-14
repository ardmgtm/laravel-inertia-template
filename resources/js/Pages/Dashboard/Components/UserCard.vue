<template>
  <div class="group relative rounded-xl bg-gradient-to-br from-white to-gray-50 border border-gray-200 p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-300"></div>
    
    <div class="relative flex items-center justify-between">
      <div class="flex-1">
        <div class="text-gray-500 text-xs uppercase font-semibold tracking-wider mb-1">Total User</div>
        <div class="text-4xl font-bold text-gray-800 mt-2">{{ displayCount.toLocaleString() }}</div>
        <div class="flex items-center mt-2 text-sm">
          <i class="pi pi-arrow-up text-green-500 mr-1 text-xs"></i>
          <span class="text-green-600 font-semibold">12.5%</span>
          <span class="text-gray-500 ml-1">vs last month</span>
        </div>
      </div>
      <div class="bg-gradient-to-br from-primary to-primary/80 rounded-2xl p-4 shadow-md group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
        <i class="pi pi-users text-3xl text-white"></i>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const targetCount = 1245
const displayCount = ref(0)

function animateCount(target, duration = 500) {
  const startTime = performance.now()

  function update(currentTime) {
    const elapsed = currentTime - startTime
    if (elapsed < duration) {
      displayCount.value = Math.floor((elapsed / duration) * target)
      requestAnimationFrame(update)
    } else {
      displayCount.value = target
    }
  }

  requestAnimationFrame(update)
}

onMounted(() => {
  animateCount(targetCount)
})
</script>
