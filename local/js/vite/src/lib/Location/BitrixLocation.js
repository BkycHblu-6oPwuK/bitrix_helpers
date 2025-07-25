import { BitrixLocationFormatter } from "./LocationFormatter";

class BitrixLocation {
    constructor(result) {
        this.result = result.result;
        this.errors = result.errors;
        this.items = this.formatItems(result.data.ITEMS, result.data.ETC.PATH_ITEMS);
        this.pathItems = this.formatPathItems(result.data.ETC.PATH_ITEMS);
    }

    formatItems(items, pathItems) {
        return items.map(item => BitrixLocationFormatter.format(item, pathItems));
    }

    formatPathItems(pathItems) {
        let formatted = {};
        for (const key in pathItems) {
            formatted[key] = {
                code: pathItems[key].CODE,
                typeId: pathItems[key].TYPE_ID,
                display: pathItems[key].DISPLAY,
                childCount: parseInt(pathItems[key].CHILD_CNT, 10),
                value: pathItems[key].VALUE
            };
        }
        return formatted;
    }
}

export default BitrixLocation;
