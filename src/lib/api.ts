const MOPAY_API_BASE = '/api/mopay';
const OTHER_API_BASE = 'http://41.186.14.66:443/api/v1';
const MOPAY_API_TOKEN = import.meta.env.VITE_MOPAY_API_TOKEN;
const OTHER_API_TOKEN = import.meta.env.VITE_OTHER_API_TOKEN;

export interface UserIdentificationResponse {
  firstName: string;
  lastName: string;
  birthDate: string;
  gender: string;
  isActive: boolean;
}

export interface Transfer {
  transaction_id?: string;
  amount: number;
  phone: string;
  message: string;
}

export interface PaymentRequest {
  transaction_id?: string;
  amount: number;
  currency: string;
  phone: string;
  payment_mode: string;
  message: string;
  callback_url: string;
  transfers: Transfer[];
}

export interface TransferRequest {
  phone: string;
  amount: number;
  message?: string;
}

export interface TransactionStatusResponse {
  transactionId: string;
  phone: string | number;
  amount: number;
  status: number;
  transfers: {
    transactionId: string;
    amount: number;
    phone: string | number;
    status: number;
  }[];
}

/**
 * Verify user via direct API call
 */
export const verifyUser = async (phoneNumber: string): Promise<UserIdentificationResponse> => {
  const res = await fetch(`${MOPAY_API_BASE}/customer-info?phone=${phoneNumber}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${MOPAY_API_TOKEN}`,
    },
  });

  if (!res.ok) {
    const msg = await res.text();
    throw new Error(`Failed to verify user: ${msg}`);
  }

  return res.json();
};

/**
 * Initiate payment via direct API call
 */
export const processPayment = async (data: PaymentRequest): Promise<any> => {
  const res = await fetch(`${MOPAY_API_BASE}/initiate-payment`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${MOPAY_API_TOKEN}`,
    },
    body: JSON.stringify(data),
  });

  if (!res.ok) {
    const msg = await res.text();
    console.error('Payment API error:', msg);
    throw new Error(`Payment failed: ${msg}`);
  }

  return res.json();
};

/**
 * Process transfer via direct API call
 */
export const processTransfer = async (data: TransferRequest): Promise<any> => {
  const res = await fetch(`${MOPAY_API_BASE}/transfer`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${MOPAY_API_TOKEN}`,
    },
    body: JSON.stringify(data),
  });

  if (!res.ok) {
    const msg = await res.text();
    throw new Error(`Transfer failed: ${msg}`);
  }

  return res.json();
};

/**
 * Check transaction status via direct API call
 */
export const checkTransactionStatus = async (transactionId: string): Promise<TransactionStatusResponse> => {
  const res = await fetch(`${MOPAY_API_BASE}/check-status/${transactionId}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${MOPAY_API_TOKEN}`,
    },
  });

  if (!res.ok) {
    const msg = await res.text();
    throw new Error(`Failed to check transaction status: ${msg}`);
  }

  return res.json();
};
