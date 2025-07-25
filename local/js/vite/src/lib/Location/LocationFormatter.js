class BitrixLocationFormatter {
    static format(item, pathItems) {
        return {
            code: item.CODE,
            typeId: item.TYPE_ID,
            value: item.VALUE,
            path: item.PATH,
            pathFormatted: this.getFullPath(item.PATH, pathItems),
            display: item.DISPLAY,
            isParent: item.IS_PARENT || false,
        }
    }

    static getFullPath(path, pathItems) {
        return path.map(id => pathItems[id]?.DISPLAY || id).join(', ');
    }
}

class YandexLocationFormatter {
    static format(item) {
        return {
            display: item.displayName,
            value: item.value,
        }
    }
}

class DaDataLocationFormatter {
    static format(item) {
        const data = item.data;
        return {
            display: item.value,
            value: item.value,
            postalCode: data.postal_code,
            coords: [
                data.geo_lat,
                data.geo_lon,
            ]
        }
    }
}

export { BitrixLocationFormatter, YandexLocationFormatter, DaDataLocationFormatter }