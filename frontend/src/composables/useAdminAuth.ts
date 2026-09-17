import { computed, ref } from 'vue'
import {
  adminLogin,
  adminLogout,
  adminMe,
  getAdminToken,
  setAdminToken,
  clearAdminToken,
  type AdminUser,
} from '../adminApi'

const user = ref<AdminUser | null>(null)
const loaded = ref(false)

export function useAdminAuth() {
  const isAuthenticated = computed(() => getAdminToken() !== null)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const canVerify = computed(
    () => user.value?.role === 'admin' || user.value?.role === 'reviewer',
  )

  async function login(email: string, password: string): Promise<AdminUser> {
    const result = await adminLogin(email, password)
    setAdminToken(result.token)
    user.value = result.user
    loaded.value = true
    return result.user
  }

  async function logout(): Promise<void> {
    await adminLogout()
    user.value = null
    loaded.value = false
  }

  async function fetchUser(): Promise<AdminUser | null> {
    if (!getAdminToken()) return null
    if (loaded.value && user.value) return user.value
    try {
      user.value = await adminMe()
      loaded.value = true
      return user.value
    } catch {
      clearAdminToken()
      return null
    }
  }

  return { user, isAuthenticated, isAdmin, canVerify, login, logout, fetchUser }
}
