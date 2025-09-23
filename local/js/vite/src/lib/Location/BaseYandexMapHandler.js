import { wait } from "@/common/js/helpers";
import YandexLocation from "./YandexLocation";
import { showErrorNotification } from "@/app/notify";

class BaseYandexMapHandler {
    constructor(containerRef, center) {
        this.containerRef = containerRef;
        this.center = center;
        this.map = null;
        this.currentRoute = null;
        this.userRouteLength = 0;
        this.enableRouteMaker = true;
    }

    async getCoordinatesFromCityName(cityName) {
        let [coords, _] = await YandexLocation.geocode(cityName);
        return coords;
    }

    async getCoordsFromCenter(center) {
        let centerCoords;
        if (typeof center === 'string') {
            centerCoords = await this.getCoordinatesFromCityName(center);
        } else if (
            typeof center === 'object' &&
            center &&
            (center.latitude || center[0]) &&
            (center.longitude || center[1])
        ) {
            if (Array.isArray(center)) {
                centerCoords = center;
            } else {
                centerCoords = [center.latitude, center.longitude];
            }
        } else {
            throw new Error('Invalid center value');
        }
        if (!centerCoords) {
            throw new Error('Failed to determine map center.');
        }
        return centerCoords;
    }

    async getUserCoords() {
        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve([position.coords.latitude, position.coords.longitude]);
                },
                (error) => {
                    console.warn('Geolocation error:', error.message);
                    resolve(null);
                }
            );
        });
    }

    destroyMap() {
        if (this.map) {
            this.map.destroy();
            this.map = null;
            this.currentRoute = null;
            if (this.containerRef.value) {
                this.containerRef.value.innerHTML = '';
            }
        }
    }

    async initMapBase(zoom = 14, controls = []) {
        await window.ymaps.ready();
        this.center = await this.getCoordsFromCenter(this.center);
        this.map = new window.ymaps.Map(this.containerRef.value, {
            center: this.center,
            zoom,
            controls
        });
    }

    async awaitMapInit() {
        return wait(() => this.map !== null);
    }

    async buildRouteToUserCoords(coords) {
        const userCoords = await this.getUserCoords();
        if (!userCoords) {
            console.error('Не удалось получить координаты пользователя.');
            return;
        }
        this.multiRouteMaker(coords, userCoords)
    }

    multiRouteMaker(coords, from = null) {
        if(!this.enableRouteMaker) return;
        this.removeCurrentRoute();
        const multiRoute = new window.ymaps.multiRouter.MultiRoute({
            referencePoints: [from ?? this.center, coords],
            params: { results: 1 }
        }, {
            boundsAutoApply: true
        });

        this.map.geoObjects.add(multiRoute);
        this.currentRoute = multiRoute;
    }

    routeMaker(coords, from = null) {
        if(!this.enableRouteMaker) return;
        this.removeCurrentRoute();
        window.ymaps.route([from ?? this.center, coords]).then((route) => {
            this.currentRoute = route;
            this.map.geoObjects.add(route);
        }, (error) => {
            showErrorNotification('Ошибка построения маршрута: ' + error.message);
        });
    }

    removeCurrentRoute() {
        if (this.currentRoute) {
            this.map.geoObjects.remove(this.currentRoute);
            this.currentRoute = null;
        }
    }

    disableRouteMaker() {
        this.enableRouteMaker = false;
        this.removeCurrentRoute();
    }

    getDist() {
        if (!this.currentRoute) return 0;
        
        if (typeof this.currentRoute.getLength === 'function') {
            this.userRouteLength = this.currentRoute.getLength();
        } else if (this.currentRoute.getActiveRoute) {
            const activeRoute = this.currentRoute.getActiveRoute();
            if (activeRoute) {
                const distObj = activeRoute.properties.get("distance");
                this.userRouteLength = distObj ? distObj.value : 0;
            }
        }
        return this.userRouteLength || 0;
    }

    escapeHTML(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

export default BaseYandexMapHandler