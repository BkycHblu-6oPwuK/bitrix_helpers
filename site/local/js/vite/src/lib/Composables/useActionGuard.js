import { ref } from 'vue'

export function useActionGuard() {
  const inProgress = ref(false)

  async function runAction(taskFn) {
    if (inProgress.value) return false
    inProgress.value = true
    try {
      await taskFn()
    } finally {
      inProgress.value = false
    }
    return true
  }

  return {
    inProgress,
    runAction,
  }
}