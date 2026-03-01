class DaDataClient {
    constructor() {
        this.apiKey = import.meta.env.VITE_DADATA_API_KEY;
        this.baseUrl = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs';
    }

    async fetchHelper(endpoint, body) {
        const response = await fetch(`${this.baseUrl}${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Token ${this.apiKey}`
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Dadata API error: ${response.status} ${errorText}`);
        }

        return response.json();
    }

    async getAddressByQuery(query, count) {
        const endpoint = '/suggest/address';
        const body = {
            query,
            count
        };
        const result = await this.fetchHelper(endpoint, body);
        if (!result.suggestions || !result.suggestions.length) {
            return [];
        }
        return result;
    }
}

export default DaDataClient