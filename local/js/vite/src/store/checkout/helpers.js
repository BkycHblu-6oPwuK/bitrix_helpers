import { showErrorNotification } from "@/app/notify";
import Validator from "@/lib/Validator";

export const getOrderParams = (getters) => {
    const propsIdsMap = getters.getPropIdsMap;
    const form = getters.getForm;
    const delivery = getters.getDelivery;

    const formData = new URLSearchParams();

    formData.append('signedParamsString', getters.getSignedParams);

    formData.append(`${propsIdsMap.profileId}`, getters.getProfileId);

    formData.append(`${propsIdsMap.personType}`, propsIdsMap.personTypes[getters.getPersonType.selected]);
    formData.append(`${propsIdsMap.personTypeOld}`, propsIdsMap.personTypes[getters.getPersonType.oldPersonType]);

    if (form.email) {
        formData.append(`${propsIdsMap.email}`, form.email);
    }

    formData.append(`${propsIdsMap.phone}`, form.phone);
    formData.append(`${propsIdsMap.fio}`, form.fio);
    formData.append(`${propsIdsMap.comment}`, getters.getComment);

    if (form.legalInn) {
        formData.append(`${propsIdsMap.legalInn}`, form.legalInn)
    }
    if (form.legalName) {
        formData.append(`${propsIdsMap.legalName}`, form.legallegalNameInn)
    }
    if (form.legalAddress) {
        formData.append(`${propsIdsMap.legalAddress}`, form.legalAddress)
    }
    if (form.legalAddressCheck) {
        formData.append(`${propsIdsMap.legalAddressCheck}`, form.legalAddressCheck ? 'Y' : 'N')
    }
    if (form.legalActualAddress) {
        formData.append(`${propsIdsMap.legalActualAddress}`, form.legalActualAddress)
    }

    if (getters.getActivePay) {
        formData.append(`${propsIdsMap.paySystem}`, propsIdsMap.payments[getters.getActivePay]);
    }

    formData.append(`${propsIdsMap.delivery}`, delivery.selectedId);

    const extraServices = delivery.deliveries[delivery.selectedId]?.extraServices || [];
    if (extraServices.length) {
        extraServices.forEach(service => {
            if (service.value) {
                formData.append(`${propsIdsMap.extraServices}[${delivery.selectedId}][${service.id}]`, service.value);
            }
        });
    }

    if (delivery.storeSelectedId > 0) {
        formData.append(`${propsIdsMap.shopId}`, delivery.storeSelectedId);
    }

    formData.append(`${propsIdsMap.locationType}`, 'code');
    formData.append(`${propsIdsMap.city}`, delivery.city);
    formData.append(`${propsIdsMap.location}`, delivery.location);
    formData.append(`${propsIdsMap.address}`, delivery.address);
    if (delivery.selectedPvz) {
        formData.append(`${propsIdsMap.eshoplogisticPvz}`, delivery.selectedPvz);
        //formData.append(`${propsIdsMap.eshoplogisticAddress}`, delivery.address);
    }
    if (delivery.completionDate) {
        formData.append(`${propsIdsMap.completionDate}`, delivery.completionDate);
    }
    formData.append(`${propsIdsMap.coordinates}`, delivery.coordinates.length ? delivery.coordinates.join(',') : '');
    formData.append(`${propsIdsMap.postCode}`, delivery.postCode);
    formData.append(`${propsIdsMap.distance}`, delivery.distance);
    formData.append(`${propsIdsMap.duration}`, delivery.duration);

    // formData.append(`${propsIdsMap.profileChange}`, 'Y|N');
    // formData.append(`${propsIdsMap.postCodeChanged}`, 'Y|N');
    // formData.append(`${propsIdsMap.locationModeSteps}`, '0|1');

    return formData;
};

/**
 * Обработка ошибок после запроса на сохранения заказа
 * @param {object} errors 
 */
export const processErrorsComponent = (errors) => {
    for (let key in errors) {
        let item = errors[key]
        if (Array.isArray(item)) {
            item.forEach(element => {
                showErrorNotification(element);
            })
        } else if (typeof item === 'string') {
            showErrorNotification(item);
        }
    }
}

export const validateCheckout = (getters) => {
    const form = {
        ...getters.getForm,
        ...{
            activePay: getters.getActivePay,
            deliveryId: getters.getDelivery.selectedId,
            address: getters.getDelivery.address
        }
    };
    const isLegal = getters.isLegal
    const validator = new Validator();
    const rules = {
        activePay: {
            condition: (value) => !!value,
            message: 'Выберите способ оплаты',
            showNotify: true,
        },
        deliveryId: {
            condition: (value) => !!value,
            message: 'Выберите способ доставки',
            showNotify: true,
        },
        address: {
            condition: (value) => !!value || getters.selectedOwnDelivery,
            message: 'Выберите адрес доставки',
            showNotify: true,
        },
        email: {
            condition: (value) => value && validator.validateEmail(value),
            message: 'Введите корректный email',
        },
        phone: {
            condition: (value) => validator.validatePhone(value),
            message: 'Введите корректный номер',
        },
        fio: {
            condition: (value) => !!value,
            message: 'Введите имя',
        },
        legalName: {
            condition: (value) => !isLegal || !!value,
            message: 'Введите название компании',
        },
        legalInn: {
            condition: (value) => !isLegal || !!value,
            message: 'Введите ИНН',
        },
        legalAddress: {
            condition: (value) => !isLegal || !!value,
            message: 'Введите юридический адрес',
        },
        legalActualAddress: {
            condition: (value) => !isLegal || form.legalAddressCheck || !!value,
            message: 'Введите актуальный адрес',
        },
    };

    return validator.validateForm(form, rules);
}

export const getExtraServiceTitle = (service) => {
    if (!service) {
        return '';
    }
    if (service.values?.length) {
        const serviceValue = Number(service.value)
        for (let value of service.values) {
            if (value.id === serviceValue) {
                return value.title
            }
        }
    }
    return service.title;
}

export const isExtaServiceSkipValue = (value) => {
    if (!value) {
        return false;
    }
    if (value.values?.length) {
        const serviceValue = Number(value.value)
        for (let item of value.values) {
            if (item.id === serviceValue) {
                return item.title.toLowerCase() === 'пропуск';
            }
        }
    }
    return value.title.toLowerCase() === 'пропуск';
}

export const isOwnDelivery = (delivery) => {
    return delivery?.isOwnDelivery ?? false;
}

export const isShopDelivery = (delivery) => {
    return delivery?.isStoreDelivery ?? false;
}

export const isTransportDelivery = (delivery) => {
    return delivery?.isTransport ?? false;
}