

export default function currency(value, round = false) {
    let val = Number(value);
    if (isNaN(val)) return "";
    if (round) return `$${Math.round(val)}`;
    return (new Intl.NumberFormat('en-US', {style: 'currency', currency: 'USD'})).format(val);
};

