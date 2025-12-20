export interface User {
    id: number
    name: string
}

export interface Participant {
    id: number
    name: string
}

export interface LatestMessage {
    body: string
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
    body: string
    created_at: string
    updated_at: string
    user: User
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
        body: string
        created_at: string
        updated_at: string
    }
    user: {
        id: number
        name: string
    }
}
