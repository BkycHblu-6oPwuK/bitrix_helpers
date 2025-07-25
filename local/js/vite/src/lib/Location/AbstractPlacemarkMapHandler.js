import BaseYandexMapHandler from "./BaseYandexMapHandler";

class AbstractPlacemarkMapHandler extends BaseYandexMapHandler {
    constructor(containerRef, center, pointUrl) {
        super(containerRef, center);
        this.placemark = null;
        this.pointUrl = pointUrl
    }

    createPlacemark(coords, caption = 'Метка') {
        return new window.ymaps.Placemark(coords, {
            iconCaption: caption,
            balloonContent: caption
        });
    }

    async createOrUpdatePlacemark(coords, caption = 'Вы здесь') {
        await this.awaitMapInit();
        if (!this.placemark) {
            this.placemark = this.createPlacemark(coords, caption);
            this.map.geoObjects.add(this.placemark);
        } else {
            this.placemark.geometry.setCoordinates(coords);
            this.placemark.properties.set({ iconCaption: caption, balloonContent: caption });
            if (this.pointUrl) {
                this.placemark.options.set('iconImageHref', this.pointUrl);
            }
        }

        this.routeMaker(coords);
    }

    removePlacemark() {
        if (this.placemark) {
            this.map.geoObjects.remove(this.placemark);
            this.placemark = null;
        }
    }

    addObjectToMap(obj) {
        this.map.geoObjects.add(obj);
    }

    async userAddressAdjust(input) {
        const escapedInput = this.escapeHTML(input);
        const userCoords = await this.getCoordinatesFromCityName(escapedInput);
        this.createOrUpdatePlacemark(userCoords);
    }

    async selectAddress(address, name) {
        let coords = await this.getCoordsFromCenter(address)
        this.createOrUpdatePlacemark(coords, name);
    }
}

export default AbstractPlacemarkMapHandler