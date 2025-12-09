import { defineStore } from 'pinia';
import type { FavouriteResponse, FavouriteToggleResponse } from '~/types/favourite';

export const useFavouriteStore = defineStore('favourite', {
    state: () => ({
        items: [] as number[],
        count: 0 as number,
        loading: false as boolean,
        initialized: false as boolean,
    }),

    getters: {
        isFavourite: (state) => (id: number) => state.items.includes(id),
    },

    actions: {
        async load() {
            if (this.initialized || this.loading) return;

            this.loading = true;

            try {
                const { data } = await useApiFetch<FavouriteResponse>('/favorite/get');
                this.items = data?.items || [];
                this.count = data?.count || 0;
                this.initialized = true;
            } catch (e) {
                console.error('Favourite load error:', e);
            } finally {
                this.loading = false;
            }
        },

        async toggle(id: number) {
            try {
                const { data } = await useApiFetch<FavouriteToggleResponse>(
                    `/favorite/toggle/${id}`,
                    {
                        method: 'post',
                    },
                );

                if(!data) return;

                if (data.action === 'added') {
                    if (!this.items.includes(id)) this.items.push(id);
                } else {
                    this.items = this.items.filter((x) => x !== id);
                }
            } catch (e) {
                useToast().error({message: 'Ошибка при добавлении в избранное'});
            }
        },
    },
});
