// simple types cho feature chat
export interface ChatUser {
    id: string
    name: string
    avatar: string
    isOnline: boolean
}

export interface ChatMessage {
    id: string
    content: string
    senderId: 'me' | 'other'
    timestamp: Date
    isRead: boolean
}

export interface ProductInfo {
    id: string
    name: string
    price: string
    image: string
}

export interface Chat {
    id: string
    user: ChatUser
    lastMessage: string
    timestamp: string
    unreadCount: number
    product?: ProductInfo
}