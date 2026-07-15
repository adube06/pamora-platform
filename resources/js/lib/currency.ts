export function formatCurrency(value: string | number): string {
    const amount = typeof value === 'string' ? parseFloat(value) : value;

    if (Number.isNaN(amount)) {
        return String(value);
    }

    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
}
