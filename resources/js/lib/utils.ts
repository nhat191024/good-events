import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function csrf(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
}