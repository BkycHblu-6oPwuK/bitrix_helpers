import type { FileDTO } from '~/types/file'

export const valueIsFile = (val: unknown): val is FileDTO => {
    return typeof val === 'object' && val !== null && 'src' in val
}