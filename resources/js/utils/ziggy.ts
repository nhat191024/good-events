import { route as ziggyRoute, Config } from 'ziggy-js';
import { usePage } from '@inertiajs/vue3';

export function route(
    name?: string,
    params?: any,
    absolute?: boolean,
    config?: Config,
): any {
    const page = usePage();
    const ziggyConfig = (page?.props?.ziggy as Config) || config;

    if (typeof window === 'undefined') {
        // @ts-ignore
        return ziggyRoute(name, params, absolute, ziggyConfig);
    }

    // @ts-ignore
    return ziggyRoute(name, params, absolute, config);
}
