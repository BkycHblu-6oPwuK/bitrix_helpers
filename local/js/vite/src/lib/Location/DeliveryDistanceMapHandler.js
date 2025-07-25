import { showErrorNotification } from "@/app/notify";
import YandexLocation from "./YandexLocation";
import AbstractPlacemarkMapHandler from "./AbstractPlacemarkMapHandler";

class DeliveryDistanceMapHandler extends AbstractPlacemarkMapHandler {
    constructor(containerRef, deliveryData, pointUrl, locationSelectEmit, routeBuildedEmit) {
        super(containerRef, deliveryData.location, pointUrl);
        this.restrictArea = deliveryData.restrictArea;
        this.maxDistance = deliveryData.maxDistance;
        this.maxDuration = deliveryData.maxDuration;
        this.from = deliveryData.from;
        this.locationSelectEmit = locationSelectEmit;
        this.routeBuildedEmit = routeBuildedEmit;
        this.mapZoom = 12;
    }

    async initMap() {
        await this.initMapBase(this.mapZoom, []);

        this.drawRestrictPolygon();

        this.fromPlacemark = new window.ymaps.Placemark(this.center, {
            iconCaption: 'Пункт отправки',
            balloonContent: 'Отсюда осуществляется доставка'
        }, {
            preset: 'islands#blueIcon'
        });

        this.map.geoObjects.add(this.fromPlacemark);

        this.map.events.add('click', (e) => {
            this.onMapClick(e);
        });
    }

    onMapClick(e) {
        const coords = e.get('coords');
        this.createOrUpdatePlacemark(coords);
    }

    drawRestrictPolygon() {
        if (!Array.isArray(this.restrictArea) || this.restrictArea.length === 0) return;

        this.restrictPolygon = new window.ymaps.Polygon(
            [this.restrictArea],
            {
                hintContent: 'Зона доставки',
                balloonContent: 'Ограниченная зона доставки',
            },
            {
                fillColor: '#00FF0033',
                strokeColor: '#00AA00',
                strokeOpacity: 0.7,
                strokeWidth: 3,
                fillOpacity: 0.4,
            }
        );

        this.restrictPolygon.events.add('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const coords = e.get('coords');
            let [_, name] = await YandexLocation.geocode(coords)
            this.createOrUpdatePlacemark(coords);
            this.locationSelectEmit(name, coords)
        });

        this.map.geoObjects.add(this.restrictPolygon);
    }

    isPointInRestrictArea(coords) {
        if (!this.restrictPolygon) return false;

        return this.restrictPolygon.geometry.contains(coords);
    }

    routeMaker(coords) {
        this.removeCurrentRoute();
        window.ymaps.route([this.center, coords]).then((route) => {
            this.currentRoute = route;
            this.map.geoObjects.add(route);

            const distance = (route.getLength() / 1000).toFixed(2);
            const duration = route.getJamsTime();
            const durationMin = Math.round(duration / 60);
            const maxDurationMin = this.maxDuration * 60;
            const readableDistance = distance + ' км';
            const readableDuration = `${durationMin} мин`;

            let isValid = true;
            if (this.maxDistance > 0 && distance > this.maxDistance) {
                isValid = false;
            }
            if (this.maxDuration > 0 && durationMin > maxDurationMin) {
                isValid = false;
            }
            if (!this.isPointInRestrictArea(coords)) {
                isValid = false;
            }

            let caption = `Расстояние: ${readableDistance}, Время: ${readableDuration}`;

            if (!isValid) {
                caption = 'Вне зоны доставки';

                if (this.placemark) {
                    this.placemark.properties.set({
                        iconCaption: '🚫 Вне зоны',
                        balloonContent: caption,
                    });
                    this.placemark.options.set('preset', 'islands#redIcon');
                }

                showErrorNotification(caption);
                this.locationSelectEmit(null);
                return;
            }

            const durationHour = (durationMin / 60).toFixed(1);
            if (this.routeBuildedEmit && distance && durationHour) {
                this.routeBuildedEmit(distance, durationHour)
            }

            if (this.placemark) {
                this.placemark.properties.set({
                    iconCaption: caption,
                    balloonContent: caption,
                });
                this.placemark.options.set('preset', 'islands#greenIcon');
            }

            const points = route.getWayPoints();
            points.each((point) => {
                const placemark = new window.ymaps.Placemark(point.geometry.coordinates, {
                    balloonContent: 'Метка на маршруте',
                }, {
                    iconLayout: 'default#image',
                    iconImageHref: this.pointUrl,
                    iconImageSize: [32, 32],
                    iconImageOffset: [-16, -32],
                });
                this.map.geoObjects.add(placemark);
            });
        }, (error) => {
            showErrorNotification('Ошибка при построении маршрута: ' + error.message);
        });
    }
}

export default DeliveryDistanceMapHandler