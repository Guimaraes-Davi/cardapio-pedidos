const reais = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

export function formatarReais(centavos) {
    return reais.format(centavos / 100);
}

export function formatarDataHora(iso) {
    return new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
}

export const STATUS = {
    recebido: { label: 'Recebido', cor: 'bg-sky-100 text-sky-800' },
    em_preparo: { label: 'Em preparo', cor: 'bg-amber-100 text-amber-800' },
    saiu_para_entrega: { label: 'Saiu para entrega', cor: 'bg-violet-100 text-violet-800' },
    entregue: { label: 'Entregue', cor: 'bg-emerald-100 text-emerald-800' },
    cancelado: { label: 'Cancelado', cor: 'bg-stone-200 text-stone-600' },
};
