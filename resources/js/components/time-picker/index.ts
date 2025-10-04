interface Time12Hour {
    h12: number;
    m: number;
    period: 'AM' | 'PM';
}

export interface Props {
    modelValue?: string | Date | null
    minuteStep?: number
    id?: string
    placeholder?: string
    commitDefaultOnMount?: boolean
    defaultTime?: string
}

export function toMinutes(val: string | Date | null | undefined): number | null {
    if (!val) return null
    if (val instanceof Date) return val.getHours() * 60 + val.getMinutes()
    if (typeof val === 'string') {
        const m = val.match(/^(\d{1,2}):(\d{2})$/)
        if (m) {
            const h = Number(m[1]); const mi = Number(m[2])
            if (h >= 0 && h < 24 && mi >= 0 && mi < 60) return h * 60 + mi
        }
    }
    return null
}

export function fmt24(mins: number): string {
    const h = String(Math.floor(mins / 60)).padStart(2, '0')
    const m = String(mins % 60).padStart(2, '0')
    return `${h}:${m}`
}

export function to12h(mins: number): Time12Hour {
    let h24 = Math.floor(mins / 60)
    const m = mins % 60
    const period = h24 >= 12 ? 'PM' : 'AM'
    let h12 = h24 % 12
    if (h12 === 0) h12 = 12
    return { h12, m, period }
}

export function from12h(h12: number, m: number, period: 'AM' | 'PM'): number {
    let h24 = h12 % 12
    if (period === 'PM') h24 += 12
    return h24 * 60 + m
}
