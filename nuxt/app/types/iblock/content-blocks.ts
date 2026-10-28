/**
 * Типы для блоков спринт-редактора на контент-страницах
 */

import type { PageData } from "../page";

// ── примитивные блоки ────────────────────────────────────────────────────────

export interface ContentBlockText {
    type: 'text' | 'textfield' | 'selectfield'
    content: { value: string }
}

export interface ContentBlockHtag {
    type: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6'
    content: { value: string; anchor: string }
}

export interface ContentBlockImage {
    type: 'image'
    content: {
        SRC: string
        WIDTH: number
        HEIGHT: number
        DESCRIPTION: string
    }
}

export interface ContentBlockGallery {
    type: 'gallery'
    content: {
        images: Array<{
            SRC: string
            DETAIL_SRC: string
            DESCRIPTION: string
        }>
    }
}

export interface ContentBlockVideo {
    type: 'video'
    content: { url: string; id: string; type: string }
}

export interface ContentBlockTable {
    type: 'table'
    content: { rows: any[][] }
}

export interface ContentBlockLists {
    type: 'lists'
    content: { tag: 'ul' | 'ol'; value: string }
}

export interface ContentBlockButtonLink {
    type: 'button_link'
    content: { url: string; title: string; target: string }
}

export interface ContentBlockFiles {
    type: 'files'
    content: {
        files: Array<{ name: string; src: string; desc: string }>
    }
}

export interface ContentBlockSelectfield {
    type: 'selectfield'
    content: { value: string }
}

// ── блоки с вложенностью ─────────────────────────────────────────────────────

export interface ContentBlockAccordion {
    type: 'accordion'
    content: {
        items: Array<{ title: string; blocks: ContentBlock[] }>
    }
}

export interface ContentBlockContainer {
    type: 'container'
    content: { blocks: ContentBlock[] }
}

export interface ContentBlockComplexImageText {
    type: 'complex_image_text'
    content: {
        image: { SRC: string; WIDTH: number; HEIGHT: number; DESCRIPTION: string }
        text: string
    }
}

export interface ContentBlockComplexVideoText {
    type: 'complex_video_text'
    content: {
        video: { url: string; id: string; type: string }
        text: string
    }
}

/** Сырые данные блока спринт-редактора (для вложенных структур) */
export type RawSprintBlock = Record<string, any>

export interface ContentBlockContents {
    type: 'contents'
    content: {
        items: Array<{ text: string; anchor: string; level: number }>
    }
}

export interface ContentBlockProperties {
    type: 'properties'
    content: {
        elements: Array<{ title: string; text: string }>
    }
}

export interface ContentBlockVideoGallery {
    type: 'video_gallery'
    content: {
        items: Array<{ youtubeCode: string; src: string; detailSrc: string; description: string }>
    }
}

export interface ContentBlockYandexMap {
    type: 'yandex_map'
    content: { mapData: any }
}

export interface ContentBlockEmbed {
    type: 'instagram' | 'twitter'
    content: { html: string }
}

// ── инфоблоки ────────────────────────────────────────────────────────────────

export interface ContentBlockIblockElements {
    type: 'iblock_elements'
    content: {
        items: Array<{ id: string; name: string; url: string }>
    }
}

export interface ContentBlockIblockSections {
    type: 'iblock_sections'
    content: {
        items: Array<{ id: string; name: string; url: string }>
    }
}

export interface ContentBlockProducts {
    type: 'products'
    content: {
        title: string
        text: string
        linkToAll: string
        items: any[]
    }
}

// ── union-тип ────────────────────────────────────────────────────────────────

export type ContentBlock =
    | ContentBlockText
    | ContentBlockHtag
    | ContentBlockImage
    | ContentBlockGallery
    | ContentBlockVideo
    | ContentBlockTable
    | ContentBlockLists
    | ContentBlockButtonLink
    | ContentBlockFiles
    | ContentBlockSelectfield
    | ContentBlockAccordion
    | ContentBlockContainer
    | ContentBlockComplexImageText
    | ContentBlockComplexVideoText
    | ContentBlockContents
    | ContentBlockProperties
    | ContentBlockVideoGallery
    | ContentBlockYandexMap
    | ContentBlockEmbed
    | ContentBlockIblockElements
    | ContentBlockIblockSections
    | ContentBlockProducts

export type ContentPageResponse = PageData<ContentBlock[]>
