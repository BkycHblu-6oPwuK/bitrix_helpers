import { getEshopLogisticClient } from "@/api/order";
import { ref } from "vue";
import { showErrorNotification } from "../notify";

export const formattedDate = (date) => {
    if (date instanceof Date) {
        return date.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }
    return date;
};

export function useEshopLogisticClientData() {
    const mapClientData = ref(null)
    const getClientMapData = async () => {
        try {
            const result = await getEshopLogisticClient()
            mapClientData.value = result.data
        } catch (error) {
            showErrorNotification('Произошла непредвиденная ошибка, попробуйте позже')
            console.error(error)
        }
    }

    return {
        mapClientData,
        getClientMapData,
    }
}