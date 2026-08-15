<template>
    <div class="recibo">
        <header class="recibo-header">
            <h1 class="recibo-titulo">LABORATORIO CLÍNICO</h1>
            <p class="recibo-subtitulo">Recibo de pago</p>
        </header>

        <section class="recibo-seccion">
            <div class="recibo-fila"><span class="recibo-label">N° Orden</span><span class="recibo-valor">{{ orden.codigo }}</span></div>
            <div class="recibo-fila"><span class="recibo-label">Fecha</span><span class="recibo-valor">{{ fechaActual }}</span></div>
            <div class="recibo-fila"><span class="recibo-label">Paciente</span><span class="recibo-valor">{{ orden.paciente?.nombres }} {{ orden.paciente?.apellidos }}</span></div>
            <div class="recibo-fila"><span class="recibo-label">Cédula</span><span class="recibo-valor">{{ orden.paciente?.cedula }}</span></div>
            <div v-if="orden.medico" class="recibo-fila"><span class="recibo-label">Médico</span><span class="recibo-valor">{{ orden.medico?.nombre_completo }}</span></div>
        </section>

        <section class="recibo-seccion">
            <p class="recibo-section-title">Exámenes</p>
            <table class="recibo-tabla">
                <thead><tr><th>Examen</th><th class="text-right">Precio</th></tr></thead>
                <tbody>
                    <tr v-for="item in orden.items" :key="item.id">
                        <td>{{ item.examen?.nombre }}</td>
                        <td class="text-right">${{ Number(item.precio).toFixed(2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr v-if="Number(orden.descuento) > 0">
                        <td>Descuento</td><td class="text-right">-${{ Number(orden.descuento).toFixed(2) }}</td>
                    </tr>
                    <tr class="recibo-total-row">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>${{ Number(orden.total).toFixed(2) }} USD</strong></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <section class="recibo-seccion">
            <p class="recibo-section-title">Pagos</p>

            <div v-for="(pago, pidx) in orden.pagos" :key="pago.id" class="recibo-pago-bloque">
                <p class="recibo-pago-header">
                    Pago #{{ pidx + 1 }} — {{ divisaLabel(pago.divisa) }}
                    <span class="recibo-pago-usd">${{ Number(pago.monto_usd).toFixed(2) }} USD</span>
                </p>
                <table class="recibo-tabla">
                    <thead><tr><th>Método</th><th>Ref.</th><th class="text-right">Monto</th><th class="text-right">USD</th></tr></thead>
                    <tbody>
                        <tr v-for="met in pago.metodos" :key="met.id">
                            <td>{{ metodoLabel(met.metodo) }}</td>
                            <td>{{ met.referencia ?? '—' }}</td>
                            <td class="text-right">{{ divisaSimbol(pago.divisa) }}{{ Number(met.monto_divisa).toFixed(2) }}</td>
                            <td class="text-right">${{ Number(met.monto_usd).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <table class="recibo-tabla" style="margin-top:4px">
                <tfoot>
                    <tr class="recibo-total-row">
                        <td colspan="3"><strong>TOTAL PAGADO</strong></td>
                        <td class="text-right"><strong>${{ Number(orden.monto_pagado_usd).toFixed(2) }} USD</strong></td>
                    </tr>
                    <tr v-if="Number(orden.saldo_pendiente) > 0">
                        <td colspan="3">Saldo pendiente</td>
                        <td class="text-right">${{ Number(orden.saldo_pendiente).toFixed(2) }} USD</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <footer class="recibo-footer">
            <p>Estado: <strong>{{ orden.estado_pago_label }}</strong></p>
            <p class="mt-2 text-xs">Conserve este recibo. Los resultados estarán disponibles según el tiempo indicado por cada examen.</p>
        </footer>
    </div>
</template>

<script setup lang="ts">
defineProps<{ orden: any }>()

const fechaActual = new Date().toLocaleString('es-ES', {
    day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
})

const metodoLabels: Record<string, string> = {
    efectivo: 'Efectivo', transferencia: 'Transferencia',
    punto_de_venta: 'POS', zelle: 'Zelle', pago_movil: 'Pago móvil', otro: 'Otro',
}

function metodoLabel(m: string) { return metodoLabels[m] ?? m }
function divisaLabel(d: string) { return ({ USD: 'USD ($)', EUR: 'EUR (€)', BS: 'Bs.' } as Record<string,string>)[d] ?? d }
function divisaSimbol(d: string) { return ({ USD: '$', EUR: '€', BS: 'Bs.' } as Record<string,string>)[d] ?? '' }
</script>

<style scoped>
.recibo              { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 8mm; color: #000; }
.recibo-header       { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
.recibo-titulo       { font-size: 14px; font-weight: bold; margin: 0; }
.recibo-subtitulo    { font-size: 11px; margin: 2px 0 0; }
.recibo-seccion      { margin-bottom: 10px; }
.recibo-section-title { font-weight: bold; border-bottom: 1px dashed #000; margin-bottom: 4px; padding-bottom: 2px; }
.recibo-fila         { display: flex; justify-content: space-between; margin-bottom: 2px; }
.recibo-label        { color: #555; }
.recibo-valor        { font-weight: 600; text-align: right; max-width: 55%; }
.recibo-tabla        { width: 100%; border-collapse: collapse; }
.recibo-tabla th     { text-align: left; border-bottom: 1px solid #000; padding: 2px 0; font-weight: bold; }
.recibo-tabla td     { padding: 2px 0; vertical-align: top; }
.recibo-tabla .text-right { text-align: right; }
.recibo-total-row td { border-top: 1px solid #000; padding-top: 3px; font-size: 13px; }
.recibo-pago-bloque  { margin-bottom: 6px; }
.recibo-pago-header  { font-weight: bold; font-size: 11px; display: flex; justify-content: space-between; margin-bottom: 2px; }
.recibo-pago-usd     { font-weight: bold; }
.recibo-footer       { border-top: 2px solid #000; padding-top: 8px; text-align: center; }
</style>