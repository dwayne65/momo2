export interface User {
  id: string;
  email: string;
  name: string;
}

const STORAGE_KEY = 'momo_auth_user';

export const login = (email: string, password: string): User | null => {
  // Mock authentication - accept any email/password
  if (email && password) {
    const user: User = {
      id: '1',
      email,
      name: email.split('@')[0],
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(user));
    return user;
  }
  return null;
};

export const logout = () => {
  localStorage.removeItem(STORAGE_KEY);
};

export const getCurrentUser = (): User | null => {
  const userStr = localStorage.getItem(STORAGE_KEY);
  if (userStr) {
    try {
      return JSON.parse(userStr);
    } catch {
      return null;
    }
  }
  return null;
};

export const isAuthenticated = (): boolean => {
  return getCurrentUser() !== null;
};
