import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const number = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export const formatUsd = (value: number | string | null | undefined) => `$${number.format(Number(value) || 0)}`;
export const formatBs = (value: number | string | null | undefined) => `Bs. ${number.format(Number(value) || 0)}`;

/**
 * Tasa USD → Bs compartida por HandleInertiaRequests. Redondea igual que
 * Payment::computeAmounts para que lo que ve el cliente coincida con lo que se guarda.
 */
export function useExchangeRate() {
    const page = usePage<{ exchangeRate?: { bs: number; updatedAt: string | null } }>();

    const rate = computed(() => Number(page.props.exchangeRate?.bs) || 0);
    const hasRate = computed(() => rate.value > 1);
    const updatedAt = computed(() => page.props.exchangeRate?.updatedAt ?? null);

    const round2 = (n: number) => Math.round(n * 100) / 100;
    const toBs = (usd: number | string | null | undefined) => round2((Number(usd) || 0) * rate.value);
    const toUsd = (bs: number | string | null | undefined) => (rate.value > 0 ? round2((Number(bs) || 0) / rate.value) : 0);

    return { rate, hasRate, updatedAt, toBs, toUsd, formatUsd, formatBs };
}
