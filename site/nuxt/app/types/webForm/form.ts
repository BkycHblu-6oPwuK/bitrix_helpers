export interface FormNewFieldDTO {
  id: number
  name: string
  label: string
  type: string
  required: boolean
  isMultiple: boolean
  attributes: Record<string, any>
  options: Record<string, any>
  error: string
}

export interface FormDTO {
  id: number
  title: string
  description: string
  fields: FormNewFieldDTO[]
  formIdsMap: Record<string, string>
  error: string
  successAdded: boolean
}

export interface FormStoreRequest {
  form: FormDTO
}