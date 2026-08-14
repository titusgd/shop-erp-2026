const chevronLeft = `
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
    </svg>
`;

const chevronRight = `
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
`;

const pageButtonClass = (active, disabled = false, isFirst = false) => {
    const base =
        'inline-flex h-9 min-w-9 shrink-0 items-center justify-center px-3 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-500';
    const border = isFirst ? '' : 'border-l border-slate-300';

    if (disabled) {
        return `${base} ${border} cursor-not-allowed bg-slate-50 text-slate-400`;
    }

    if (active) {
        return `${base} ${border} cursor-pointer bg-teal-700 text-white`;
    }

    return `${base} ${border} cursor-pointer bg-white text-teal-700 hover:bg-teal-50`;
};

const range = (start, end) => {
    const pages = [];

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
};

/**
 * @param {number} current
 * @param {number} lastPage
 * @param {number} [siblingCount]
 * @returns {Array<number|'ellipsis'>}
 */
export function visiblePageItems(current, lastPage, siblingCount = 1) {
    const total = Math.max(1, lastPage);
    const currentPage = Math.min(Math.max(1, current), total);
    const totalPageNumbers = siblingCount * 2 + 5;

    if (total <= totalPageNumbers) {
        return range(1, total);
    }

    const leftSibling = Math.max(currentPage - siblingCount, 1);
    const rightSibling = Math.min(currentPage + siblingCount, total);
    const showLeftEllipsis = leftSibling > 2;
    const showRightEllipsis = rightSibling < total - 1;

    if (!showLeftEllipsis && showRightEllipsis) {
        const leftItemCount = 3 + 2 * siblingCount;

        return [...range(1, leftItemCount), 'ellipsis', total];
    }

    if (showLeftEllipsis && !showRightEllipsis) {
        const rightItemCount = 3 + 2 * siblingCount;

        return [1, 'ellipsis', ...range(total - rightItemCount + 1, total)];
    }

    return [1, 'ellipsis', ...range(leftSibling, rightSibling), 'ellipsis', total];
}

/**
 * @param {{
 *   current: number,
 *   lastPage: number,
 *   pageAttr?: string,
 *   prevAttr?: string,
 *   nextAttr?: string,
 * }} options
 */
export function renderPaginationControls({
    current,
    lastPage,
    pageAttr = 'data-page',
    prevAttr = 'data-page-prev',
    nextAttr = 'data-page-next',
}) {
    const buttons = [
        `
            <button
                type="button"
                ${prevAttr}
                ${current <= 1 ? 'disabled' : ''}
                aria-label="上一頁"
                class="${pageButtonClass(false, current <= 1, true)}"
            >
                ${chevronLeft}
            </button>
        `,
    ];

    visiblePageItems(current, lastPage).forEach((item) => {
        if (item === 'ellipsis') {
            buttons.push(`
                <span
                    class="inline-flex h-9 min-w-9 shrink-0 items-center justify-center border-l border-slate-300 bg-white px-2 text-sm text-slate-400"
                    aria-hidden="true"
                >…</span>
            `);

            return;
        }

        const isActive = item === current;

        buttons.push(`
            <button
                type="button"
                ${pageAttr}="${item}"
                aria-label="第 ${item} 頁"
                aria-current="${isActive ? 'page' : 'false'}"
                class="${pageButtonClass(isActive)}"
            >
                ${item}
            </button>
        `);
    });

    buttons.push(`
        <button
            type="button"
            ${nextAttr}
            ${current >= lastPage ? 'disabled' : ''}
            aria-label="下一頁"
            class="${pageButtonClass(false, current >= lastPage)}"
        >
            ${chevronRight}
        </button>
    `);

    return buttons.join('');
}
