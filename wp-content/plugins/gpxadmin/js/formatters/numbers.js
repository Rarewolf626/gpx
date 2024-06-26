
const currency = (value, force = false) => {
    if (value === '' || value === null || isNaN(Number(value))) {
        if (force) {
            value = 0.00;
        } else {
            return null;
        }
    }

    return Number(value).toLocaleString(undefined, {style: 'currency', currency: 'USD'});
};

const round = (value, decimals = 0) => {
    if (value === '' || value === null || isNaN(Number(value))) {
        return decimals > 0 ? 0.00 : 0;
    }
    // round to 2 decimal places
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
};

export {
    currency, round
}
