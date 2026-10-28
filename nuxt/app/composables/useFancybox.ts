import type { FancyboxOptions } from '@fancyapps/ui';

/**
 * Подключает Fancybox к элементам с data-fancybox="<group>".
 * Инициализация только на клиенте, после монтирования.
 */
export function useFancybox(group: string, options: Partial<FancyboxOptions> | undefined = {Hash: false}) {
    const selector = `[data-fancybox="${group}"]`;
    let fancybox: typeof import('@fancyapps/ui').Fancybox | null = null;

    onMounted(async () => {
        const { Fancybox } = await import('@fancyapps/ui');
        fancybox = Fancybox;
        Fancybox.bind(selector, options);
    });

    onBeforeUnmount(() => {
        if (!fancybox) return;
        fancybox.unbind(selector);
        fancybox.close();
    });
}