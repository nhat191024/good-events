export interface User {
    id: number
    name: string
}

export interface Participant {
    id: number
    name: string
}

export interface LatestMessage {
    body: string | null
    type: MessageType
    attachments: MessageAttachment[]
    location: MessageLocation | null
    preview_text: string
    created_at: string
}

export interface Thread {
    id: number
    subject: string
    updated_at: string
    is_unread: boolean
    other_participants: Participant[]
    participants: Participant[]
    latest_message: LatestMessage | null
    bill: Bill | null
}

export interface Message {
    id: number
    thread_id: number
    user_id: number
    type: MessageType
    body: string | null
    attachments: MessageAttachment[]
    location: MessageLocation | null
    preview_text: string
    created_at: string
    updated_at: string
    user: User
}

export type MessageType = 'text' | 'image' | 'location'

export interface MessageAttachment {
    id: number
    name: string
    file_name: string
    mime_type: string | null
    size: number
    url: string
}

export interface MessageLocation {
    latitude: number
    longitude: number
    label: string | null
    address: string | null
}

export interface Bill {
    id: number
    code?: string
    event_name: string | null
    datetime: string | null
    address: string | null
}

export interface ThreadDetail {
    id: number
    subject: string
    updated_at: string
    is_unread: boolean
    other_participants: Participant[]
    participants: Participant[]
    bill: Bill | null
}

export interface BroadcastMessagePayload {
    sender_id: number
    message: {
        id: number
        thread_id: number
        user_id: number
        type: MessageType
        body: string | null
        attachments: MessageAttachment[]
        location: MessageLocation | null
        preview_text: string
        created_at: string
        updated_at: string
    }
    user: {
        id: number
        name: string
    }
}
