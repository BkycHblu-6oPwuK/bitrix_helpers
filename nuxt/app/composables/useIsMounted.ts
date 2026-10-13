export const useIsMounted = () => {
    const isMounted = ref(false);
    onMounted(() => isMounted.value = true);
    return readonly(isMounted);
};