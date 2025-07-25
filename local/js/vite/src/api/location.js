import Location from "@/lib/model/Location";
import { fetchHelper } from "./helper";

/**
 * 
 * @param {string} query 
 * @returns 
 */
export const getLocation = async (query, {
    pageSize = 20,
    page = 0,
    siteId = 's1',
    language = 'ru'
}) => {
    const formData = new URLSearchParams;
    formData.append('select[1]', 'CODE');
    formData.append('select[2]', 'TYPE_ID');
    formData.append('select[VALUE]', 'ID');
    formData.append('select[DISPLAY]', 'NAME.NAME');
    formData.append('additionals[1]', 'PATH');
    formData.append('filter[=PHRASE]', query);
    formData.append('filter[=NAME.LANGUAGE_ID]', language);
    formData.append('filter[=SITE_ID]', siteId);
    formData.append('version', 2);
    formData.append('PAGE_SIZE', pageSize);
    formData.append('PAGE', page);
    const response = await fetchHelper({
        url: '/bitrix/components/bitrix/sale.location.selector.search/get.php',
        formData: formData,
        method: 'POST'
    });
    const text = await response.text();
    const result = JSON.parse(text.replace(/([{,])\s*'/g, '$1"')
    .replace(/'\s*:/g, '":')
    .replace(/:\s*'([^']+)'/g, ':"$1"')
    .replace(/:\s*'(\d+)'/g, ':$1'));
    if (!result) {
        throw new Error;
    }
    return new Location(result);
}