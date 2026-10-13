export function useQuestionForm() {
    return useForm({
        initialValues: {
            name: '',
            phone: '',
            text: '',
            consent: true
        },
        validate: (values) => {
            const errors: any = {}
            if (!values.name) errors.name = 'Введите имя'
            if (!values.phone) errors.phone = 'Введите телефон'
            if (!values.text) errors.text = 'Введите сообщение'
            if (!values.consent) errors.consent = 'Необходимо согласие'
            return Object.keys(errors).length ? errors : null
        },
        onSubmit: async (values) => {
            await useApiFetch('web-form/question', {
                method: 'post',
                body: values
            })
        }
    })
}
