import { initSearchableSelect } from './searchable-select';

/**
 * @param {HTMLElement} root
 * @param {(url: string, options?: RequestInit) => Promise<any>} _api
 */
export function initAddressLocationFields(root, _api) {
    const cityRoot = root.querySelector('[data-city-searchable-select]');
    const districtRoot = root.querySelector('[data-district-searchable-select]');

    if (!cityRoot || !districtRoot) {
        return {
            getValues: () => ({ city_id: null, district_id: null }),
        };
    }

    let currentCityId = cityRoot.dataset.initialId || '';

    const districtSelect = initSearchableSelect(districtRoot, {
        endpoint: '/api/districts',
        queryParams: () => ({
            active_only: '1',
            ...(currentCityId ? { city_id: String(currentCityId) } : {}),
        }),
        placeholder: '輸入關鍵字搜尋區域',
        disabledPlaceholder: '請先選擇縣市',
        emptyLabel: '目前沒有可選區域',
        noResultLabel: '找不到符合的區域',
    });

    const citySelect = initSearchableSelect(cityRoot, {
        endpoint: '/api/cities',
        queryParams: { active_only: '1' },
        placeholder: '輸入關鍵字搜尋縣市',
        emptyLabel: '目前沒有可選縣市',
        noResultLabel: '找不到符合的縣市',
        onChange: (city) => {
            const nextCityId = city ? String(city.id) : '';
            const cityChanged = nextCityId !== String(currentCityId || '');
            currentCityId = nextCityId;

            if (!currentCityId) {
                districtSelect.clear();
                districtSelect.setDisabled(true);
                return;
            }

            districtSelect.setDisabled(false);

            if (cityChanged) {
                districtSelect.clear();
            }
        },
    });

    if (currentCityId) {
        districtSelect.setDisabled(false);
    } else {
        districtSelect.clear();
        districtSelect.setDisabled(true);
    }

    return {
        getValues: () => ({
            city_id: citySelect.getValue(),
            district_id: districtSelect.getValue(),
        }),
    };
}
