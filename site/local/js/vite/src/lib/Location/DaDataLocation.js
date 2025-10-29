import DaDataClient from "@/api/DaData/DaDataClient";
import { DaDataLocationFormatter } from "./LocationFormatter";

class DaDataLocation {
    constructor() {
        this.client = new DaDataClient();
    }

    async getAddressByQuery(query, count = 10) {
        if (!query || query.length < 3) return [];
        try {
            const response = await this.client.getAddressByQuery(query, count);
            return response.suggestions
                .filter(item => item.data.geo_lat)
                .map(item => DaDataLocationFormatter.format(item));
        } catch (e) {
            console.error("DaData API error:", e);
            return [];
        }
    }
}

export default DaDataLocation;
