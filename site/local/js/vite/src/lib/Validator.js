import { showErrorNotification } from "@/app/notify";

class Validator {
    constructor() {
        this.errors = {};
    }

    addError(field, message) {
        this.errors[field] = message;
    }

    removeError(field) {
        delete this.errors[field];
    }

    validateField(field, value, rule) {
        if (rule.condition(value)) {
            this.removeError(field);
        } else {
            if(rule.showNotify){
                showErrorNotification(rule.message);
            } 
            this.addError(field, rule.message);
        }
    }

    validateForm(formData, rules) {
        this.errors = {}; 

        Object.keys(rules).forEach((field) => {
            const rule = rules[field];
            const value = formData[field];
            this.validateField(field, value, rule);
        });

        return this.errors;
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validatePhone(phone) {
        return phone && phone.length >= 17
    }
}

export default Validator;