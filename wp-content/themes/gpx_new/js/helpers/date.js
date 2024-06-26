import isString from 'lodash/isString';
import {parse} from "date-fns";

export function parseDate(date, format) {
    if (!date) return '';
    if (format) {
        return parse(date, format, new Date());
    }
    if (/^\d{4}-\d{2}-\d{2}$/i.test(date)) {
        return parse(date, 'yyyy-MM-dd', new Date());
    }
    if (/^\d{2}\/\d{2}\/\d{4}$/i.test(date)) {
        return parse(date, 'MM/dd/yyyy', new Date());
    }
    return new Date(Date.parse(date));
};

export default function formatDate(date) {
    if (!date) return '';
    if (isString(date)) {
        date = parseDate(date);
    }
    return new Intl.DateTimeFormat('en-US', {
        year: "numeric",
        month: "long",
        day: "numeric",
    }).format(date);
};

export function timestampToDate(timestamp) {
    let date = new Date(timestamp * 1000);
    return new Intl.DateTimeFormat('en-US', {
        year: "numeric",
        month: "numeric",
        day: "numeric",
    }).format(date);
};
