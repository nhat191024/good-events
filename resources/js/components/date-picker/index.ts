export function fmtDDMMYYYY(d: Date): string {
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
}

export function toDate(val: unknown): Date | null {
    if (!val) return null;
    if (val instanceof Date) return isNaN(val.getTime()) ? null : val;

    if (typeof val === 'string') {
        // dd/mm/yyyy
        const m1 = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (m1) {
            const [, dd, mm, yyyy] = m1;
            const d = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
            return isNaN(d.getTime()) ? null : d;
        }
        // yyyy-mm-dd
        const m2 = val.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (m2) {
            const [, yyyy, mm, dd] = m2;
            const d = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
            return isNaN(d.getTime()) ? null : d;
        }
        const d = new Date(val);
        return isNaN(d.getTime()) ? null : d;
    }
    return null;
}
