export interface VerifiedUser {
  id: string;
  firstName: string;
  lastName: string;
  birthDate: string;
  gender: string;
  isActive: boolean;
  phone: string;
  created_at: string;
}

export interface Group {
  id: string;
  group_name: string;
  created_at: string;
}

export interface GroupMember {
  id: string;
  group_id: string;
  user_id: string;
  created_at: string;
}

export interface Payment {
  id: string;
  phone: string;
  amount: number;
  reference: string;
  status: string;
  created_at: string;
}

export interface Transfer {
  id: string;
  receiver: string;
  amount: number;
  status: string;
  created_at: string;
}

// Storage Keys
const KEYS = {
  USERS: 'momo_users',
  GROUPS: 'momo_groups',
  GROUP_MEMBERS: 'momo_group_members',
  PAYMENTS: 'momo_payments',
  TRANSFERS: 'momo_transfers',
};

// Generic storage functions
const getFromStorage = <T>(key: string): T[] => {
  const data = localStorage.getItem(key);
  return data ? JSON.parse(data) : [];
};

const saveToStorage = <T>(key: string, data: T[]) => {
  localStorage.setItem(key, JSON.stringify(data));
};

// Users
export const saveUser = (user: Omit<VerifiedUser, 'id' | 'created_at'>) => {
  const users = getFromStorage<VerifiedUser>(KEYS.USERS);
  const newUser: VerifiedUser = {
    ...user,
    id: Date.now().toString(),
    created_at: new Date().toISOString(),
  };
  users.push(newUser);
  saveToStorage(KEYS.USERS, users);
  return newUser;
};

export const getUsers = (): VerifiedUser[] => getFromStorage<VerifiedUser>(KEYS.USERS);

export const getUserByPhone = (phone: string): VerifiedUser | undefined => {
  const users = getUsers();
  return users.find(u => u.phone === phone);
};

// Groups
export const saveGroup = (group_name: string) => {
  const groups = getFromStorage<Group>(KEYS.GROUPS);
  const newGroup: Group = {
    id: Date.now().toString(),
    group_name,
    created_at: new Date().toISOString(),
  };
  groups.push(newGroup);
  saveToStorage(KEYS.GROUPS, groups);
  return newGroup;
};

export const getGroups = (): Group[] => getFromStorage<Group>(KEYS.GROUPS);

// Group Members
export const addMemberToGroup = (group_id: string, user_id: string) => {
  const members = getFromStorage<GroupMember>(KEYS.GROUP_MEMBERS);
  const newMember: GroupMember = {
    id: Date.now().toString(),
    group_id,
    user_id,
    created_at: new Date().toISOString(),
  };
  members.push(newMember);
  saveToStorage(KEYS.GROUP_MEMBERS, members);
  return newMember;
};

export const getGroupMembers = (): GroupMember[] => getFromStorage<GroupMember>(KEYS.GROUP_MEMBERS);

export const getMembersByGroup = (group_id: string): GroupMember[] => {
  const members = getGroupMembers();
  return members.filter(m => m.group_id === group_id);
};

// Payments
export const savePayment = (payment: Omit<Payment, 'id' | 'created_at'>) => {
  const payments = getFromStorage<Payment>(KEYS.PAYMENTS);
  const newPayment: Payment = {
    ...payment,
    id: Date.now().toString(),
    created_at: new Date().toISOString(),
  };
  payments.push(newPayment);
  saveToStorage(KEYS.PAYMENTS, payments);
  return newPayment;
};

export const getPayments = (): Payment[] => getFromStorage<Payment>(KEYS.PAYMENTS);

// Transfers
export const saveTransfer = (transfer: Omit<Transfer, 'id' | 'created_at'>) => {
  const transfers = getFromStorage<Transfer>(KEYS.TRANSFERS);
  const newTransfer: Transfer = {
    ...transfer,
    id: Date.now().toString(),
    created_at: new Date().toISOString(),
  };
  transfers.push(newTransfer);
  saveToStorage(KEYS.TRANSFERS, transfers);
  return newTransfer;
};

export const getTransfers = (): Transfer[] => getFromStorage<Transfer>(KEYS.TRANSFERS);
