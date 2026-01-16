import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

export type TutorialLink = {
    label: string;
    href: string;
    description?: string;
};

export type TutorialLinkInput = TutorialLink | string;

const tutorialLinks = ref<TutorialLink[]>([]);
const currentOwner = ref<symbol | null>(null);
const defaultTutorialLinks = ref<TutorialLink[]>([]);
const hiddenRoutes = ref<string[]>([]);

const DEFAULT_LABEL = 'Hướng dẫn';

const getDefaultHref = () => {
    try {
        if (typeof route === 'function') {
            return route('tutorial.index');
        }
    } catch (error) {
        return '/huong-dan';
    }

    return '/huong-dan';
};

const normalizeLinks = (input: TutorialLinkInput | TutorialLinkInput[]) => {
    const raw = Array.isArray(input) ? input : [input];
    const filtered = raw.filter(Boolean);
    const shouldNumber = filtered.length > 1;

    return filtered.map((link, index) => {
        if (typeof link === 'string') {
            return {
                label: shouldNumber ? `${DEFAULT_LABEL} ${index + 1}` : DEFAULT_LABEL,
                href: link,
            };
        }
        return link;
    });
};

const mergeUniqueByHref = (base: TutorialLink[], extras: TutorialLink[]) => {
    const map = new Map<string, TutorialLink>();
    [...base, ...extras].forEach((item) => {
        map.set(item.href, item);
    });
    return Array.from(map.values());
};

const normalizeHiddenRoutes = (input: string | string[]) => {
    const raw = Array.isArray(input) ? input : [input];

    return raw
        .map((item) => (typeof item === 'string' ? item.trim() : item))
        .filter((item): item is string => Boolean(item));
};

const mergeUniqueStrings = (base: string[], extras: string[]) => Array.from(new Set([...base, ...extras]));

export const setDefaultTutorialLinks = (input: TutorialLinkInput | TutorialLinkInput[]) => {
    const normalized = normalizeLinks(input);
    defaultTutorialLinks.value = normalized;

    // Seed tutorialLinks when no owner yet or merge into existing to keep defaults visible.
    if (currentOwner.value === null || tutorialLinks.value.length === 0) {
        tutorialLinks.value = [...normalized];
    } else {
        tutorialLinks.value = mergeUniqueByHref(tutorialLinks.value, normalized);
    }
};

export const clearDefaultTutorialLinks = () => {
    defaultTutorialLinks.value = [];
};

const resetTutorialLinks = () => {
    tutorialLinks.value = [];
};

const resetHiddenRoutes = () => {
    hiddenRoutes.value = [];
};

export const setTutorialHiddenRoutes = (input: string | string[]) => {
    hiddenRoutes.value = normalizeHiddenRoutes(input);
};

export const addTutorialHiddenRoutes = (input: string | string[]) => {
    hiddenRoutes.value = mergeUniqueStrings(hiddenRoutes.value, normalizeHiddenRoutes(input));
};

export const clearTutorialHiddenRoutes = () => {
    resetHiddenRoutes();
};

export const useTutorialHelper = () => {
    const owner = Symbol('tutorial-links');

    const ensureOwner = () => {
        if (currentOwner.value !== owner) {
            currentOwner.value = owner;
            tutorialLinks.value = [...defaultTutorialLinks.value];
        }
    };

    const addTutorialRoutes = (input: TutorialLinkInput | TutorialLinkInput[]) => {
        ensureOwner();
        tutorialLinks.value = [...tutorialLinks.value, ...normalizeLinks(input)];
    };

    const addTutorialRoute = (input: TutorialLinkInput | TutorialLinkInput[]) => {
        addTutorialRoutes(input);
    };

    const setTutorialRoutes = (input: TutorialLinkInput | TutorialLinkInput[]) => {
        ensureOwner();
        tutorialLinks.value = normalizeLinks(input);
    };

    const clearTutorialRoutes = () => {
        if (currentOwner.value === owner) {
            resetTutorialLinks();
        }
    };

    const addTutorialHiddenRoutesForOwner = (input: string | string[]) => {
        ensureOwner();
        addTutorialHiddenRoutes(input);
    };

    const setTutorialHiddenRoutesForOwner = (input: string | string[]) => {
        ensureOwner();
        setTutorialHiddenRoutes(input);
    };

    const clearTutorialHiddenRoutesForOwner = () => {
        resetHiddenRoutes();
    };

    onBeforeUnmount(() => {
        if (currentOwner.value === owner) {
            resetTutorialLinks();
        }
    });

    return {
        addTutorialRoute,
        addTutorialRoutes,
        clearTutorialRoutes,
        setTutorialRoutes,
        addTutorialHiddenRoutes: addTutorialHiddenRoutesForOwner,
        clearTutorialHiddenRoutes: clearTutorialHiddenRoutesForOwner,
        setTutorialHiddenRoutes: setTutorialHiddenRoutesForOwner,
    };
};

export const useTutorialLinks = () => {
    const page = usePage();

    const links = computed(() => {
        if (tutorialLinks.value.length) {
            return tutorialLinks.value;
        }
        if (defaultTutorialLinks.value.length) {
            return defaultTutorialLinks.value;
        }
        return [{ label: DEFAULT_LABEL, href: getDefaultHref() }];
    });

    const isHidden = computed(() => {
        if (!hiddenRoutes.value.length) {
            return false;
        }

        const currentUrl =
            (page && 'url' in page ? (page as any).url : '') ||
            (typeof window !== 'undefined' ? window.location.pathname : '');
        const componentName = page && 'component' in page ? (page as any).component : '';

        return hiddenRoutes.value.some((routeName) => {
            if (!routeName) {
                return false;
            }

            if (componentName && routeName === componentName) {
                return true;
            }

            if (
                currentUrl === routeName ||
                currentUrl.startsWith(`${routeName}?`) ||
                currentUrl.startsWith(`${routeName}/`)
            ) {
                return true;
            }

            try {
                if (typeof route === 'function') {
                    const ziggyRoute = route();
                    if (ziggyRoute && typeof ziggyRoute.current === 'function' && ziggyRoute.current(routeName)) {
                        return true;
                    }
                }
            } catch (error) {
                return false;
            }

            return false;
        });
    });

    return { links, isHidden };
};

export const slugifyTutorial = (value: string) =>
    value
        .toLowerCase()
        .replace(/đ/g, 'd')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

const resolveRoute = (routeName: string) => {
    try {
        if (typeof route === 'function') {
            return route(routeName);
        }
    } catch (error) {
        return getDefaultHref();
    }

    return getDefaultHref();
};

export const buildTutorialLink = (routeName: string, sectionId?: string) => {
    const base = resolveRoute(routeName);
    if (!sectionId) {
        return base;
    }
    return `${base}#${sectionId}`;
};

export const buildTutorialLinkFromTitle = (routeName: string, title: string) =>
    buildTutorialLink(routeName, slugifyTutorial(title));
