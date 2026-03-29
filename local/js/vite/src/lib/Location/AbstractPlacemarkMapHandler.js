import BaseYandexMapHandler from "./BaseYandexMapHandler";

class AbstractPlacemarkMapHandler extends BaseYandexMapHandler {
    constructor(containerRef, center, pointUrl) {
        super(containerRef, center);
        this.placemark = null;
        this.pointUrl = pointUrl;
        this.mapClickHandler = null;
    }

    createPlacemark(coords, properties = {}, options = {}) {
        return new window.ymaps.Placemark(
            coords,
            {
                iconCaption: properties.caption || 'Метка',
                balloonContent: properties.caption || 'Метка',
                ...properties
            },
            {
                ...options
            }
        );
    }

    async createOrUpdatePlacemark(coords, properties = {}, options = {}) {
        await this.awaitMapInit();

        const defaultProperties = {
            caption: 'Вы здесь',
            balloonContent: 'Вы здесь',
        };

        if (!this.placemark) {
            this.placemark = this.createPlacemark(coords, { ...defaultProperties, ...properties }, options);
            this.map.geoObjects.add(this.placemark);
        } else {
            this.placemark.geometry.setCoordinates(coords);
            this.placemark.properties.set({ ...defaultProperties, ...properties });
            this.placemark.options.set({ ...options });

            if (this.pointUrl) {
                this.placemark.options.set('iconImageHref', this.pointUrl);
            }
        }

        this.routeMaker(coords);
    }

    setMapClickHandler(handler) {
        this.mapClickHandler = handler;
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
        this.createOrUpdatePlacemark(coords, {caption: name});
    }
}

export default AbstractPlacemarkMapHandler