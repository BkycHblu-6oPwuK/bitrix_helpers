import ResultError from "../ResultError";
import { YandexLocationFormatter } from "./LocationFormatter";

class YandexLocation {
    static async getAddressByQuery(query) {
        if (!query || query.length < 3) return [];

        const suggestions = await window.ymaps.suggest(query);
        return suggestions.map((item) => YandexLocationFormatter.format(item));
    }

    /**
     * 
     * @param {String} address 
     * @param {CallableFunction} filter - функция фильтрации геообъектов 
     * @returns 
     */
    static async geocode(address, filter = null) {
        await window.ymaps.ready();
        const res = await window.ymaps.geocode(address);
        const geoObjects = res.geoObjects;

        if (geoObjects.getLength() === 0) {
            throw new ResultError('Адрес не найден');
        }

        if (!filter) {
            filter = (obj) => {
                const country = obj.properties.get('metaDataProperty')?.GeocoderMetaData?.Address?.country_code;
                return country && country.toLowerCase() === 'ru';
            }
        }

        let selectedGeoObject = null;
        for (let i = 0; i < geoObjects.getLength(); i++) {
            const obj = geoObjects.get(i);
            if (filter(obj)) {
                selectedGeoObject = obj;
                break;
            }
        }
        if (!selectedGeoObject) {
            selectedGeoObject = geoObjects.get(0);
        }

        const coords = selectedGeoObject.geometry.getCoordinates();
        const name = selectedGeoObject.getAddressLine();

        return [coords, name];
    }

}

export default YandexLocation