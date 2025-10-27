import { ref, watch } from 'vue';
import type { Ward, WardTypeSelectBox } from '@/types/database';
import axios from 'axios';

type Options = {
  endpoint?: (provinceId: string) => string;
  cache?: boolean;
  auto?: boolean;
};

const _cache = new Map<string, WardTypeSelectBox[]>();

export function useWards(opts: Options = {}) {
  const endpointFn = opts.endpoint ?? ((pid: string) => `/api/locations/${pid}/wards`);
  const useCache = opts.cache ?? true;
  const auto = opts.auto ?? true;

  const provinceId = ref<string | null>(null);
  const wardList = ref<WardTypeSelectBox[]>([]);
  const loadingWards = ref(false);
  const wardsError = ref('');
  let lastProvinceProcessed: string | null = null;

  async function fetchWards(id: string, { force = false }: { force?: boolean } = {}) {
    if (!id) {
      wardList.value = [];
      lastProvinceProcessed = null;
      return;
    }
    if (!force && lastProvinceProcessed === id) return;
    lastProvinceProcessed = id;

    if (useCache && _cache.has(id)) {
      wardList.value = _cache.get(id)!;
      return;
    }

    loadingWards.value = true;
    wardsError.value = '';
    try {
      const response = await axios.get<Ward[]>(endpointFn(id), {
        headers: { Accept: 'application/json' },
      });
      const data = response.data;
      const options: WardTypeSelectBox[] = data.map((w) => ({ name: w.name, value: String(w.id) }));
      wardList.value = options;
      if (useCache) _cache.set(id, options);
    } catch (e) {
      console.error(e);
      wardsError.value = 'không tải được danh sách phường/xã';
      wardList.value = [];
    } finally {
      loadingWards.value = false;
    }
  }

  if (auto) {
    watch(provinceId, (id) => {
      if (id) fetchWards(id);
      else {
        wardList.value = [];
        lastProvinceProcessed = null;
      }
    });
  }

  return {
    provinceId,
    wardList,
    loadingWards,
    wardsError,
    fetchWards,
  };
}
