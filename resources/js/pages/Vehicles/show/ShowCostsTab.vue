<template>
    <section class="space-y-6">
        <!-- ── Fórmula resumen ──────────────────────────────────────────── -->
        <div
            class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="grid gap-5 lg:grid-cols-[1fr_auto_1fr_auto_1fr] lg:items-center"
            >
                <FormulaCard
                    title="Coste total"
                    :value="formatCurrency(totalCost)"
                    tone="red"
                    :icon="CartIcon"
                />
                <div class="hidden justify-center text-slate-300 lg:flex">
                    <ArrowIcon class="h-10 w-10" />
                </div>
                <FormulaCard
                    title="PVP objetivo"
                    :value="formatCurrency(targetPrice)"
                    tone="blue"
                    :icon="TargetIcon"
                />
                <div class="hidden justify-center text-slate-300 lg:flex">
                    <EqualsIcon class="h-10 w-10" />
                </div>
                <FormulaCard
                    title="Margen bruto"
                    :value="formatCurrency(grossMargin)"
                    :sub-value="marginPercentText"
                    tone="amber"
                    :icon="TrendIcon"
                />
            </div>
        </div>

        <!-- ── Grid principal ───────────────────────────────────────────── -->
        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <!-- Desglose de costes -->
            <section
                class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-300"
                    >
                        <ReceiptIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-semibold text-slate-900 dark:text-white"
                        >
                            Desglose de costes
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ costEntryCount }} movimientos registrados
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- PVI base -->
                    <article
                        class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 dark:bg-slate-800/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm dark:bg-slate-900 dark:text-slate-300"
                            >
                                <TagIcon class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="font-medium text-slate-800 dark:text-white"
                                >
                                    PVI (Precio Venta Interna)
                                </p>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Base de adquisición
                                </p>
                            </div>
                        </div>
                        <p
                            class="text-base font-semibold text-slate-900 dark:text-white"
                        >
                            {{ formatCurrency(pviAmount) }}
                        </p>
                    </article>

                    <!-- Entradas de coste -->
                    <article
                        v-for="entry in vehicle.cost_entries"
                        :key="entry.id"
                        class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 dark:bg-slate-800/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm dark:bg-slate-900 dark:text-slate-300"
                            >
                                <component
                                    :is="resolveEntryIcon(entry)"
                                    class="h-4 w-4"
                                />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p
                                        class="font-medium text-slate-800 dark:text-white"
                                    >
                                        {{
                                            entry.concept || 'Coste registrado'
                                        }}
                                    </p>
                                    <CostEntryBadge :entry="entry" />
                                </div>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatEntryCaption(entry) }}
                                </p>
                            </div>
                        </div>
                        <p
                            class="text-base font-semibold text-rose-500 dark:text-rose-300"
                        >
                            {{ formatCurrency(parseNumber(entry.amount)) }}
                        </p>
                    </article>
                </div>

                <!-- Total -->
                <div
                    class="mt-5 rounded-2xl bg-slate-100 px-4 py-4 dark:bg-slate-800"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-700 shadow-sm dark:bg-slate-900 dark:text-slate-200"
                            >
                                <StackIcon class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Total inversión
                                </p>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    PVI + costes adicionales
                                </p>
                            </div>
                        </div>
                        <p
                            class="text-xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ formatCurrency(totalCost) }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Análisis de rentabilidad -->
            <section
                class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-300"
                    >
                        <PieIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-semibold text-slate-900 dark:text-white"
                        >
                            Análisis de rentabilidad
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Costes vs margen esperado
                        </p>
                    </div>
                </div>

                <!-- Barra costes/margen -->
                <div
                    class="rounded-2xl border border-slate-100 p-4 dark:border-slate-800"
                >
                    <div
                        class="flex h-11 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
                    >
                        <div
                            class="flex items-center justify-center bg-[linear-gradient(135deg,_#ef4444_0%,_#fb7185_100%)] text-sm font-semibold text-white"
                            :style="{ width: `${costSharePercent}%` }"
                        >
                            Costes
                        </div>
                        <div
                            class="flex items-center justify-center bg-[linear-gradient(135deg,_#f59e0b_0%,_#fbbf24_100%)] text-sm font-semibold text-white"
                            :style="{ width: `${marginSharePercent}%` }"
                        >
                            Margen
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <AnalysisMetric
                        label="ROI"
                        :value="roiText"
                        accent="text-emerald-500 dark:text-emerald-300"
                    />
                    <AnalysisMetric label="Markup" :value="markupText" />
                    <AnalysisMetric
                        label="Punto equilibrio"
                        :value="formatCurrency(totalCost)"
                    />
                    <AnalysisMetric
                        label="Beneficio/día"
                        :value="formatCurrency(profitPerDay)"
                    />
                </div>

                <div
                    :class="[
                        'mt-5 rounded-2xl border px-4 py-4',
                        profitabilityBannerClass,
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            <AlertIcon class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="font-semibold">
                                {{ profitabilityTitle }}
                            </p>
                            <p class="mt-1 text-sm opacity-80">
                                {{ profitabilityMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ── Facturas imputadas ────────────────────────────────────────── -->
        <section
            v-if="vehicle.invoices && vehicle.invoices.length > 0"
            class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="mb-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-50 text-violet-500 dark:bg-violet-500/10 dark:text-violet-300"
                    >
                        <FileTextIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-semibold text-slate-900 dark:text-white"
                        >
                            Facturas imputadas
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ vehicle.invoices.length }} factura{{
                                vehicle.invoices.length !== 1 ? 's' : ''
                            }}
                            · total imputado
                            {{ formatCurrency(totalInvoicesAssigned) }}
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"
                >
                    {{ pendingInvoicesCount }} pendiente{{
                        pendingInvoicesCount !== 1 ? 's' : ''
                    }}
                </span>
            </div>

            <div class="space-y-3">
                <article
                    v-for="invoice in vehicle.invoices"
                    :key="invoice.id"
                    class="rounded-2xl border border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/30"
                >
                    <!-- Cabecera factura -->
                    <div
                        class="flex items-start justify-between gap-4 px-4 py-4"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white text-violet-500 shadow-sm dark:bg-slate-900"
                            >
                                <FileTextIcon class="h-4 w-4" />
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            invoice.invoice_number ||
                                            `Factura #${invoice.id}`
                                        }}
                                    </p>
                                    <InvoiceStatusBadge
                                        :status="invoice.status"
                                    />
                                    <OcrStatusBadge
                                        :status="invoice.ocr_status"
                                    />
                                </div>
                                <p
                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ invoice.provider?.name ?? '—' }}
                                    <template v-if="invoice.issued_at">
                                        ·
                                        {{
                                            formatDate(invoice.issued_at)
                                        }}</template
                                    >
                                </p>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <p
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    formatCurrency(
                                        parseNumber(
                                            invoice.pivot?.assigned_amount,
                                        ),
                                    )
                                }}
                            </p>
                            <p class="text-xs text-slate-400">
                                de
                                {{
                                    formatCurrency(
                                        parseNumber(invoice.total_amount),
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Desglose subtotal/IVA si disponible -->
                    <div
                        v-if="invoice.subtotal_amount || invoice.vat_amount"
                        class="flex items-center gap-6 border-t border-slate-100 px-4 py-3 dark:border-slate-700/50"
                    >
                        <div
                            v-if="invoice.subtotal_amount"
                            class="text-xs text-slate-500 dark:text-slate-400"
                        >
                            Base:
                            <span
                                class="font-medium text-slate-700 dark:text-slate-200"
                                >{{
                                    formatCurrency(
                                        parseNumber(invoice.subtotal_amount),
                                    )
                                }}</span
                            >
                        </div>
                        <div
                            v-if="invoice.vat_amount"
                            class="text-xs text-slate-500 dark:text-slate-400"
                        >
                            IVA:
                            <span
                                class="font-medium text-slate-700 dark:text-slate-200"
                                >{{
                                    formatCurrency(
                                        parseNumber(invoice.vat_amount),
                                    )
                                }}</span
                            >
                        </div>
                        <div v-if="invoice.pivot?.assigned_by" class="ml-auto">
                            <span
                                :class="[
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    invoice.pivot.assigned_by === 'ocr'
                                        ? 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                ]"
                            >
                                {{
                                    invoice.pivot.assigned_by === 'ocr'
                                        ? 'OCR'
                                        : 'Manual'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Notas de imputación -->
                    <div
                        v-if="invoice.pivot?.notes"
                        class="border-t border-slate-100 px-4 py-3 dark:border-slate-700/50"
                    >
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <span
                                class="font-medium text-slate-600 dark:text-slate-300"
                                >Nota:</span
                            >
                            {{ invoice.pivot.notes }}
                        </p>
                    </div>

                    <!-- Barra imputación + botones de archivo -->
                    <div
                        class="border-t border-slate-100 px-4 py-3 dark:border-slate-700/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"
                                style="height: 5px"
                            >
                                <div
                                    class="h-full rounded-full bg-violet-500 dark:bg-violet-400"
                                    :style="{
                                        width: `${invoiceAssignedPercent(invoice)}%`,
                                    }"
                                />
                            </div>
                            <p
                                class="text-xs text-slate-400 dark:text-slate-500"
                            >
                                {{
                                    invoiceAssignedPercent(invoice).toFixed(0)
                                }}% imputado
                            </p>
                        </div>
                    </div>

                    <!-- Documentos adjuntos -->
                    <div
                        v-if="invoice.pdf_url || invoice.albaran_url"
                        class="flex flex-wrap items-center gap-2 border-t border-slate-100 px-4 py-3 dark:border-slate-700/50"
                    >
                        <a
                            v-if="invoice.pdf_url"
                            :href="'/' + invoice.pdf_url"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                        >
                            <DownloadIcon class="h-3.5 w-3.5" />
                            Ver factura
                        </a>
                        <a
                            v-if="invoice.albaran_url"
                            :href="'/' + invoice.albaran_url"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                        >
                            <DownloadIcon class="h-3.5 w-3.5" />
                            Ver albarán
                        </a>
                    </div>
                </article>
            </div>

            <!-- Saldo sin imputar total -->
            <div
                class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 dark:bg-amber-500/10"
            >
                <div class="flex items-center justify-between">
                    <p
                        class="text-sm font-medium text-amber-700 dark:text-amber-300"
                    >
                        Saldo sin imputar (total facturas)
                    </p>
                    <p
                        class="text-sm font-bold text-amber-700 dark:text-amber-300"
                    >
                        {{ formatCurrency(totalUnassigned) }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ── Sin facturas ──────────────────────────────────────────────── -->
        <section
            v-else
            class="rounded-[26px] border border-dashed border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
            >
                <FileTextIcon class="h-6 w-6" />
            </div>
            <p class="mt-4 font-medium text-slate-600 dark:text-slate-300">
                Sin facturas imputadas
            </p>
            <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">
                Las facturas asignadas a este vehículo aparecerán aquí.
            </p>
        </section>
    </section>
</template>

<script setup lang="ts">
import { computed, defineComponent, h } from 'vue';
import type { VehicleData, VehicleCostEntry } from './vehicleShow';
import { formatCurrency, parseNumber, formatDate } from './vehicleShow';

// ─── Tipos extendidos ──────────────────────────────────────────────────────

// VehicleCostEntry viene de vehicleShow — lo reexportamos localmente como alias
// para mayor claridad en el resto del componente
type CostEntry = VehicleCostEntry & {
    // Campos extra que el backend incluye en este endpoint pero no están en el tipo base
    invoice_vehicle_id?: number | null;
    type?: string | null;
    notes?: string | null;
};

interface InvoicePivot {
    assigned_amount?: number | string;
    assigned_by?: 'ocr' | 'manual' | string;
    notes?: string | null;
}

interface Invoice {
    id: number | string;
    invoice_number?: string | null;
    total_amount?: number | string;
    subtotal_amount?: number | string | null;
    vat_amount?: number | string | null;
    albaran_url?: string | null;
    pdf_url?: string | null;
    issued_at?: string | null;
    ocr_status?: 'pendiente' | 'procesado' | 'error' | string;
    status?: string;
    notes?: string | null;
    provider?: { id: number; name: string } | null;
    pivot?: InvoicePivot;
}

// VehicleData ya tiene cost_entries: VehicleCostEntry[] — compatible.
// Solo añadimos invoices, que no existe en el tipo base.
type ExtendedVehicleData = VehicleData & {
    invoices?: Invoice[];
};

const props = defineProps<{
    vehicle: ExtendedVehicleData;
}>();

// ─── Cálculos financieros ──────────────────────────────────────────────────

const pviAmount = computed(() => parseNumber(props.vehicle.pvi));

const extraCosts = computed(() =>
    (props.vehicle.cost_entries || []).reduce(
        (sum, e) => sum + parseNumber(e.amount),
        0,
    ),
);

const totalCost = computed(() => pviAmount.value + extraCosts.value);

const targetPrice = computed(() => {
    const cash = parseNumber(props.vehicle.price_cash);
    return cash || totalCost.value;
});

const grossMargin = computed(() => targetPrice.value - totalCost.value);

const marginPercent = computed(() =>
    targetPrice.value ? (grossMargin.value / targetPrice.value) * 100 : 0,
);

const roi = computed(() =>
    totalCost.value ? (grossMargin.value / totalCost.value) * 100 : 0,
);

const markup = computed(() =>
    totalCost.value
        ? ((targetPrice.value - totalCost.value) / totalCost.value) * 100
        : 0,
);

const stockDays = computed(() => {
    const createdAt = props.vehicle.created_at;
    if (!createdAt) return 1;
    const diffMs = Date.now() - new Date(createdAt).getTime();
    return Math.max(1, Math.floor(diffMs / 86_400_000));
});

const profitPerDay = computed(() => grossMargin.value / stockDays.value);
const marginPercentText = computed(() => `${marginPercent.value.toFixed(1)}%`);
const roiText = computed(() => `${roi.value.toFixed(1)}%`);
const markupText = computed(() => `${markup.value.toFixed(1)}%`);
const costEntryCount = computed(() => props.vehicle.cost_entries?.length ?? 0);

const costSharePercent = computed(() =>
    targetPrice.value
        ? Math.min(
              100,
              Math.max(
                  0,
                  Number(
                      ((totalCost.value / targetPrice.value) * 100).toFixed(1),
                  ),
              ),
          )
        : 0,
);
const marginSharePercent = computed(() =>
    Math.max(0, Number((100 - costSharePercent.value).toFixed(1))),
);

// ─── Facturas ──────────────────────────────────────────────────────────────

const totalInvoicesAssigned = computed(() =>
    (props.vehicle.invoices || []).reduce(
        (sum, inv) => sum + parseNumber(inv.pivot?.assigned_amount),
        0,
    ),
);

const totalUnassigned = computed(() =>
    (props.vehicle.invoices || []).reduce(
        (sum, inv) =>
            sum +
            Math.max(
                0,
                parseNumber(inv.total_amount) -
                    parseNumber(inv.pivot?.assigned_amount),
            ),
        0,
    ),
);

const pendingInvoicesCount = computed(
    () =>
        (props.vehicle.invoices || []).filter(
            (inv) => inv.status === 'pendiente',
        ).length,
);

function invoiceAssignedPercent(invoice: Invoice): number {
    const total = parseNumber(invoice.total_amount);
    if (!total) return 0;
    return Math.min(
        100,
        (parseNumber(invoice.pivot?.assigned_amount) / total) * 100,
    );
}

// ─── Helpers de presentación ───────────────────────────────────────────────

function formatEntryCaption(entry: CostEntry): string {
    const parts: string[] = [];
    if (entry.entry_date || entry.created_at)
        parts.push(formatDate(entry.entry_date ?? entry.created_at));
    if (entry.type) parts.push(entry.type);
    if (entry.invoice_vehicle_id) parts.push('vía factura');
    return parts.join(' · ') || '—';
}

// ─── Rentabilidad banner ───────────────────────────────────────────────────

const profitabilityTitle = computed(() => {
    if (marginPercent.value >= 12) return 'Margen excelente';
    if (marginPercent.value >= 8) return 'Margen aceptable';
    return 'Margen ajustado';
});

const profitabilityMessage = computed(() => {
    if (marginPercent.value >= 12)
        return 'La operación tiene una rentabilidad sana y deja buen colchón comercial.';
    if (marginPercent.value >= 8)
        return 'Margen correcto. Conviene vigilar costes o sostener el PVP objetivo.';
    return 'Conviene optimizar costes o aumentar PVP para mejorar la rentabilidad.';
});

const profitabilityBannerClass = computed(() => {
    if (marginPercent.value >= 12)
        return 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200';
    if (marginPercent.value >= 8)
        return 'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200';
    return 'border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200';
});

// ─── Resolución de icono por tipo/concepto ─────────────────────────────────

const conceptIconMap = [
    {
        match: ['transporte', 'logistica', 'logístico'],
        path: 'M8.25 18.75a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM17.25 18.75a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM3.75 4.5h9v9h-9zM12.75 8.25h3l2.25 2.25v4.5h-5.25',
    },
    {
        match: [
            'taller',
            'preparacion',
            'preparación',
            'reparacion',
            'reparación',
            'chapa',
            'pintura',
        ],
        path: 'M14.7 6.3a1 1 0 010 1.4l-6.99 6.99a2 2 0 01-.82.49l-2.54.85.85-2.54a2 2 0 01.49-.82l6.99-6.99a1 1 0 011.4 0l.62.62z',
    },
    {
        match: [
            'gestoria',
            'gestoría',
            'gestion',
            'gestión',
            'tramite',
            'trámite',
        ],
        path: 'M7.5 3.75h6l3 3v13.5a1.5 1.5 0 01-1.5 1.5h-7.5A1.5 1.5 0 016 20.25V5.25a1.5 1.5 0 011.5-1.5zM13.5 3.75v3h3M9 12h6M9 15h3',
    },
    {
        match: ['limpieza', 'lavado'],
        path: 'M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641l-.548 2.19a.75.75 0 00.916.99l2.597-.87a1.26 1.26 0 01.77-.019A9.15 9.15 0 0012 20.25z',
    },
    {
        match: ['seguro', 'poliza', 'póliza'],
        path: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    },
];

function resolveEntryIcon(entry: CostEntry) {
    const normalized = (
        (entry.concept ?? '') +
        ' ' +
        (entry.type ?? '')
    ).toLowerCase();
    const match = conceptIconMap.find((item) =>
        item.match.some((term) => normalized.includes(term)),
    );
    return match ? icon(match.path) : TagIcon;
}

// ─── Iconos SVG ───────────────────────────────────────────────────────────

const CartIcon = icon(
    'M2.25 3.75h1.386a1.5 1.5 0 011.464 1.175L5.37 6h13.88a.75.75 0 01.73.922l-1.5 6A.75.75 0 0117.75 13.5H7.5m0 0L6.375 8.25M7.5 13.5l-1.03 2.06A1.125 1.125 0 007.47 17.25h10.78M8.25 20.25a.75.75 0 100-1.5.75.75 0 000 1.5zm9 0a.75.75 0 100-1.5.75.75 0 000 1.5z',
);
const TargetIcon = icon(
    'M12 16.5a4.5 4.5 0 100-9 4.5 4.5 0 000 9zm0 0V21m0-18V3m9 9h-4.5m-9 0H3',
);
const TrendIcon = icon('M4.5 16.5l5.25-5.25 3.75 3.75 6-7.5M19.5 19.5h-15');
const ArrowIcon = icon('M5.25 12h13.5m0 0l-4.5-4.5m4.5 4.5l-4.5 4.5');
const EqualsIcon = icon('M6 9h12M6 15h12');
const ReceiptIcon = icon(
    'M7.5 3.75h9a1.5 1.5 0 011.5 1.5v15l-3-1.5-3 1.5-3-1.5-3 1.5v-15a1.5 1.5 0 011.5-1.5zM9 8.25h6M9 12h6M9 15.75h3',
);
const PieIcon = icon(
    'M11.25 3v9h9A9 9 0 0011.25 3zM9.75 4.08A9 9 0 1019.92 14.25H9.75V4.08z',
);
const TagIcon = icon(
    'M20.59 13.41L11 3.83H4v7l9.59 9.58a2 2 0 002.82 0l4.18-4.17a2 2 0 000-2.83zM7.5 7.5h.01',
);
const StackIcon = icon(
    'M12 3c-4.97 0-9 1.79-9 4s4.03 4 9 4 9-1.79 9-4-4.03-4-9-4zm-9 9v5c0 2.21 4.03 4 9 4s9-1.79 9-4v-5',
);
const AlertIcon = icon(
    'M12 9v4.5m0 3h.008v.008H12v-.008zM10.29 3.86L1.82 18a1.875 1.875 0 001.61 2.81h16.94A1.875 1.875 0 0021.98 18L13.51 3.86a1.875 1.875 0 00-3.22 0z',
);
const FileTextIcon = icon(
    'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
);
const DownloadIcon = icon(
    'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3',
);

function icon(path: string) {
    return defineComponent({
        name: 'CostsIcon',
        setup(_, { attrs }) {
            return () =>
                h(
                    'svg',
                    {
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: 'currentColor',
                        'stroke-width': '1.8',
                        ...attrs,
                    },
                    [
                        h('path', {
                            d: path,
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                        }),
                    ],
                );
        },
    });
}

// ─── Componentes inline ────────────────────────────────────────────────────

const FormulaCard = defineComponent({
    name: 'FormulaCard',
    props: {
        title: { type: String, required: true },
        value: { type: String, required: true },
        subValue: { type: String, default: '' },
        tone: {
            type: String as () => 'red' | 'blue' | 'amber',
            required: true,
        },
        icon: { type: Object, required: true },
    },
    setup(p) {
        const toneClasses = {
            red: 'border-rose-300 bg-rose-50/70',
            blue: 'border-blue-300 bg-blue-50/70',
            amber: 'border-amber-300 bg-amber-50/80',
        };
        const subValueClasses = {
            red: 'text-rose-500',
            blue: 'text-blue-500',
            amber: 'text-emerald-500',
        };
        return () =>
            h(
                'article',
                {
                    class: `rounded-[22px] border p-6 text-center shadow-sm ${toneClasses[p.tone]}`,
                },
                [
                    h(
                        'div',
                        {
                            class: 'mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-indigo-500 shadow-sm',
                        },
                        [h(p.icon as never, { class: 'h-6 w-6' })],
                    ),
                    h(
                        'p',
                        {
                            class: 'mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500',
                        },
                        p.title,
                    ),
                    h(
                        'p',
                        {
                            class: 'mt-3 text-[2rem] font-bold tracking-tight text-slate-800',
                        },
                        p.value,
                    ),
                    p.subValue
                        ? h(
                              'p',
                              {
                                  class: `mt-3 text-xl font-semibold ${subValueClasses[p.tone]}`,
                              },
                              p.subValue,
                          )
                        : null,
                ],
            );
    },
});

const AnalysisMetric = defineComponent({
    name: 'AnalysisMetric',
    props: {
        label: { type: String, required: true },
        value: { type: String, required: true },
        accent: { type: String, default: '' },
    },
    setup(p) {
        return () =>
            h(
                'div',
                {
                    class: 'rounded-2xl bg-slate-50 px-4 py-5 text-center dark:bg-slate-800/50',
                },
                [
                    h(
                        'p',
                        {
                            class: 'text-sm font-medium text-slate-500 dark:text-slate-400',
                        },
                        p.label,
                    ),
                    h(
                        'p',
                        {
                            class: `mt-2 text-3xl font-bold tracking-tight text-slate-800 dark:text-white ${p.accent}`.trim(),
                        },
                        p.value,
                    ),
                ],
            );
    },
});

// Badge estado factura
const InvoiceStatusBadge = defineComponent({
    name: 'InvoiceStatusBadge',
    props: { status: { type: String, default: '' } },
    setup(p) {
        const map: Record<string, string> = {
            pendiente:
                'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
            asignada:
                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
            parcial:
                'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
        };
        const labels: Record<string, string> = {
            pendiente: 'Pendiente',
            asignada: 'Asignada',
            parcial: 'Parcial',
        };
        return () => {
            const cls =
                map[p.status] ??
                'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
            const label = labels[p.status] ?? p.status;
            if (!label) return null;
            return h(
                'span',
                {
                    class: `rounded-full px-2 py-0.5 text-xs font-medium ${cls}`,
                },
                label,
            );
        };
    },
});

// Badge estado OCR
const OcrStatusBadge = defineComponent({
    name: 'OcrStatusBadge',
    props: { status: { type: String, default: '' } },
    setup(p) {
        const map: Record<string, string> = {
            pendiente:
                'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
            procesado:
                'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
            error: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
        };
        const labels: Record<string, string> = {
            pendiente: 'OCR pendiente',
            procesado: 'OCR ok',
            error: 'OCR error',
        };
        return () => {
            const cls = map[p.status] ?? '';
            const label = labels[p.status] ?? '';
            if (!label) return null;
            return h(
                'span',
                {
                    class: `rounded-full px-2 py-0.5 text-xs font-medium ${cls}`,
                },
                label,
            );
        };
    },
});

// Badge origen del coste
const CostEntryBadge = defineComponent({
    name: 'CostEntryBadge',
    props: { entry: { type: Object as () => CostEntry, required: true } },
    setup(p) {
        return () => {
            if (!p.entry.invoice_vehicle_id) return null;
            return h(
                'span',
                {
                    class: 'rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
                },
                'Factura',
            );
        };
    },
});
</script>
