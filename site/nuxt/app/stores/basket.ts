import { defineStore } from 'pinia'
import type { BasketApiResponse, BasketDataDTO, BasketIdsApiResponse, BasketItemDTO } from '~/types/basket'

export const useBasketStore = defineStore('basket', {
    state: () => ({
        ids: [] as number[],
        basket: null as BasketDataDTO | null,
        loading: false,
        loadingIds: false,
        initialized: false,
    }),

    getters: {
        items: (state): BasketItemDTO[] => state.basket?.items || [],
        
        totalQuantity: (state): number => 
            state.basket?.summary.totalQuantity || state.ids.length,
        
        totalPrice: (state): number => 
            state.basket?.summary.totalPrice || 0,
        
        totalPriceFormatted: (state): string => 
            state.basket?.summary.totalPriceFormatted || '',
        
        totalDiscount: (state): number => 
            state.basket?.summary.totalDiscount || 0,
        
        totalDiscountFormatted: (state): string => 
            state.basket?.summary.totalDiscountFormatted || '',
        
        coupon: (state) => state.basket?.coupon,
        
        isEmpty: (state): boolean => 
            !state.basket?.items.length,
        
        hasItems: (state): boolean => 
            !!state.basket?.items.length,
    },

    actions: {
        async fetchIds() {
            if(this.loadingIds) return;
            this.loadingIds = true
            try {
                const { data } = await useApiFetch<BasketIdsApiResponse>('/basket/get-ids')
                this.ids = data?.data?.ids || []
            } catch (e) {
                console.error('Basket fetch IDs error:', e)
            } finally {
                this.loadingIds = false
            }
        },
        async fetchBasket() {
            if(this.loading) return;
            this.loading = true

            try {
                const { data } = await useApiFetch<BasketApiResponse>('/basket/get')
                
                if (data?.page?.basket) {
                    this.basket = data.page.basket
                    this.initialized = true
                }
            } catch (e) {
                console.error('Basket fetch error:', e)
                useToast().error({ message: 'Ошибка загрузки корзины' })
            } finally {
                this.loading = false
            }
        },

        async addItem(offerId: number, quantity: number = 1) {
            try {
                const { data } = await useApiFetch<BasketApiResponse>(
                    `/basket/add/${offerId}`,
                    {
                        method: 'post',
                        body: { quantity },
                    }
                )

                if (data?.page?.basket) {
                    this.basket = data.page.basket
                    useToast().success({ message: 'Товар добавлен в корзину' })
                }
            } catch (e) {
                console.error('Add to basket error:', e)
                useToast().error({ message: 'Ошибка при добавлении в корзину' })
            }
        },

        async updateItem(offerId: number, quantity: number) {
            if (quantity <= 0) {
                await this.removeItem(offerId)
                return
            }

            try {
                const { data } = await useApiFetch<BasketApiResponse>(
                    `/basket/update/${offerId}`,
                    {
                        method: 'post',
                        body: { quantity },
                    }
                )

                if (data?.page?.basket) {
                    this.basket = data.page.basket
                }
            } catch (e) {
                console.error('Update basket error:', e)
                useToast().error({ message: 'Ошибка при обновлении количества' })
            }
        },

        async removeItem(offerId: number) {
            try {
                const { data } = await useApiFetch<BasketApiResponse>(
                    `/basket/delete/${offerId}`,
                    {
                        method: 'delete',
                    }
                )

                if (data?.page?.basket) {
                    this.basket = data.page.basket
                    useToast().success({ message: 'Товар удален из корзины' })
                }
            } catch (e) {
                console.error('Remove from basket error:', e)
                useToast().error({ message: 'Ошибка при удалении товара' })
            }
        },

        async clearBasket() {
            try {
                const { data } = await useApiFetch<BasketApiResponse>(
                    '/basket/clear',
                    {
                        method: 'delete',
                    }
                )

                if (data?.page?.basket) {
                    this.basket = data.page.basket
                    useToast().success({ message: 'Корзина очищена' })
                }
            } catch (e) {
                console.error('Clear basket error:', e)
                useToast().error({ message: 'Ошибка при очистке корзины' })
            }
        },

        async applyCoupon(coupon: string) {
            try {
                const { data } = await useApiFetch<BasketApiResponse>(
                    '/basket/apply-coupon',
                    {
                        method: 'post',
                        body: { coupon },
                    }
                )

                if (data?.page?.basket) {
                    this.basket = data.page.basket
                    useToast().success({ message: 'Купон применен' })
                }
            } catch (e) {
                console.error('Apply coupon error:', e)
                useToast().error({ message: 'Ошибка при применении купона' })
            }
        },

        async incrementItem(offerId: number) {
            const item = this.items.find(i => i.offerId === offerId)
            if (item) {
                await this.updateItem(offerId, item.quantity + 1)
            }
        },

        async decrementItem(offerId: number) {
            const item = this.items.find(i => i.offerId === offerId)
            if (item) {
                await this.updateItem(offerId, item.quantity - 1)
            }
        },
    },
})
