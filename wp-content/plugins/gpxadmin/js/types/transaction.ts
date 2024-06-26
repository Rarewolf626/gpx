export interface TransactionFees {
    booking: {
        type: 'booking';
        original: number;
        amount: number;
        balance: number;
    };
    cpo: {
        type: 'cpo';
        original: number;
        amount: number;
        balance: number;
    };
    upgrade: {
        type: 'upgrade';
        original: number;
        amount: number;
        balance: number;
    };
    guest: {
        type: 'guest';
        original: number;
        amount: number;
        balance: number;
    };
    late: {
        type: 'late';
        original: number;
        amount: number;
        balance: number;
    };
    third_party: {
        type: 'third_party';
        original: number;
        amount: number;
        balance: number;
    };
    extension: {
        type: 'extension';
        original: number;
        amount: number;
        balance: number;
    };
    tax: number;
    coupon: number;
    occoupon: number;
    total: number;
    refunded: number;
    balance: number;
    max_refund: number;
    refunds: {
        booking: number;
        cpo: number;
        upgrade: number;
        guest: number;
        late: number;
        third_party: number;
        extension: number;
        refund: number;
    };
}

export interface TransactionDetails {
    id: number;
    is_booking: boolean;
    is_guest: boolean;
    is_extension: boolean;
    related_transaction_count: number;
    parent_transaction: boolean;
    is_partner: boolean;
    is_admin: boolean;
    date?: string;
    checkin?: string;
    week?: number;
    resort?: string;
    size?: string;
    nights: number;
    user_id: number;
    member?: string;
    guest?: string;
    adults: number;
    children: number;
    special_request?: string;
    deposit?: {
        id: number;
        resort?: string;
        year?: string;
        unit?: string;
    };
    cancelled: boolean;
    cancelled_date?: string;
    cancelled_by?: string;
    cancelled_data?: {
        coupon?: string;
        date?: string;
        name?: string;
        action?: string;
        amount: number;
    };
    has_flex: boolean;
    can_refund: boolean;
    refunds?: {
        credits: number;
        refunds: number;
    };
    fees: TransactionFees;
}

