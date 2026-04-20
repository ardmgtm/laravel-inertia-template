<template>
  <div class="rounded-lg border border-gray-300 p-4 flex items-center justify-between">
    <div>
      <div class="text-primary text-xs uppercase font-bold tracking-wider">Total User</div>
      <div class="text-3xl font-bold mt-2">{{ displayCount }}</div>
    </div>
    <div class="bg-primary rounded-full p-3 flex items-center justify-center h-10 w-10">
      <i class="pi pi-users text-2xl text-white"></i>
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
