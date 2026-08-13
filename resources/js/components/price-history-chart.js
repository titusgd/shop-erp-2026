const CHART_WIDTH = 640;
const CHART_HEIGHT = 220;
const PAD = { top: 20, right: 20, bottom: 36, left: 52 };
const SERIES_COLORS = [
    '#0f766e',
    '#c2410c',
    '#1d4ed8',
    '#7c3aed',
    '#be123c',
    '#0e7490',
    '#a16207',
    '#15803d',
    '#db2777',
    '#4338ca',
];

export function formatChartMoney(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) {
        return '—';
    }

    return number.toLocaleString('zh-TW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export function seriesKey(point) {
    if (point.vendorId != null && point.vendorId !== '') {
        return `id:${point.vendorId}`;
    }

    if (point.vendorName) {
        return `name:${point.vendorName}`;
    }

    return 'default';
}

function compareTime(a, b) {
    return String(a ?? '').localeCompare(String(b ?? ''));
}

function uniqueTimes(points) {
    const times = [];
    const seen = new Set();

    for (const point of points) {
        const key = point.at || '';
        if (seen.has(key)) {
            continue;
        }
        seen.add(key);
        times.push(key);
    }

    return times.sort(compareTime);
}

function sortPointsByTime(points) {
    return [...points].sort((a, b) => compareTime(a.at, b.at));
}

function groupSeries(points) {
    const order = [];
    const groups = new Map();

    for (const point of points) {
        const key = seriesKey(point);
        if (!groups.has(key)) {
            groups.set(key, {
                key,
                name: point.vendorName || '',
                points: [],
            });
            order.push(key);
        }
        groups.get(key).points.push(point);
    }

    return order.map((key, index) => ({
        ...groups.get(key),
        color: SERIES_COLORS[index % SERIES_COLORS.length],
    }));
}

function linePathFromCoords(coords) {
    return coords.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' ');
}

function areaPathFromCoords(coords, baselineY) {
    if (!coords.length) {
        return '';
    }

    const linePath = linePathFromCoords(coords);

    return `${linePath} L ${coords[coords.length - 1].x.toFixed(2)} ${baselineY.toFixed(2)} L ${coords[0].x.toFixed(2)} ${baselineY.toFixed(2)} Z`;
}

function pickXLabels(items) {
    if (items.length <= 1) {
        return items.slice(0, 1);
    }

    if (items.length === 2) {
        return [items[0], items[items.length - 1]];
    }

    return [items[0], items[Math.floor((items.length - 1) / 2)], items[items.length - 1]];
}

function bindLegendToggle(legend, onToggleSeries) {
    legend._onToggleSeries = onToggleSeries;

    if (legend.dataset.legendBound === '1') {
        return;
    }

    legend.dataset.legendBound = '1';
    legend.addEventListener('click', (event) => {
        const button = event.target.closest('[data-series-key]');
        if (!button || !legend.contains(button)) {
            return;
        }

        legend._onToggleSeries?.(button.dataset.seriesKey);
    });
}

function renderChartLegend(svg, series, { hiddenKeys, onToggleSeries } = {}) {
    const wrap = svg.closest('[data-price-histories-chart-wrap]');
    const legend = wrap?.querySelector('[data-price-histories-chart-legend]');
    if (!legend) {
        return;
    }

    const named = series.filter((item) => item.name);
    if (!named.length) {
        legend.hidden = true;
        legend.innerHTML = '';
        return;
    }

    const hidden = hiddenKeys instanceof Set ? hiddenKeys : new Set();

    legend.hidden = false;
    legend.innerHTML = named
        .map((item) => {
            const isHidden = hidden.has(item.key);
            const buttonClass = isHidden
                ? 'inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-400 transition hover:bg-slate-100'
                : 'inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50';

            return `
                <button
                    type="button"
                    data-series-key="${escapeHtml(item.key)}"
                    aria-pressed="${isHidden ? 'false' : 'true'}"
                    title="${isHidden ? '顯示' : '隱藏'}「${escapeHtml(item.name)}」"
                    class="${buttonClass}"
                >
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color:${isHidden ? '#cbd5e1' : item.color}"></span>
                    <span class="${isHidden ? 'line-through' : ''}">${escapeHtml(item.name)}</span>
                </button>
            `;
        })
        .join('');

    bindLegendToggle(legend, onToggleSeries);
}

function formatAxisDate(value, { includeTime = false } = {}) {
    const raw = String(value ?? '');
    const datePart = raw.slice(0, 10);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
        return '';
    }

    const dateLabel = `${datePart.slice(5, 7)}/${datePart.slice(8, 10)}`;
    if (!includeTime) {
        return dateLabel;
    }

    const timePart = raw.slice(11, 16);

    return timePart ? `${dateLabel} ${timePart}` : dateLabel;
}

function shouldIncludeAxisTime(times) {
    const dates = new Set(times.map((at) => String(at ?? '').slice(0, 10)).filter(Boolean));

    return dates.size > 0 && dates.size < times.length;
}

function toChartX(index, count, innerWidth) {
    if (count <= 1) {
        return PAD.left + innerWidth / 2;
    }

    return PAD.left + (index / (count - 1)) * innerWidth;
}

function toChartY(price, min, max, innerHeight) {
    if (max === min) {
        return PAD.top + innerHeight / 2;
    }

    return PAD.top + ((max - price) / (max - min)) * innerHeight;
}

function renderEmptyChart(svg, message) {
    svg.innerHTML = `
        <rect x="0" y="0" width="${CHART_WIDTH}" height="${CHART_HEIGHT}" fill="#fff" />
        <text x="${CHART_WIDTH / 2}" y="${CHART_HEIGHT / 2}" text-anchor="middle" fill="#64748b" font-size="14">
            ${escapeHtml(message)}
        </text>
    `;
}

export function drawPriceLineChart(svg, points, options = {}) {
    if (!svg) {
        return;
    }

    const hiddenKeys = options.hiddenKeys instanceof Set ? options.hiddenKeys : new Set(options.hiddenKeys ?? []);
    const onToggleSeries = options.onToggleSeries;
    const wrap = svg.closest('[data-price-histories-chart-wrap]');
    const tooltip = wrap?.querySelector('[data-price-histories-chart-tooltip]');
    if (tooltip) {
        tooltip.hidden = true;
    }
    const innerWidth = CHART_WIDTH - PAD.left - PAD.right;
    const innerHeight = CHART_HEIGHT - PAD.top - PAD.bottom;
    const allSeries = groupSeries(points);

    if (!points.length) {
        renderEmptyChart(svg, '區間內尚無價格資料');
        renderChartLegend(svg, [], { hiddenKeys, onToggleSeries });
        return;
    }

    const visibleSeriesMeta = allSeries.filter((item) => !hiddenKeys.has(item.key));
    const visiblePoints = visibleSeriesMeta.flatMap((item) => item.points);

    if (!visiblePoints.length) {
        renderEmptyChart(svg, '請點選圖例以顯示供應商');
        renderChartLegend(svg, allSeries, { hiddenKeys, onToggleSeries });
        return;
    }

    const prices = visiblePoints.map((point) => point.price);
    const dataMin = Math.min(...prices);
    const dataMax = Math.max(...prices);
    const pad = dataMax === dataMin ? Math.max(dataMax * 0.1, 1) : (dataMax - dataMin) * 0.12;
    const min = Math.max(0, dataMin - pad);
    const max = dataMax + pad;
    const baselineY = PAD.top + innerHeight;
    const times = uniqueTimes(visiblePoints);
    const includeAxisTime = shouldIncludeAxisTime(times);
    const series = visibleSeriesMeta.map((item) => ({
        ...item,
        coords: sortPointsByTime(item.points).map((point) => ({
            ...point,
            color: item.color,
            x: toChartX(times.indexOf(point.at || ''), times.length, innerWidth),
            y: toChartY(point.price, min, max, innerHeight),
        })),
    }));
    const coords = series.flatMap((item) => item.coords);
    const showArea = series.length === 1;

    const seriesPaths = series
        .map((item) => {
            if (item.coords.length === 0) {
                return '';
            }

            const linePath = linePathFromCoords(item.coords);
            const areaPath = showArea ? areaPathFromCoords(item.coords, baselineY) : '';

            return `
                ${areaPath ? `<path d="${areaPath}" fill="${item.color}" fill-opacity="0.08"></path>` : ''}
                ${item.coords.length > 1 ? `<path d="${linePath}" fill="none" stroke="${item.color}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>` : ''}
            `;
        })
        .join('');

    const highY = toChartY(dataMax, min, max, innerHeight);
    const lowY = toChartY(dataMin, min, max, innerHeight);
    const mid = (min + max) / 2;

    const yTicks = [
        { value: max, y: PAD.top },
        { value: mid, y: PAD.top + innerHeight / 2 },
        { value: min, y: PAD.top + innerHeight },
    ];

    const xLabels = pickXLabels(
        times.map((at, index) => ({
            at,
            x: toChartX(index, times.length, innerWidth),
        })),
    );

    const dots = coords
        .map(
            (point, index) => `
            <g data-chart-point data-point-index="${index}" tabindex="0" role="img" aria-label="${formatChartMoney(point.price)}${point.vendorName ? `，${escapeHtml(point.vendorName)}` : ''}，${point.at || ''}" style="cursor:pointer">
                <circle cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="14" fill="transparent"></circle>
                <circle class="chart-dot" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4" fill="${point.color}" stroke="#fff" stroke-width="2"></circle>
            </g>
        `,
        )
        .join('');

    svg.setAttribute('viewBox', `0 0 ${CHART_WIDTH} ${CHART_HEIGHT}`);
    svg.innerHTML = `
        <rect x="0" y="0" width="${CHART_WIDTH}" height="${CHART_HEIGHT}" fill="#fff" />
        ${yTicks
            .map(
                (tick) => `
                    <line x1="${PAD.left}" y1="${tick.y.toFixed(2)}" x2="${PAD.left + innerWidth}" y2="${tick.y.toFixed(2)}" stroke="#e2e8f0" stroke-width="1" />
                    <text x="${PAD.left - 8}" y="${tick.y + 4}" text-anchor="end" fill="#64748b" font-size="11">${formatChartMoney(tick.value)}</text>
                `,
            )
            .join('')}
        <line x1="${PAD.left}" y1="${PAD.top}" x2="${PAD.left}" y2="${PAD.top + innerHeight}" stroke="#cbd5e1" stroke-width="1" />
        <line x1="${PAD.left}" y1="${PAD.top + innerHeight}" x2="${PAD.left + innerWidth}" y2="${PAD.top + innerHeight}" stroke="#cbd5e1" stroke-width="1" />
        <line x1="${PAD.left}" y1="${highY.toFixed(2)}" x2="${PAD.left + innerWidth}" y2="${highY.toFixed(2)}" stroke="#0f766e" stroke-width="1" stroke-dasharray="4 4" />
        <line x1="${PAD.left}" y1="${lowY.toFixed(2)}" x2="${PAD.left + innerWidth}" y2="${lowY.toFixed(2)}" stroke="#b45309" stroke-width="1" stroke-dasharray="4 4" />
        ${seriesPaths}
        ${dots}
        ${xLabels
            .map(
                (point) => `
                    <text x="${point.x.toFixed(2)}" y="${CHART_HEIGHT - 12}" text-anchor="middle" fill="#64748b" font-size="11">${formatAxisDate(point.at, { includeTime: includeAxisTime })}</text>
                `,
            )
            .join('')}
    `;

    renderChartLegend(svg, allSeries, { hiddenKeys, onToggleSeries });
    bindChartPointHover(svg, coords);
}

function bindChartPointHover(svg, coords) {
    const wrap = svg.closest('[data-price-histories-chart-wrap]');
    const tooltip = wrap?.querySelector('[data-price-histories-chart-tooltip]');

    const hideTooltip = () => {
        if (tooltip) {
            tooltip.hidden = true;
        }
    };

    const showTooltip = (group, point) => {
        if (!tooltip || !wrap) {
            return;
        }

        const wrapRect = wrap.getBoundingClientRect();
        const dot = group.querySelector('.chart-dot');
        const dotRect = (dot ?? group).getBoundingClientRect();
        const priceText = formatChartMoney(point.price);
        const timeText = point.at ? String(point.at) : '';
        const vendorText = point.vendorName ? String(point.vendorName) : '';
        const color = point.color || SERIES_COLORS[0];

        tooltip.hidden = false;
        tooltip.innerHTML = [
            vendorText
                ? `<span class="flex items-center gap-1.5 text-[10px] font-normal text-slate-300"><span class="inline-block h-2 w-2 shrink-0 rounded-full" style="background-color:${color}"></span>${escapeHtml(vendorText)}</span>`
                : '',
            `<span class="block">${priceText}</span>`,
            timeText ? `<span class="mt-0.5 block text-[10px] font-normal text-slate-300">${escapeHtml(timeText)}</span>` : '',
        ].join('');

        const tooltipRect = tooltip.getBoundingClientRect();
        let left = dotRect.left - wrapRect.left + dotRect.width / 2 - tooltipRect.width / 2;
        let top = dotRect.top - wrapRect.top - tooltipRect.height - 8;

        left = Math.max(8, Math.min(left, wrapRect.width - tooltipRect.width - 8));
        if (top < 8) {
            top = dotRect.bottom - wrapRect.top + 8;
        }

        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
    };

    svg.querySelectorAll('[data-chart-point]').forEach((group) => {
        const index = Number(group.getAttribute('data-point-index'));
        const point = coords[index];
        const dot = group.querySelector('.chart-dot');

        if (!point) {
            return;
        }

        group.addEventListener('mouseenter', () => {
            dot?.setAttribute('r', '6');
            showTooltip(group, point);
        });

        group.addEventListener('mouseleave', () => {
            dot?.setAttribute('r', '4');
            hideTooltip();
        });

        group.addEventListener('focusin', () => {
            dot?.setAttribute('r', '6');
            showTooltip(group, point);
        });

        group.addEventListener('focusout', () => {
            dot?.setAttribute('r', '4');
            hideTooltip();
        });
    });
}
