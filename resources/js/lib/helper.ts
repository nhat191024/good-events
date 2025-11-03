/**
 * Converts a valid Date object to an ISO 8601 date string (YYYY-MM-DD).
 *
 * @param val - The value to convert. Should be a Date object.
 * @returns The ISO date string if the input is a valid Date, otherwise `null`.
 */
export function toISODate(val: unknown) {
    if (!(val instanceof Date)) return null
    if (isNaN(val.getTime())) return null
    return val.toISOString().slice(0, 10)
}

const dateDiffHours = (start: any, end: any) => {
    return (end - start) / 3_600_000;
}

export function calculateEstimatedPrice(startTime: string, endTime: string, minPrice: number, maxPrice: number) {
    const startFormatted = new Date(startTime);
    const endFormatted = new Date(endTime);

    const averagePricePerHour = (minPrice+maxPrice)/2;
    const totalHoursDiff = dateDiffHours(startFormatted, endFormatted);
    return (averagePricePerHour * totalHoursDiff);
}

export function formatDate(iso: string): string {
    const date = parseIsoToDate(iso)
    if (!date) return ''

    // thứ trong tuần
    const days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7']
    const dayName = days[date.getDay()]

    // dd/MM/yyyy
    const dd = String(date.getDate()).padStart(2, '0')
    const mm = String(date.getMonth() + 1).padStart(2, '0')
    const yyyy = date.getFullYear()

    return `${dayName}, ${dd}/${mm}/${yyyy}`
}

export function formatTime(iso: string): string {
    const date = parseIsoToDate(iso)
    if (date) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        })
    }

    const [, timePart] = iso.split('T')
    if (!timePart) return ''
    const [hh, mm] = timePart.split(':')
    return hh && mm ? `${hh}:${mm}` : ''
}

export function formatTimeRange(startIso: string, endIso: string): string {
    const start = formatTime(startIso)
    const end = formatTime(endIso)
    if (!start && !end) return ''
    if (!start) return end
    if (!end) return start
    return `${start} - ${end}`
}

export function formatPrice(value: number | string): string {
    const num = Math.floor(Number(value)) // ép về số nguyên
    return new Intl.NumberFormat('vi-VN').format(num)
}

function parseIsoToDate(iso: string): Date | null {
    if (!iso) return null
    const date = new Date(iso)
    return Number.isNaN(date.getTime()) ? null : date
}
