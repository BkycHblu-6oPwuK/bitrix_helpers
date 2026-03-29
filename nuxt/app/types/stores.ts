import type { FileSrc } from "./file";
import type { LocationDTO } from "./location";

export interface PickPointDTO {
  id: number,
  title: string,
  address: string,
  description: string,
  imageSrc: FileSrc,
  phone: string,
  email: string,
  schedule: string,
  location: LocationDTO
  issuingCenter: boolean,
  isDefault: boolean,
  isShippingCenter: boolean,
}