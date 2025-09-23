import { BitrixLocationFormatter } from "./LocationFormatter";

class BitrixLocation {
    constructor(items) {
        this.items = this.formatItems(items);
    }

    formatItems(items) {
        return items.map(item => BitrixLocationFormatter.format(item));
    }
}

export default BitrixLocation;
