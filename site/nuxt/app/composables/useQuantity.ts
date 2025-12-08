/**
 * Композабл для управления количеством
 */
export function useQuantity() {
    const quantity = ref(1)

    const incrementQuantity = () => {
        quantity.value++
    }

    const decrementQuantity = () => {
        if (quantity.value > 1) {
            quantity.value--
        }
    }

    const setQuantity = (value: number) => {
        if (value >= 1) {
            quantity.value = value
        }
    }

    return {
        quantity,
        incrementQuantity,
        decrementQuantity,
        setQuantity,
    }
}
