import { defineStore } from 'pinia'
import type { MenuData, MenuItem } from '~/types/menu'

export const useMenuStore = defineStore('menu', {
    state: () => ({
        menu: {
            top: null as MenuItem[] | null,
            bottom: null as MenuItem[] | null,
            catalog: null as MenuItem[] | null,
        },
        loading: false,
        initialized: false,
    }),

    actions: {
        async load() {
            if (this.initialized || this.loading) return;
            this.loading = true;
            try {
                const [topRes, bottomRes, catalogRes] = await Promise.all([
                    useApiFetch<MenuData>('/get-menu/top'),
                    useApiFetch<MenuData>('/get-menu/bottom'),
                    useApiFetch<MenuData>('/get-menu/catalog'),
                ])
                this.menu.top = topRes.data?.menu ?? null
                this.menu.bottom = bottomRes.data?.menu ?? null
                this.menu.catalog = catalogRes.data?.menu ?? null
                this.initialized = true
            } catch (error) {
                console.error('Failed to load menu:', error)
            } finally {
                this.loading = false
            }
        },
    },
})
