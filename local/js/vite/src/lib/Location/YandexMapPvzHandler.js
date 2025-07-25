import BaseYandexMapHandler from "./BaseYandexMapHandler";

class YandexMapPvzHandler extends BaseYandexMapHandler {
    constructor(containerRef, center, selectPvzEmit) {
        super(containerRef, center);
        this.selectPvz = selectPvzEmit;
    }

    async initMap(pvzList) {
        return new Promise((resolve, reject) => {
            window.ymaps.ready(async () => {
                try {
                    await this.initMapBase(14, []);
                    this.updatePlacemarks(pvzList);
                    resolve();
                } catch (err) {
                    reject(err);
                }
            });
        });
    }

    updatePlacemarks(pvzList) {
        if (!this.map) return;

        if (!pvzList || pvzList.length === 0) {
            console.warn('pvzList is empty or undefined');
            return;
        }

        pvzList.forEach(pvz => {
            if (!pvz.location || !pvz.location.latitude || !pvz.location.longitude) {
                console.warn('Invalid coordinates for PVZ', pvz);
                return;
            }

            const coords = [pvz.location.latitude, pvz.location.longitude];

            const placemark = new window.ymaps.Placemark(
                coords,
                {
                    balloonContentHeader: `<b>${pvz.name}</b>`,
                    balloonContentBody: `
                        <p><b>Адрес:</b> ${pvz.address}</p>
                        <p><b>Телефон:</b> ${pvz.phone}</p>
                        <p><b>График работы:</b> ${pvz.schedule}</p>
                        <button id="selectPvz_${pvz.id}" class="select-pvz-btn">Выбрать</button>
                    `
                },
                {
                    iconLayout: 'default#imageWithContent',
                    iconImageHref: '/images/getPlacemark.svg',
                    iconImageSize: [24, 24],
                    iconImageOffset: [-12, -12],
                }
            );

            placemark.events.add('balloonopen', () => {
                setTimeout(() => {
                    const selectBtn = document.getElementById(`selectPvz_${pvz.id}`);
                    if (selectBtn) {
                        selectBtn.addEventListener('click', () => {
                            this.selectPvz(pvz);
                            placemark.balloon.close();
                        });
                    }
                }, 0);
            });

            this.map.geoObjects.add(placemark);
        });
    }
}

export default YandexMapPvzHandler;
