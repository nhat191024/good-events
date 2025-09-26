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
