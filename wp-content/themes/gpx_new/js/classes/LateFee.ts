import {addDays, parseISO, isAfter} from 'date-fns';

type FeesType = {
    days: number;
    extra_days: number;
    fee: number;
    extra_fee: number;
    tp_fee: number;
    tp_days: number;
}

class LateFee {
    private days: number;
    private extra_days: number;
    private fee: number;
    private extra_fee: number;
    private tp_fee: number;
    private tp_days: number;

    constructor(fees: FeesType = {days: 0, extra_days: 0, fee: 0, extra_fee: 0, tp_fee: 50, tp_days: 60}) {
        this.days = fees.days;
        this.extra_days = fees.extra_days;
        this.fee = fees.fee;
        this.extra_fee = fees.extra_fee;
        this.tp_fee = fees.tp_fee;
        this.tp_days = fees.tp_days;
    }

    setFees(fees: FeesType) {
        this.days = fees.days;
        this.extra_days = fees.extra_days;
        this.fee = fees.fee;
        this.extra_fee = fees.extra_fee;
        this.tp_fee = fees.tp_fee;
        this.tp_days = fees.tp_days;
    }

    calculate(checkin ?: string | Date): number {
        if (!checkin) {
            return 0;
        }
        const date = checkin instanceof Date ? new Date(checkin.getTime()) : parseISO(checkin);
        const sevenDaysFromNow = addDays((new Date()).setHours(0, 0, 0, 0), this.extra_days);
        if (isAfter(sevenDaysFromNow, date)) {
            return this.extra_fee;
        }
        const fifteenDaysFromNow = addDays((new Date()).setHours(0, 0, 0, 0), this.days);
        if (isAfter(fifteenDaysFromNow, date)) {
            return this.fee;
        }

        return 0;
    }

    thirdPartyFee(is_third_party_resort: boolean = false): number {
        if(!is_third_party_resort) {
            return 0.00;
        }
        return this.tp_fee;
    }

    isThirdPartyAllowed(checkin ?: string | Date): boolean {
        if(!checkin) {
            return false;
        }

        const date = checkin instanceof Date ? new Date(checkin.getTime()) : parseISO(checkin);
        const daysFromNow = addDays((new Date()).setHours(0, 0, 0, 0), this.tp_days - 1);
        return isAfter(date, daysFromNow);
    }

    thirdPartyDays(): number {
        return this.tp_days;
    }
}

export default LateFee;
